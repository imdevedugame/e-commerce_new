<x-app-layout>
    <div class="px-4 py-40">
        <div class="flex items-center justify-center">
            <!-- Success Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </div>

        <h1 class="text-3xl font-semibold text-center text-green-500 mt-6">Payment Successful!</h1>
        
        <p class="text-center mt-4 text-lg text-gray-600">Thank you, <span class="font-semibold">{{ ucfirst($customer->name) }}</span>.</p>
        <p class="text-center mt-2 text-md text-gray-600">Your payment has been processed successfully and your order is being prepared.</p>

        <div class="mt-8 text-center">
            <p class="text-lg text-gray-700">Order ID: <span class="font-semibold">{{ $order->id }}</span></p>
            <p class="text-lg text-gray-700">Total Amount: <span class="font-semibold">{{ number_format($order->total, 2) }} IDR</span></p>
        </div>

        <div class="mt-8 flex justify-center">
            <a href="{{ route('home') }}" class="px-6 py-3 bg-green-500 text-white rounded-full hover:bg-green-600 transition-all">Go back to shopping</a>
        </div>
    </div>
</x-app-layout>
