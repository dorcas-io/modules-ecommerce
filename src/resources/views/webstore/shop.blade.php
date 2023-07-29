@extends('modules-ecommerce::webstore.layouts.shop')
@section('body_main_content_container_body')
    <div class="nobottommargin col_last" v-bind:class="{'postcontent': product_categories.length > 0}">
        <div class="progress" v-if="storeIsReady && is_posting">
            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar"
                 aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                <span class="sr-only">Processing...</span>
            </div>
        </div>
        <div id="shop" class="shop product-3 grid-container clearfix" data-layout="fitRows" v-if="storeIsReady && products.length > 0">
            <webstore-product v-for="product in products" :key="product.id" :product_json="product"
                              v-on:add-to-cart="addToCart"></webstore-product>
            <div class="col_full">
                <!--TODO: Handle situations where the number of pages > 10; we need to limit the pages displayed in those cases -->
                <ul class="pagination pagination-lg" v-if="typeof meta.pagination !== 'undefined' && meta.pagination.total_pages > 1">
                    <li><a href="#" v-on:click.prevent="changePage(1)">«</a></li>
                    <li v-for="n in meta.pagination.total_pages" v-bind:class="{active: n === page_number}">
                        <a href="#" v-on:click.prevent="changePage(n)" v-if="n !== page_number">@{{ n }}</a>
                        <span v-else>@{{ n }}</span>
                    </li>
                    <li><a href="#" v-on:click.prevent="changePage(meta.pagination.total_pages)">»</a></li>
                </ul>
            </div>
        </div><!-- #shop end -->
        <div class="progress" v-if="storeIsReady && is_fetching">
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                 aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                <span class="sr-only">Querying store...</span>
            </div>
        </div>
        <div class="col_full nobottommargin" v-if="storeIsReady && products.length === 0 && !is_fetching">
            <div class="feature-box center media-box fbox-bg">
                <div class="fbox-media">
                    <a href="#">
                        <img class="image_fade" src="{{ cdn('images/gallery/rawpixel-com-579246-unsplash.jpg') }}"
                             alt="No products" style="opacity: 1;">
                    </a>
                </div>
                <div class="fbox-desc">
                    <h3>No products at the moment!<span class="subtitle">{{ $storeOwner->name }} has not added any products to their store.</span></h3>
                </div>
            </div>
        </div>
        <div class="col_full nobottommargin" v-if="!verifyReadiness">
            <div class="feature-box center media-box fbox-bg">
                <div class="fbox-media">
                    <a href="#">
                        <img class="image_fade" src="{{ cdn('images/gallery/rawpixel-com-579246-unsplash.jpg') }}"
                             alt="No products" style="opacity: 1;">
                    </a>
                </div>
                <div class="fbox-desc">
                    <h3>{{ $storeOwner->name }} is <strong>currently</strong> SETTING UP and getting ready to LAUNCH shortly.
                    <br/><br/>
                    <span class="subtitle">Check back soon.</span></h3>
                </div>
                <div class="fbox-desc" v-if="storeAdminLoggedIn">
                    <h3>Hi {{ $storeOwner->name }}, it appears you are <strong>LOGGED IN</strong>.
                    <br/><br/>
                    <span class="subtitle">Please ensure <strong>{{ $setupRemainingMessage }}</strong>.</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar nobottommargin" v-if="product_categories.length > 0">
        <div class="sidebar-widgets-wrap">
            <div class="widget widget-filter-links clearfix">
                <h4>Select Category</h4>
                <ul class="custom-filter" data-container="#shop" data-active-class="active-filter">
                    <li class="widget-filter-reset" v-bind:class="{'active-filter': category_slug.length === 0}">
                        <a href="{{ route('webstore') }}" data-filter="*">Clear</a>
                    </li>
                    <li v-for="category in product_categories" :key="category.id" v-bind:class="{'active-filter': category_slug.length > 0 && category_slug == category.slug}">
                        <a v-bind:href="'{{ route('webstore.categories') }}' + '/' + category.slug"
                           v-bind:data-filter="'.sf-cat-' + category.id">@{{ category.name }}</a>
                        <span>@{{ category.products_count }}</span>
                    </li>
                </ul>
                <br/><br/>
                <h4>Ready to Checkout</h4>
                <a href="{{ route('webstore.cart') }}" class="button button-3d nomargin">View Shopping Cart</a>
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
                is_fetching: false,
                is_posting: false,
                products: [],
                meta: [],
                category_slug: {!! json_encode($categorySlug ?: '') !!},
                search_term: '{{ $defaultSearch }}',
                shop: {!! json_encode($storeOwner) !!},
                base_url: "{{ config('dorcas-api.url') }}",
                page_number: 1,
                store_settings: {!! json_encode($storeSettings) !!},
                product_categories: {!! json_encode($productCategories ?: []) !!},
                storeIsReady: {!! json_encode($storeIsReady) !!},
                readinessChecks: {!! json_encode($readinessChecks) !!},
                storeAdminLoggedIn: {{ $storeAdminLoggedIn }},
            },
            mounted: function () {
                this.searchProducts();
                console.log(this.readinessChecks);
                console.log(this.verifyReadiness());
            },
            computed: {

            },
            updated: function () {
                SEMICOLON.initialize.lightbox();
            },
            watch: {
                search_term: function (old_search, new_search) {
                    if (old_search.toLowerCase() === new_search.toLowerCase()) {
                        return;
                    }
                    this.page_number = 1;
                }
            },
            methods: {

                verifyReadiness: function() {
                    let readiness = this.readinessChecks;
                    for (let x in readiness) {
                        if (typeof readiness[x] === 'boolean') {
                            if (readiness[x] !== true) {
                                return false;
                            }
                        }
                        //else if (typeof readiness[x] === 'object') {
                        //    if (!this.verifyReadiness(readiness[x])) {
                        //        return false;
                        //    }
                        //}
                    }
                    
                    return true;
                },

                addToCart: function (id, name, price, photo, quantity) {
                    if (this.is_posting) {
                        // a request still running
                        return swal({
                            title: "Please Wait...",
                            text: "Your previous request is still processing.",
                            type: "info"
                        });
                    }
                    quantity = typeof quantity === 'undefined' || parseInt(quantity, 10) <= 0 ? 1 : parseInt(quantity, 10);
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
                changePage: function (number) {
                    this.page_number = parseInt(number, 10);
                    this.searchProducts();
                },
                searchProducts: function () {
                    var context = this;
                    this.is_fetching = true;
                    this.products = [];
                    axios.get(this.base_url + "/store/" + this.shop.id, {
                        params: {
                            search: context.search_term,
                            limit: 12,
                            page: context.page_number,
                            category_slug: context.category_slug
                        }
                    }).then(function (response) {
                        context.is_fetching = false;
                        context.products = response.data.data;
                        context.meta = response.data.meta;
                    }).catch(function (error) {
                            var message = '';
                            context.is_fetching = false;
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
                    shopView.searchProducts();
                },
                toggleCartOpen: function () {
                    this.is_cart_opened = !this.is_cart_opened;
                }
            }
        });
    </script>
@endsection