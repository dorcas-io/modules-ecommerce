<header id="header" class="full-header">
    <div id="header-wrap">
        <div class="container clearfix">
            <div id="primary-menu-trigger"><i class="icon-reorder"></i></div>
            <!-- Logo
            ============================================= -->
            <div id="logo">
                <a href="{{ $storeSettings['store_homepage'] ?? route('webstore') }}" class="standard-logo"
                   target="{{ !empty($storeSettings['store_homepage']) ? '_blank' : '_self' }}">
                    <img src="{{ !empty($storeOwner->logo) ? $storeOwner->logo : cdn('images/icon-only.png') }}"
                         alt="{{ $storeOwner->name }}" style="max-width: 126px;">
                </a>
                <a href="{{ $storeSettings['store_homepage'] ?? route('webstore') }}" class="retina-logo"
                   target="{{ !empty($storeSettings['store_homepage']) ? '_blank' : '_self' }}">
                    <img src="{{ !empty($storeOwner->logo) ? $storeOwner->logo : cdn('images/icon-only.png') }}"
                         alt="{{ $storeOwner->name }}"  style="max-width: 126px;">
                </a>
            </div><!-- #logo end -->

            <!-- Primary Navigation
            ============================================= -->
            <nav id="primary-menu">

                <ul>
                    <li><a href="{{ route('webstore') }}"><div>Home</div></a></li>
                    @if (!empty($productCategories))
                        <li>
                            <a href="{{ route('webstore.categories') }}"><div>Categories</div></a>
                            <ul>
                                @foreach ($productCategories as $category)
                                    <li>
                                        <a href="{{ route('webstore.categories.single', [$category->slug]) }}">
                                            <div>{{ $category->name }}</div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    <li><a href="{{ route('webstore.cart') }}"><div>My Cart</div></a></li>
                </ul>
                <!-- Top Cart
                ============================================= -->
                <div id="top-cart" v-bind:class="{'top-cart-open': is_cart_opened}">
                    <a href="#" id="top-cart-trigger" v-on:click.prevent="toggleCartOpen">
                        <i class="icon-shopping-cart"></i>
                        <span v-if="cart.items.length > 0">@{{ cart.items.length }}</span>
                    </a>
                    <div class="top-cart-content">
                        <div class="top-cart-title">
                            <h4>Shopping Cart</h4>
                        </div>
                        <div class="top-cart-items" v-if="cart.items.length > 0">
                            <div class="top-cart-item clearfix" v-for="product in cart.items" :key="product.id">
                                <div class="top-cart-item-image">
                                    <a href="javascript:void(0);">
                                        <img v-bind:src="product.photo" alt="product photo" />
                                    </a>
                                </div>
                                <div class="top-cart-item-desc">
                                    <a href="#">@{{ product.name }}</a>
                                    <span class="top-cart-item-price">NGN@{{ product.unit_price }}</span>
                                    <span class="top-cart-item-quantity">x @{{ product.quantity }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="top-cart-action clearfix" v-if="cart.items.length > 0">
                            <span class="fleft top-checkout-price">NGN @{{ cart.total.formatted }}</span>
                            <a href="{{ route('webstore.cart') }}" class="button button-3d button-small nomargin fright">View Cart</a>
                        </div>
                    </div>
                </div><!-- #top-cart end -->

                <!-- Top Search
                ============================================= -->
                <div id="top-search">
                    @if (empty($categorySlug))
                        <a href="#" id="top-search-trigger"><i class="icon-search3"></i><i class="icon-line-cross"></i></a>
                    @endif
                    <form action="" method="get" v-on:submit.prevent="searchProducts">
                        <input type="text" name="q" class="form-control" v-model="search_term" placeholder="Type &amp; Hit Enter..">
                    </form>
                </div><!-- #top-search end -->

            </nav><!-- #primary-menu end -->

        </div>

    </div>

</header><!-- #header end -->