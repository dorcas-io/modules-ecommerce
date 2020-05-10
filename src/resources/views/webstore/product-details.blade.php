@extends('modules-ecommerce::webstore.layouts.shop')
@section('head_meta')
    <meta property="og:site_name" content="Dorcas Hub" />
    <meta property="og:url" content="{{ route('webstore.products.details', [$product->id]) }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $product->name }}" />
    <meta property="og:description" content="{{ $product->description }}" />
    @if (count($product->images['data']) > 0)
        @foreach ($product->images['data'] as $image)
            <meta property="og:image" content="{{ $image['url'] }}" />
        @endforeach
    @endif
    <meta property="twitter:card" content="summary" />
    @if (!empty($storeSettings['store_twitter_id']))
        <meta property="twitter:site" content="{{ '@' . $storeSettings['store_twitter_id'] }}" />
    @endif
@endsection
@section('body_main_content_container_body')
    <div class="single-product">
        <div class="product">
            <div class="col_two_fifth">
                <div class="product-image">
                    <div class="fslider" data-pagi="false" data-arrows="true" data-thumbs="true">
                        <div class="flexslider">
                            <div class="slider-wrap" data-lightbox="gallery" v-if="product.images.data.length > 0">
                                <div class="slide" v-for="image in product.images.data" :key="image.id" v-bind:data-thumb="image.url">
                                    <a v-bind:href="image.url" v-bind:title="product.name" data-lightbox="gallery-item">
                                        <img v-bind:src="image.url" v-bind:alt="product.name">
                                    </a>
                                </div>
                            </div>
                            <div class="slider-wrap" data-lightbox="gallery" v-else>
                                <div class="slide" data-thumb="/apps/webstore/images/products/1.jpg">
                                    <a href="/apps/webstore/images/products/1.jpg" v-bind:title="product.name" data-lightbox="gallery-item">
                                        <img src="/apps/webstore/images/products/1.jpg" alt="Default Image">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sale-flash">Sale!</div>
                </div><!-- Product Single - Gallery End -->
            </div>
            <div class="col_two_fifth product-desc">
                <!-- Product Single - Price
                ============================================= -->
                <div class="product-price" v-if="typeof product.prices === 'undefined' || product.prices.data.length === 0">
                    <ins>@{{ "NGN" + product.default_unit_price.formatted }}</ins>
                </div>
                <div class="product-price" v-else>
                    <ins>@{{ default_price.currency + default_price.unit_price.formatted }}</ins>
                </div>
                <div class="clear"></div>
                <div class="line"></div>
                <!-- Product Single - Quantity & Cart Button
                ============================================= -->
                <form class="cart nobottommargin clearfix" method="post">
                    <div class="quantity clearfix" v-if="product.inventory > 0">
                        <input type="button" value="-" class="minus" v-on:click.prevent="reduceQuantity">
                        <input type="text" step="1" min="1"  name="quantity" v-model="quantity" title="Qty" class="qty"
                               v-bind:max="product.inventory" />
                        <input type="button" value="+" class="plus" v-on:click.prevent="increaseQuantity">
                    </div>
                    <div class="quantity clearfix" v-else>
                        <h4 class="text-danger">OUT OF STOCK</h4>
                    </div>
                    <button type="submit" class="add-to-cart button nomargin" v-on:click.prevent="addToCart"
                            v-show="product.inventory > 0 && quantity > 0">Add to Cart</button>
                <div class="clear"></div>
                <div class="line"></div>
                <!-- Product Single - Short Description
                ============================================= -->
                <p>@{{ product.description }}</p>
                <!-- Product Single - Variants
                ============================================= -->
                
                <div class="panel panel-default product-meta nobottommargin" v-if="!variantSearching && variantSearched && variantProducts.length > 0">
                    <div class="panel-body">
                        <h5>Other Options</h5>
                        <ul class="iconlist">
                            <li v-for="variantProduct in variantProducts" :key="variantProduct.id">
                                <i class="icon-tasks"></i> <a v-bind:href="'{{ url('/products') }}' + '/' + variantProduct.id">@{{ variantProduct.name }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="panel panel-default product-meta nobottommargin" v-if="variantSearching">
                    <div class="panel-body">
                        loading...
                    </div>
                </div>
                <div class="clear"></div>
                <div class="line"></div>
                <!-- Product Single - Meta
                ============================================= -->
                <div class="panel panel-default product-meta nobottommargin">
                    <div class="panel-body">
                        <span itemprop="productID" class="sku_wrapper">SKU: <span class="sku">@{{ product.id.toUpperCase() }}</span></span>
                        <span class="posted_in">Store Owner: <a href="{{ $storeDomain }}" rel="tag">@{{ shop.name }}</a>.</span>
                        <span class="posted_in" v-if="product.categories.data.length > 0">Categories:
                            <a v-for="category in product.categories.data" :key="category.id"
                               v-bind:href="'{{ route('webstore.categories') }}' + '/' + category.slug" rel="tag">@{{ category.name }}</a>.
                        </span>
                    </div>
                </div>
            </div>
            <div class="col_one_fifth col_last">
                <div class="divider divider-center"><i class="icon-circle-blank"></i></div>
                <div class="feature-box fbox-plain fbox-dark fbox-small">
                    <div class="fbox-icon">
                        <i class="icon-thumbs-up2"></i>
                    </div>
                    <h3>100% Original</h3>
                    <p class="notopmargin">We guarantee you the sale of Original Brands.</p>
                </div>

                <div class="feature-box fbox-plain fbox-dark fbox-small">
                    <div class="fbox-icon">
                        <i class="icon-credit-cards"></i>
                    </div>
                    <h3>Payment Options</h3>
                    <p class="notopmargin">We accept Visa, MasterCard and American Express.</p>
                </div>
            </div>
            <div class="col_full nobottommargin">
                <div class="tabs clearfix nobottommargin" id="tab-1">
                    <ul class="tab-nav clearfix">
                        <li><a href="#tabs-1"><i class="icon-align-justify2"></i><span class="d-none d-md-block"> Description</span></a></li>
                        <li><a href="#tabs-2"><i class="icon-info-sign"></i><span class="d-none d-md-block"> Additional Information</span></a></li>
                    </ul>
                    <div class="tab-container">
                        <div class="tab-content clearfix" id="tabs-1">
                            <p>
                                @{{ product.description }}
                            </p>
                        </div>
                        <div class="tab-content clearfix" id="tabs-2">
                            <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Product Name</td>
                                        <td>@{{ product.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('body_js')
    <script>
        function addToCart(id, name, price, photo, quantity) {
            //console.log(id, name, price, photo, quantity);
            shopView.addToCart(id, name, price, photo, quantity);
        }

        var shopView = new Vue({
            el: '#main_content_container',
            data: {
                is_posting: false,
                active_currency: 'NGN',
                product: {!! json_encode($product) !!},
                shop: {!! json_encode($storeOwner) !!},
                base_url: "{{ config('dorcas-api.url') }}",
                quantity: 1,
                productType: '{!! !empty($productType) ? $productType : "default" !!}',
                variantTypes: {!! json_encode($variantTypes ?: []) !!},
                variantType: '',
                variant: { name:'', description:'', product_type:'', product_parent:'', prices: '', currency: '', product_variant_type: '' },
                variantProducts: {!! json_encode(!empty($variantProducts) ? $variantProducts : []) !!},
                variantParent: {!! json_encode(!empty($variantParent) ? $variantParent : []) !!},
                variantSearching: false,
                variantSearched: false
            },
            mounted: function() {
                //console.log(this.productType)
                //console.log(this.product.product_type)
                if (this.productType==="default") {
                    this.searchVariants(this.product.id);
                } else if (this.productType==="variant") {
                    this.searchVariants(this.variantParent.id);
                }
            },
            computed: {
                default_price: function () {
                    if (typeof this.product.prices === 'undefined' || this.product.prices.data.length === 0) {
                        return {
                            currency: this.currency,
                            unit_price: {raw: product.default_unit_price.raw, formatted: product.default_unit_price.formatted}
                        };
                    }
                    for (var i = 0; i < this.product.prices.data.length; i++) {
                        if (this.product.prices.data[i].currency !== this.active_currency) {
                            continue;
                        }
                        return this.product.prices.data[i];
                    }
                    return {
                        currency: this.currency,
                        unit_price: {raw: product.default_unit_price.raw, formatted: product.default_unit_price.formatted}
                    };
                },
                isVariant: function () {
                    return this.product.product_type === 'variant';
                },
                isVariantSearched: function () {
                    return this.variantSearched;
                }
            },
            updated: function () {
                SEMICOLON.initialize.lightbox();
            },
            methods: {
                increaseQuantity: function () {
                    var quantity = parseInt(this.quantity, 10);
                    if (quantity < this.product.inventory)  {
                        this.quantity = quantity + 1;
                    }
                },
                reduceQuantity: function () {
                    var quantity = parseInt(this.quantity, 10);
                    if (quantity > 1)  {
                        this.quantity = quantity - 1;
                    }
                },
                addToCart: function () {
                    if (this.is_posting) {
                        // a request still running
                        return swal({
                            title: "Please Wait...",
                            text: "Your previous request is still processing.",
                            type: "info"
                        });
                    }
                    var id = this.product.id;
                    var name = this.product.name;
                    var price = this.default_price.unit_price.raw;

                    var photo = '{{ cdn('apps/webstore/images/products/1.jpg') }}';
                    if (typeof this.product.images !== 'undefined' && typeof this.product.images.data !== 'undefined' && this.product.images.data.length > 0) { // added -  && this.product.images.data.length > 0
                        photo = this.product.images.data[0].url;
                    }
                    var quantity = typeof this.quantity === 'undefined' || parseInt(this.quantity, 10) <= 0 ? 1 : parseInt(this.quantity, 10);
                    var context = this;
                    this.is_posting = true;
                    axios.post("/xhr/cart", {
                        id: id, name: name, quantity: quantity, photo: photo, unit_price: price
                    }).then(function (response) {
                        context.is_posting = false;
                        headerView.cart = response.data;
                    }).catch(function (error) {
                        var message = '';
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            var e = error.response.data.errors[0];
                            message = e.title;
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            message = 'The request was made but no response was received';
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            message = error.message;
                        }
                        context.is_posting = false;
                        return swal("Oops!", message, "warning");
                    });
                },
                searchVariants: function (product_id) {
                    var context = this;
                    this.is_fetching = true;
                    this.variantProducts = [];
                    this.variantSearching = true;
                    axios.get(this.base_url + "/store/" + this.shop.id, {
                        params: {
                            search: context.search_term,
                            limit: 12,
                            page: context.page_number,
                            category_slug: context.category_slug,
                            product_type: 'variant',
                            product_parent: product_id
                        }
                    }).then(function (response) {
                        //console.log(response)
                        context.variantSearching = false;
                        context.variantSearched = true;
                        context.variantProducts = response.data.data;
                        context.is_fetching = false;
                        context.meta = response.data.meta;

                        if (context.productType==="variant") {
                            // remove variant option
                            let currentProduct = context.variantProducts.find( prod => prod.id===context.product.id)
                            let variantIndex = context.variantProducts.indexOf(currentProduct);
                            context.variantProducts.splice(variantIndex, 1)
                        }
                    }).catch(function (error) {
                            var message = '';
                            context.is_fetching = false;
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                var e = error.response.data.errors[0];
                                message = e.title;
                                //var e = error.response;
                                //message = e.data.message;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            return swal("Oops!", message, "warning");
                        });
                }

            }
        });

        var headerView = new Vue({
            el: '#header',
            data: {
                search_term: '',
                is_cart_opened: false,
                cart: {!! json_encode($cart) !!}
            },
            watch: {
                search_term: function (old_value, new_value) {
                    shopView.search_term = new_value;
                }
            },
            methods: {
                searchProducts: function () {
                    window.location = '/?q=' + encodeURIComponent(this.search_term)
                },
                toggleCartOpen: function () {
                    this.is_cart_opened = !this.is_cart_opened;
                }
            }
        });
    </script>
@endsection