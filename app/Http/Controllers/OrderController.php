<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Other methods...

    /**
     * Update the status of the order to 'success'.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus($id)
{
    try {
        $order = Order::findOrFail($id); // Jika ID tidak ditemukan, lempar 404 error

        // Update status menjadi 'completed'
        $order->status = 'completed';
        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    } catch (\Exception $e) {
        \Log::error('Error updating order status: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui status.');
    }
}

}
