<x-app-layout>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                @include('livewire.sidebar')
                <div class="col-lg-9">
                    <div class="shop-product-fillter">
                        <div class="totall-product">
                            <p>Kami menemukan <strong class="text-brand">{{ $products->total() }}</strong> item untuk Anda!</p>
                        </div>
                        <div class="sort-by-product-area">
                            <div class="sort-by-cover">
                                <div class="sort-by-product-wrap">
                                    <div class="sort-by">
                                        <span><i class="fi-rs-apps-sort"></i>Urutkan berdasarkan:</span>
                                    </div>
                                    <div class="sort-by-dropdown-wrap">
                                        <span>
                                            @if ($sort === 'latest')
                                                Terbaru: Produk Baru
                                            @elseif ($sort === 'low-to-high')
                                                Harga: Termurah
                                            @elseif ($sort === 'high-to-low')
                                                Harga: Termahal
                                            @else
                                                Urutan Default
                                            @endif
                                            <i class="fi-rs-angle-small-down"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="sort-by-dropdown">
                                    <ul>
                                        <li><a class="{{ $sort === 'latest' ? 'active' : '' }}"
                                                href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'latest'])) }}">Terbaru:
                                                Produk Baru</a></li>
                                        <li><a class="{{ $sort === 'low-to-high' ? 'active' : '' }}"
                                                href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'low-to-high'])) }}">Harga:
                                                Termurah</a></li>
                                        <li><a class="{{ $sort === 'high-to-low' ? 'active' : '' }}"
                                                href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'high-to-low'])) }}">Harga:
                                                Termahal</a></li>
                                        <li><a href="{{ route('home') }}">Urutan Default</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row product-grid-3">
                        @foreach ($products as $p)
                            <div class="col-lg-4 col-md-4 col-6 col-sm-6 ">
                                <div class="product-cart-wrap mb-30 border-slate-200 shadow-md">
                                    <div class="product-img-action-wrap">
                                        <div class="product-img product-img-zoom">
                                            <a href="{{ route('product.details', $p->id) }}" class="">
                                                <img class="default-img" src="{{ asset('storage/'.$p->image) }}"
                                                    alt="{{ $p->name }}">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-content-wrap">
                                        <div class="product-category flex gap-2 mb-2">
                                            @foreach ($p->categories as $index => $cat)
                                                @if ($index < 4)
                                                    <a class="bg-orange-400 py-1 px-2 text-white font-semibold rounded-full"
                                                        href="/?{{ http_build_query(array_merge(request()->query(), ['category' => $cat->slug])) }}"
                                                        rel="tag">{{ $cat->name }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                        <h2>
                                            <a href="{{ route('product.details', $p->id) }}"
                                                class="text-xl">
                                                {{ strlen($p->name) > 20 ? substr($p->name, 0, 17) . '...' : $p->name }}
                                            </a>
                                        </h2>
                                        <div class="flex items-center justify-between">
                                            <div class="">
                                                <p class="text-xl font-bold text-orange-500">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                                                <p class="text-md line-through opacity-50">Rp {{ number_format($p->old_price, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="show flex justify-end mt-3">
                                                <form action="{{ route('cart.add') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                                    <a onclick="this.closest('form').submit()" class="text-orange-500 hover:text-black duration-100">
                                                        <i class="fi-rs-shopping-cart-add text-3xl"></i>
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!--pagination-->
                    {{ $products->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
