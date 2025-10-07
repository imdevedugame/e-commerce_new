<x-app-layout>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-25">
                        <h4 class="font-semibold text-lg text-gray-600">Detail Penagihan</h4>
                    </div>
                    <form method="post" action="{{route('checkout.order')}}" id="checkoutForm">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="country" :value="__('Negara *')" />
                            @include('livewire.countries-select')
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="billing_address" :value="__('Alamat *')" />
                            <x-text-input id="billing_address" name="billing_address" type="text"
                                class="mt-1 block w-full" value="{{$billingDetails ? $billingDetails->billing_address : ''}}" required autofocus autocomplete="billing_address" />
                            <x-input-error class="mt-2" :messages="$errors->get('billing_address')" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="city" :value="__('Kota *')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                autofocus autocomplete="city" value="{{$billingDetails ? $billingDetails->city : ''}}"/>
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="state" :value="__('Provinsi / Kabupaten *')" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                autofocus autocomplete="state" value="{{$billingDetails ? $billingDetails->state : ''}}"/>
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="zipcode" :value="__('Kode Pos *')" />
                            <x-text-input id="zipcode" name="zipcode" type="text" class="mt-1 block w-full"
                                autofocus autocomplete="zipcode" value="{{$billingDetails ? $billingDetails->zipcode : ''}}"/>
                            <x-input-error class="mt-2" :messages="$errors->get('zipcode')" />
                        </div>
                        <div class="mb-4">
                            <x-input-label for="phone" :value="__('Nomor Telepon *')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                autofocus autocomplete="phone" value="{{$billingDetails ? $billingDetails->phone : ''}}"/>
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                        <div class="form-group mb-30">
                            <x-input-label for="order_notes" :value="__('Informasi tambahan')" />
                            <x-text-input id="order_notes" name="order_notes" type="text" class="mt-1 block w-full"
                                autofocus autocomplete="order_notes" placeholder="Catatan tambahan?" value="{{$billingDetails ? $billingDetails->order_notes : ''}}"/>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="order_review border-0">
                        <div class="mb-5">
                            <h3 class="my-2 text-lg font-semibold text-gray-600">Pesanan Anda</h3>
                        </div>
                        <div class="table-responsive order_table text-center">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2">Produk</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (Cart::content() as $i)
                                        <tr>
                                            <td class="image product-thumbnail"><img src="{{ asset('storage/'.$i->model->image) }}"
                                                    alt="#">
                                            </td>
                                            <td>
                                                <h5><a
                                                        href="{{ route('product.details', $i->model->id) }}">{{ $i->model->name }}</a>
                                                </h5>
                                                <span class="product-qty">x {{ $i->qty }}</span>
                                            </td>
                                            <td>Rp{{ $i->subtotal }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th>SubTotal</th>
                                        <td class="product-subtotal" colspan="2">Rp{{ Cart::subtotal() }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pajak</th>
                                        <td class="product-subtotal" colspan="2">Rp{{ Cart::tax() }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pengiriman</th>
                                        <td colspan="2"><em>Pengiriman Gratis</em></td>
                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        <td colspan="2" class="product-subtotal"><span
                                                class="font-xl text-brand fw-900">Rp{{ Cart::total() }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-block mt-30" onclick="document.getElementById('checkoutForm').submit();">Pesan Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
