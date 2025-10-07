<?php

namespace App\Http\Livewire;

use App\Mail\OrderReceived;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InvoiceService;
use Livewire\Component;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Snap;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Checkout extends Component
{
    // Fungsi untuk melakukan checkout dengan Midtrans
    public function midtransCheckout()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Simpan order baru ke database dengan session_id
        $sessionId = session()->getId();  // Mendapatkan session ID pengguna saat ini
        $order = Order::create([
            'user_id' => Auth::id(),
            'status' => 'pending',  // Status sementara saat menunggu pembayaran
            'total' => $this->getCartTotal(), // Total pembayaran yang sudah dibersihkan
            'session_id' => $sessionId,  // Menyimpan session_id
        ]);

        // Data transaksi
        $transactionDetails = [
            'order_id' => $order->id,  // Gunakan ID order yang baru dibuat
            'gross_amount' => (int) str_replace(',', '', Cart::total()), // Total pembayaran
        ];

        // Data pelanggan
        $customerDetails = [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->billingDetails->phone ?? '',
        ];

        // Data item
        $itemDetails = [];
        foreach (Cart::content() as $item) {
            $itemDetails[] = [
                'id' => $item->model->id,
                'price' => (int) str_replace(',', '', $item->price),
                'quantity' => $item->qty,
                'name' => $item->name,
            ];

            // Pastikan setiap item masuk ke dalam tabel order_items
            OrderItem::create([
                'order_id' => $order->id, // Menghubungkan item dengan order yang baru dibuat
                'product_id' => $item->model->id, // ID produk
                'quantity' => $item->qty, // Jumlah produk
                'price' => (int) str_replace(',', '', $item->price), // Harga produk yang sudah dibersihkan
            ]);
        }

        // Parameter untuk Snap API
        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        // Mendapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Midtrans Checkout Error: ' . $e->getMessage());

            // Redirect ke halaman checkout dengan pesan error
            return redirect()->route('checkout')->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi nanti.');
        }
    }

    // Fungsi untuk mengambil total cart
    private function getCartTotal()
    {
        return (int) str_replace(',', '', Cart::total());
    }

    // Fungsi untuk menangani transaksi sukses
    public function success(Request $request)
    {
        // Get the payment result from Midtrans
        $paymentResult = $request->all();
        \Log::info('Payment Success: ' . json_encode($paymentResult));
    
        // Get the order ID and transaction status
        $orderId = $paymentResult['order_id'];
        $transactionStatus = $paymentResult['transaction_status'];
    
        // Find the order
        $order = Order::find($orderId);
    
        if ($order && ($transactionStatus == 'capture' || $transactionStatus == 'settlement')) {
            // Update the order status to 'processing' if payment is successful
            $order->status = 'processing';
            $order->save();
            
            // Return a response to indicate the success of the operation
            return response()->json([
                'success' => true,
                'redirect_url' => route('checkout.success.page')  // Redirect to GET success page
            ]);
        } else {
            // Payment failed or is pending, handle failure
            return response()->json([
                'success' => false,
                'error' => 'Payment verification failed.'
            ]);
        }
    }
    public function successPage()
    {
        // Get the necessary customer and order data
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->latest()->first();  // Get the latest order of the user
    
        return view('livewire.success', ['customer' => $user, 'order' => $order]);
    }
        

    // Fungsi untuk menangani status pembayaran pending
    public function pending(Request $request)
    {
        return view('livewire.pending');  // Halaman untuk status pending
    }

    // Fungsi untuk menangani status pembayaran gagal
    public function failed(Request $request)
    {
        return view('livewire.failed');  // Halaman untuk status gagal
    }

    // Fungsi untuk menangani pembatalan transaksi
    public function cancel()
    {
        return redirect()->route('home')->with('success', 'Pesanan Anda telah dibatalkan. Silakan coba lagi.');
    }

    // Fungsi untuk membuat order baru
    public function makeOrder(Request $request)
    {
        $validatedRequest = $request->validate([
            'country' => 'required',
            'billing_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'phone' => 'required',
            'zipcode' => 'required|numeric',
            'order_notes' => 'nullable',
        ]);

        $user = Auth::user();
        $billingDetails = $user->billingDetails;

        // Jika billing details belum ada, buat baru
        if ($billingDetails === null) {
            $user->billingDetails()->create($validatedRequest);
        } else {
            // Jika billing details sudah ada, update
            $billingDetails->update($validatedRequest);
        }

        // Ambil Snap Token untuk transaksi
        $snapToken = $this->midtransCheckout();
        return view('livewire.snap', ['snapToken' => $snapToken]);
    }

    // Fungsi untuk merender halaman checkout
    public function render()
    {
        if (Cart::count() <= 0) {
            session()->flash('error', 'Keranjang belanja Anda kosong.');
            return redirect()->route('home');
        }

        $user = Auth::user();
        $billingDetails = $user->billingDetails;

        return view('livewire.checkout', compact('billingDetails'));
    }
}