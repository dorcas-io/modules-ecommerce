@extends('modules-ecommerce::webstore.layouts.shop')
@section('head_css')
    <!-- Radio Checkbox Plugin -->
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/radio-checkbox.css') }}" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@endsection

@section('body_main_content_container_body')

    <ul class="nav nav-tabs nav-justified" id="shoppingCartTab" role="tablist">
        <li class="nav-item" role="presentation" style="padding-top:5px !important; padding-bottom:5px !important;">
            <button :class="stages.data.address.active ? 'nav-link active' : 'nav-link'" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab" aria-controls="address" :aria-selected="{'true': stages.data.address.active, 'false': !stages.data.address.active}"><h3>(1) @{{ stages.data.address.title }}</h3></button>
        </li>
        <li class="nav-item" role="presentation" style="padding-top:5px !important; padding-bottom:5px !important;">
            <button :class="{'nav-link active': stages.data.shipping.active, 'nav-link disabled': stages.stage == 'address', 'nav-link': stages.stage == 'review'}" :tabindex="stages.stage == 'address' ? '-1' : '0'" :aria-disabled="stages.stage == 'address' ? 'true' : 'false'" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" :aria-selected="{'true': stages.data.shipping.active, 'false': !stages.data.shipping.active}"><h3>(2) @{{ stages.data.shipping.title }}</h3></button>
        </li>
        <li class="nav-item" role="presentation" style="padding-top:5px !important; padding-bottom:5px !important;">
            <button :class="{'nav-link active': stages.data.review.active, 'nav-link disabled': stages.stage != 'review'}" :tabindex="stages.stage != 'review' ? '-1' : '0'" :aria-disabled="stages.stage != 'review' ? 'true' : 'false'" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab" aria-controls="review" :aria-selected="{'true': stages.data.review.active, 'false': !stages.data.review.active}"><h3>(3) @{{ stages.data.review.title }}</h3></button>
        </li>
    </ul>

    <div class="tab-content" id="shoppingCartTabContent">
        
        <div :class="stages.data.address.active ? 'tab-pane active' : 'tab-pane'" id="address" role="tabpanel" aria-labelledby="address-tab" style="padding:20px !important;">

            <!-- Cart Address Begins -->
            <div class="row clearfix" v-if="typeof cart.items !== 'undefined' && cart.items.length > 0">
                <div class="col-md-6 clearfix">
                    <h4>Delivery Address</h4>
                    <form method="get" action="/cart">
                        {{ csrf_field() }}
                        <div class="col_full">
                            <input v-if="useAutoComplete" type="text" class="sm-form-control" name="address_address" id="address_address" required placeholder="Enter Delivery Address" v-model="checkout_form.address">
                        </div>
                        <div v-if="useAutoComplete" id="address_map" style="width:100%; height: 300px;" class="col_full"></div>
                        <a v-if="useAutoComplete && mapIsConfirmed" id="address_confirm" href="#" v-on:click.prevent="confirmAddress" class="button button-3d nomargin button-green">Yes, Address On Map Is Correct</a>
                        <!-- <button v-if="!useAutoComplete && !addressIsConfirmed" class="button button-3d nomargin button-black" action="confirmAddress()">Confirm Address</button> -->
                        <hr>
                        <div class="col_half" v-show="addressIsConfirmed">
                            <input type="text" class="sm-form-control" name="address_firstname" id="address_firstname" required placeholder="First Name" v-model="checkout_form.firstname">
                        </div>
                        <div class="col_half col_last" v-show="addressIsConfirmed">
                            <input type="text" class="sm-form-control" name="address_lastname" id="address_lastname" required placeholder="Lastname" v-model="checkout_form.lastname">
                        </div>
                        <div class="col_half" v-show="addressIsConfirmed">
                            <input type="email" class="sm-form-control" name="address_email" id="address_email" required placeholder="Email address" v-model="checkout_form.email">
                        </div>
                        <div class="col_half col_last" v-show="addressIsConfirmed">
                            <input type="text" class="sm-form-control" name="address_phone" id="address_phone" required placeholder="Phone number" v-model="checkout_form.phone">
                        </div>
                        <div class="col_full">
                            <textarea v-if="!useAutoComplete" class="form-control summernote"  name="address_address" id="address_address" maxlength="250" v-model="checkout_form.address" required rows="4" placeholder="Delivery Address"></textarea>
                        </div>
                        <div class="col_half" v-show="addressIsConfirmed">
                            <select class="sm-form-control" name="address_state" id="address_state" v-model="checkout_form.state">
                                <option value="">Choose your State</option>
                                @if (!empty($states))
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                @endif
                                <option value="non-local">Non-Local (Include In Address Above)</option>
                            </select>
                        </div>
                        <div class="col_half col_last" v-show="addressIsConfirmed">
                            <select class="sm-form-control" name="address_country" id="address_country" v-model="checkout_form.country" required>
                                <option value="">Choose your Country</option>
                                @if (!empty($countries))
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <input type="hidden" name="stage" id="stage" value="shipping">
                        <input type="hidden" name="address_latitude" id="address_latitude" v-model="checkout_form.latitude">
                        <input type="hidden" name="address_longitude" id="address_longitude" v-model="checkout_form.longitude">
                        <button v-if="addressIsConfirmed" type="submit" class="button button-3d nomargin button-black">Confirm & Save Address</button>
                        <button v-if="!useAutoComplete && !addressIsConfirmed" class="button button-3d nomargin button-black" action="confirmAddress()">Confirm Address</button>
                        @include('modules-ecommerce::modals.confirm-address')
                    </form>
                </div>
                <div class="col-md-6 clearfix">
                    <div class="table-responsive">
                        <h4>Cart Totals</h4>
                        <table class="table cart">
                            <tbody>
                                <tr class="cart_item">
                                    <td class="cart-product-name">
                                        <strong>Total</strong>
                                    </td>
                                    <td class="cart-product-name">
                                        <span class="amount color lead"><strong>@{{ cart.currency + '' + cart.total.formatted }}</strong></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row clearfix" v-else>
                <div class="col-md-12 clearfix">
                    <h3>There are no products in your cart.</h3>
                    <p>Please add one or more products from the store.</p>
                    <p><a href="{{ route('webstore') }}" class="button button-3d nomargin fright">Continue Shopping</a></p>
                </div>
            </div>
            <!-- Cart Address Ends -->
        </div>

        <div :class="stages.data.shipping.active ? 'tab-pane active' : 'tab-pane'" id="shipping" role="tabpanel" aria-labelledby="shipping-tab" style="padding:20px !important;">

            <!-- Cart Shipping Begins -->
            <div class="table-responsive bottommargin" id="cart-container-shipping" v-if="typeof cart.items !== 'undefined' && cart.items.length > 0 && typeof shippingRoutes !== 'undefined' && shippingRoutes.length > 0">
                <h4>Delivery Costing</h4>
                <table class="table cart">
                    <thead>
                    <tr>
                        <th class="cart-product-remove">Select</th>
                        <th class="cart-product-thumbnail">&nbsp;</th>
                        <th class="cart-product-name">Option</th>
                        <th class="cart-product-quantity" style="text-align: left !important;">Description</th>
                        <th class="cart-product-price" style="text-align: left !important;">Cost</th>
                    </tr>
                    </thead>
                    <tbody v-if="shippingSelected">
                        <tr class="cart_item" >
                            <td colspan="5">
                                <div>
                                    Delivery Option Selected! <em><strong>REVIEW THE CART</strong> to change options</em>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="!shippingSelected">
                        <tr class="cart_item" >
                            <td class="cart-product-remove">
                                <div>
                                    <input id="shipping-none" class="radio-style" name="shipping-group" type="radio" value="0" checked>
                                    <label for="shipping-none" class="radio-style-2-label">&nbsp;</label>
                                </div>
                            </td>
                            <td class="cart-product-thumbnail">
                                <a href="#">
                                </a>
                            </td>
                            <td class="cart-product-name">
                                <a href="#">None</a>
                            </td>
                            <td class="cart-product-price">
                                <span class="amount"></span>
                            </td>
                            <td class="cart-product-quantity">
                                <div class="quantity clearfix">
                                    
                                </div>
                            </td>
                        </tr>
                        <tr class="cart_item" v-for="(shippingRoute, index) in shippingRoutes" :key="shippingRoute.id">
                            <td class="cart-product-remove">
                                <div>
                                    <input :id="'shipping-' + shippingRoute.id" class="radio-style" name="shipping-group" type="radio" :value="shippingRoute.id">
                                    <label :for="'shipping-' + shippingRoute.id" class="radio-style-2-label">&nbsp;</label>
                                </div>
                            </td>
                            <td class="cart-product-thumbnail">
                                <a href="#">
                                    <img width="64" height="64" v-bind:src="productPhoto" v-bind:alt="shippingRoute.name">
                                </a>
                            </td>
                            <td class="cart-product-name">
                                <a href="#">@{{ shippingRoute.name }}</a>
                            </td>
                            <td class="cart-product-quantity" style="text-align: left !important;">
                                <div class="quantity clearfix">
                                    @{{ shippingRoute.description }}
                                </div>
                            </td>
                            <td class="cart-product-price" style="text-align: left !important;">
                                <span class="amount">@{{ shippingRoute.prices.data[0].currency + '' + shippingRoute.prices.data[0].unit_price.formatted }}</span>
                            </td>
                        </tr>
                        <tr v-if="(typeof shippingRoutes === 'undefined' || shippingRoutes.length === 0)">
                            <td colspan="6">There are no delivery costing available. Delivery is possibly included or FREE.</td>
                        </tr>
                        <tr class="cart_item">
                            <td colspan="6">
                                <div class="row clearfix">
                                    <div class="col-md-4 col-xs-4 nopadding">
                                        <!--<div class="col-md-8 col-xs-7 nopadding">
                                            <input type="text" value="" class="sm-form-control" placeholder="Enter Coupon Code..">
                                        </div>
                                        <div class="col-md-4 col-xs-5">
                                            <a href="#" class="button button-3d button-black nomargin">Apply Coupon</a>
                                        </div>-->
                                        &nbsp;
                                        <div class="progress" v-if="is_processing_shipping">
                                            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                                <span class="sr-only">Processing...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-xs-8 nopadding" v-if="!is_processing_shipping && typeof shippingRoutes !== 'undefined' && shippingRoutes.length > 0">
                                        <a href="#" class="button button-3d nomargin fright" v-if="!shippingSelected" v-on:click.prevent="addDeliveryOption">Add Delivery Option</a>
                                        <a href="#" class="button button-3d nomargin fright" v-if="shippingSelected" v-on:click.prevent="proceedToReview">Review Cart</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Cart Shipping Ends -->

        </div>

        <div :class="stages.data.review.active ? 'tab-pane active' : 'tab-pane'" id="review" role="tabpanel" aria-labelledby="review-tab" style="padding:20px !important;">

            <form method="post" action="" v-on:submit.prevent="checkout()">
                <!-- Cart Review Begins -->
                <div class="table-responsive bottommargin" id="cart-container-review">
                    <table class="table cart">
                        <thead>
                        <tr>
                            <th class="cart-product-remove">&nbsp;</th>
                            <th class="cart-product-thumbnail">&nbsp;</th>
                            <th class="cart-product-name">Product</th>
                            <th class="cart-product-price">Unit Price</th>
                            <th class="cart-product-quantity">Quantity</th>
                            <th class="cart-product-subtotal">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr class="cart_item" v-for="(cartItem, index) in cart.items" :key="cartItem.id">
                                <td class="cart-product-remove">
                                    <a href="#" class="remove" title="Remove this item" v-on:click.prevent="removeItem(index)">
                                        <i class="icon-trash2"></i>
                                    </a>
                                </td>
                                <td class="cart-product-thumbnail">
                                    <a href="#">
                                        <img width="64" height="64" v-bind:src="cartItem.photo" v-bind:alt="cartItem.name">
                                    </a>
                                </td>
                                <td class="cart-product-name">
                                    <a href="#">@{{ cartItem.name }}</a>
                                </td>
                                <td class="cart-product-price">
                                    <span class="amount">@{{ cart.currency + '' + cartItem.unit_price }}</span>
                                </td>
                                <td class="cart-product-quantity">
                                    <div class="quantity clearfix" v-if="cartItem.isShipping=='no'">
                                        <input type="button" value="-" class="minus" v-on:click.prevent="decrementQuantity(index)">
                                        <input type="text" name="quantity" value="" v-model="cartItem.quantity" class="qty">
                                        <input type="button" value="+" class="plus" v-on:click.prevent="incrementQuantity(index)">
                                    </div>
                                    <div class="quantity clearfix" v-if="cartItem.isShipping=='yes'">
                                        <!-- <input type="button" value="-" class="minus" v-on:click.prevent="decrementQuantity(index)"> -->
                                        <a href="#" class="button nomargin" v-on:click.prevent="decrementQuantity(index, 'yes')">Remove</a>
                                    </div>
                                </td>
                                <td class="cart-product-subtotal">
                                    <span class="amount">@{{ cart.currency }} @{{ cartItem.total.formatted }}</span>
                                </td>
                            </tr>
                            <tr v-if="(typeof cart.items === 'undefined' || cart.items.length === 0) && payment_url.length === 0">
                                <td colspan="6">
                                    There are no product in your cart
                                    <!-- <a href="{{ route('webstore') }}">Continue Shopping</a> -->
                                    <p><a href="{{ route('webstore') }}" class="button button-3d nomargin fright">Continue Shopping</a></p>
                                </td>
                            </tr>
                            <tr v-if="payment_url.length > 0">
                                <td colspan="6">Your order has been placed, you can also <a v-bind:href="payment_url" class="button button-3d nomargin button-black">Pay Now</a> to complete your order.</td>
                            </tr>
                            <tr class="cart_item">
                                <td colspan="6">
                                    <div class="row clearfix">
                                        <div class="col-md-4 col-xs-4 nopadding">
                                            <!--<div class="col-md-8 col-xs-7 nopadding">
                                                <input type="text" value="" class="sm-form-control" placeholder="Enter Coupon Code..">
                                            </div>
                                            <div class="col-md-4 col-xs-5">
                                                <a href="#" class="button button-3d button-black nomargin">Apply Coupon</a>
                                            </div>-->
                                            &nbsp;
                                            <div class="progress" v-if="is_processing">
                                                <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                                                    aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                                    <span class="sr-only">Processing...</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-xs-8 nopadding" v-if="!is_processing && typeof cart.items !== 'undefined' && cart.items.length > 0">
                                            <a href="#" class="button button-3d nomargin fright" v-on:click.prevent="updateQuantities">Update Cart</a>
                                            <em>Click <strong>Update Cart</strong> only if you changed any quantity above</em>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Cart Review Ends -->
                <button type="submit" class="button button-3d nomargin button-black">Place Order</button>
            </form>

        </div>

    </div>


@endsection
@section('body_js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script>
        var cartView = new Vue({
            el: '#main_content_container',
            data: {
                cart: {!! json_encode($cart) !!},
                store_settings: {!! json_encode($storeSettings) !!},
                is_processing: false,
                checkout_form: {!! json_encode($cache["address"]) !!},
                payment_url: '',
                shippingRoutes: [],
                is_processing_shipping: false,
                base_url: "{{ config('dorcas-api.url') }}",
                shop: {!! json_encode($storeOwner) !!},
                stages: {!! json_encode($stages) !!},
                logistics: {!! json_encode($logistics) !!},
                addressIsConfirmed: false,
                mapIsConfirmed: false,
                useAutoComplete: true,
                env: {!! json_encode($env) !!},
                states: {!! json_encode($states) !!},
                countries: {!! json_encode($countries) !!},
            },
            mounted: function() {
                this.loadGoogleMaps();

                //console.log(this.shop.extra_data.logistics_settings.logistics_shipping);
                //console.log(this.logistics.settings.logistics_shipping);
                if (this.stages.stage == "shipping") {
                    let shipping_type = this.logistics.settings.logistics_shipping;
                    this.loadShippingRoutes(shipping_type);
                }
                //console.log(this.logistics)
            },
            computed: {
                productPhoto: function(product) {
                    var photo = '{{ cdn('apps/webstore/images/products/1.jpg') }}';
                    if (typeof product.images !== 'undefined' && typeof product.images.data !== 'undefined' && product.images.data.length > 0) {
                        photo = product.images.data[0].url;
                    }
                    return photo;
                },
                shippingSelected: function() {
                    //check cart if shipping is among
                    let shippingItem = this.cart.items.find( itm => itm.isShipping==='yes')
                    //console.log(shippingProduct)
                    //let shippingItem = this.cart.items.indexOf(shippingItem);
                    if (typeof shippingItem !== 'undefined') {
                        return true
                    } else {
                        return false
                    }
                }
            },
            methods: {
                loadGoogleMaps: function () {
                    // Load the Google Maps API script
                    const script = document.createElement('script');
                    if (this.useAutoComplete) {
                        script.src = `https://maps.googleapis.com/maps/api/js?key=${this.env.CREDENTIAL_GOOGLE_API_KEY}&libraries=places&callback=Function.prototype`;
                        script.onload = function() {
                            cartView.initAutocomplete();
                        };
                    } else {
                        script.src = `https://maps.googleapis.com/maps/api/js?key=${this.env.CREDENTIAL_GOOGLE_API_KEY}&callback=Function.prototype`;
                    }
                    script.defer = true;
                    document.head.appendChild(script);
                

                },

                initAutocomplete: function () {

                    const mapOptions = {
                        center: { lat: 0, lng: 0 },
                        zoom: 8
                    };
                    const map = new google.maps.Map(document.getElementById('address_map'), mapOptions);
                    const geocoder = new google.maps.Geocoder();

                    // Initialize the autocomplete
                    const input = document.getElementById('address_address');
                    const autocomplete = new google.maps.places.Autocomplete(input);

                    autocomplete.bindTo('bounds', map);

                    // Retrieve the selected place and populate latitude and longitude fields
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        if (!place.geometry) {
                            console.log('No location data available for this place.');
                            cartView.mapIsConfirmed = false;
                            return;
                        }

                        cartView.mapIsConfirmed = true;

                        // Update the map center and marker
                        map.setCenter(place.geometry.location);
                        const marker = new google.maps.Marker({
                            map: map,
                            position: place.geometry.location
                        });

                        // Extract the state and country
                        let state = '';
                        let country = '';
                        let countryCode = '';
                        for (const component of place.address_components) {
                            const componentType = component.types[0];
                            if (componentType === 'administrative_area_level_1') {
                                state = component.long_name;
                            } else if (componentType === 'country') {
                                country = component.long_name;
                                countryCode = component.short_name; // Two-digit ISO country code
                            }
                        }

                        // Log the state and country to the console
                        let stateObject = cartView.states.find( stat => stat.name = state.trim() )
                        let countryObject = cartView.countries.find( coun => coun.name = country.trim() )

                        cartView.checkout_form.state = stateObject.id;
                        cartView.checkout_form.country = countryObject.id;

                        cartView.checkout_form.latitude = place.geometry.location.lat();
                        cartView.checkout_form.longitude = place.geometry.location.lng();
                    });
                },

                initMap: function () {
                    // Initialize and display the map
                    const address = this.checkout_form.address;
                    const state = this.checkout_form.state;
                    const country = this.checkout_form.country;

                    const geocoder = new google.maps.Geocoder();
                    const mapOptions = {
                        zoom: 15,
                        center: new google.maps.LatLng(0, 0) // Default center
                    };
                    const map = new google.maps.Map(document.getElementById('address_map'), mapOptions);

                    const addressString = `${address}, ${state}, ${country}`;
                    geocoder.geocode({ address: addressString }, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            map.setCenter(results[0].geometry.location);
                            new google.maps.Marker({
                                map: map,
                                position: results[0].geometry.location
                            });
                        } else {
                            console.log('Geocode was not successful for the following reason: ' + status);
                        }
                    });
                },
                confirmAddress: function () {
                    if (this.useAutoComplete) {
                        this.addressIsConfirmed = true;
                    } else {
                        this.initMap();
                        $('#confirm-address-modal').modal('show');
                    }
                },
                addressConfirm: function () {
                    this.addressIsConfirmed = true;
                },
                addressCancel: function () {
                	$('#confirm-address-modal').modal('hide');
                },
                checkout: function () {
                    var context =  this;
                    swal({
                        title: "Continue Checkout?",
                        text: "You are about to checkout the cart, do you want to continue?",
                        type: "info",
                        showCancelButton: true,
                        confirmButtonText: "Checkout",
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true
                    }, function() {
                        axios.post("/xhr/cart/checkout", context.checkout_form)
                            .then(function (response) {
                                context.cart = headerView.cart = [];
                                // remove the deleted item
                                //console.log(response.data);
                                if (typeof response.data.payment_url !== 'undefined') {
                                    context.payment_url = response.data.payment_url;
                                }
                                return swal("Order Placed", "Your order has been submitted, you should get an invoice in your email inbox soon.", "success");
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
                                return swal("Checkout Failed", message, "warning");
                            });
                    });
                },
                decrementQuantity: function (index, update = 'no', refresh = 'no') {
                    if (index >= this.cart.items.length || parseInt(this.cart.items[index].quantity, 10) === 0) {
                        return;
                    }
                    this.cart.items[index].quantity -= 1;
                    if (update == 'yes') {
                        this.updateQuantities();
                    }
                    if (refresh == 'yes') {
                        var current_url = '{{ url()->current() }}';
                        if (current_url.indexOf('?') > -1) {
                            current_url += '&stage=review'
                        } else {
                            current_url += '?stage=review'
                        }
                        window.location.href = current_url;
                    }
                },
                incrementQuantity: function (index, update = 'no', refresh = 'no') {
                    if (index >= this.cart.items.length) {
                        return;
                    }
                    this.cart.items[index].quantity += 1;
                },
                removeItem: function (index) {
                    console.log(index);
                    var item = this.cart.items[index];
                    // get the actual item
                    var context = this;
                    // set the context
                    if (this.is_processing) {
                        return swal({
                            title: "Please Wait...",
                            text: "Your previous request is still processing.",
                            type: "info"
                        });
                    }
                    var msg_alert = this.cart.items[index].isShipping === 'no' ? "Do you want to remove the product \""+item.name+"\" from your cart." : "Do you want to remove the \"" + item.name.replace("[Delivery Option] ","") + "\" Delivery Option from your order?"
                    var msg_complete = this.cart.items[index].isShipping === 'no' ? "Cart Product removed" : "Delivery Option removed"
                    swal({
                        title: "Are you sure?",
                        text: msg_alert,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, remove it!",
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true
                    }, function() {
                        context.is_processing = true;
                        axios.delete("/xhr/cart", {
                            params: {id: item.id}
                        }).then(function (response) {
                                context.is_processing = false;
                                context.cart.items.splice(index, 1);
                                // remove the deleted item
                                context.updateQuantities()
                                return swal("Done", msg_complete, "success");
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
                                context.is_processing = false;
                                return swal("Cart Item removal Failed", message, "warning");
                            });
                    });
                },
                updateQuantities: function () {
                    var quantities = [];
                    for (var i = 0; i < this.cart.items.length; i++) {
                        quantities.push({id: this.cart.items[i].id, quantity: this.cart.items[i].quantity})
                    }
                    var context = this;
                    this.is_processing = true;
                    axios.put("/xhr/cart/update-quantities", {
                        quantities: quantities
                    }).then(function (response) {
                        context.cart = headerView.cart = response.data;
                        context.is_processing = false;
                    }).catch(function (error) {
                        var message = '';
                        context.is_processing = false;
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
                },
                loadShippingRoutes: function (shippingType) {
                    var context = this;
                    this.is_processing_shipping = true;

                    let shipping_url, shipping_params;

                    if (shippingType == 'shipping_myself') {
                        shipping_url = this.base_url + "/store/" + this.shop.id;
                        shipping_params = {
                            limit: 12,
                            product_type: 'shipping'
                        }
                    } else if (shippingType == 'shipping_provider') {
                        shipping_url = "/xhr/cart/get-provider-shipping-routes";
                        shipping_params = {}
                    }
                    console.log(shippingType)
                    console.log(shipping_url)

                    axios.get(shipping_url, {
                        params: shipping_params
                    }).then(function (response) {
                        console.log(response)
                        context.is_processing_shipping = false;
                        context.meta = response.data.meta;
                        context.shippingRoutes = response.data.data;
                        console.log(context.shippingRoutes)
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
                },
                addDeliveryOption: function () {
                    var context = this;

                    var shippingOption = $('input[name=shipping-group]:checked').val();
                    //console.log(shippingOption);

                    if (shippingOption==0)  {
                        return;
                    }

                    let currentShipping = this.shippingRoutes.find( ship => ship.id===shippingOption)
                    let shippingIndex = this.shippingRoutes.indexOf(currentShipping);

                    if (shippingIndex===-1)  {
                        return swal({
                            title: "Delivery Option",
                            text: "Unable to select Delivery Option or Invalid Delivery Option. Please contact the store owner",
                            type: "info"
                        });
                    }

                    //console.log(currentShipping);

                    let id = currentShipping.id;
                    let name = "[Delivery Option] " + currentShipping.name;
                    let price = currentShipping.prices.data[0].unit_price.raw;
                    let photo = (typeof currentShipping.images !== 'undefined' && typeof currentShipping.images.data !== 'undefined' && currentShipping.images.data.length > 0) ? currentShipping.images.data[0].url : '{{ cdn('apps/webstore/images/products/photo_shipping.png') }}';

                    let quantity =  1;
                    if (this.is_posting) {
                        // a request still running
                        return swal({
                            title: "Please Wait...",
                            text: "Your previous request is still processing.",
                            type: "info"
                        });
                    }
                    quantity = typeof quantity === 'undefined' || parseInt(quantity, 10) <= 0 ? 1 : parseInt(quantity, 10);
                    this.is_processing = true;
                    axios.post("/xhr/cart", {
                        id: id, name: name, quantity: quantity, photo: photo, unit_price: price, isShipping: 'yes'
                    }).then(function (response) {
                        context.is_processing = false;
                        headerView.cart = response.data;
                        context.updateQuantities();

                        // MOVE TO FINALIZE TAB
                        // $('#review-tab').tab('show');
                        
                        // we would use a redirect instead and append a stage
                        //window.location = '/msl/sales-products';
                        //window.location = '{{ route("dashboard")."?fromsetup" }}'
                        //window.location = '{{ url()->current() }}'

                        //var current_url = window.location.href;
                        var current_url = '{{ url()->current() }}';
                        if (current_url.indexOf('?') > -1) {
                            current_url += '&stage=review'
                        } else {
                            current_url += '?stage=review'
                        }
                        window.location.href = current_url;

                    }).catch(function (error) {
                        var message = '';
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
                        context.is_processing = false;
                        return swal("Oops!", message, "warning");
                    });
                },
                proceedToReview: function () {
                    var current_url = '{{ url()->current() }}';
                    if (current_url.indexOf('?') > -1) {
                        current_url += '&stage=review'
                    } else {
                        current_url += '?stage=review'
                    }
                    window.location.href = current_url;
                }


            }
        });

        var headerView = new Vue({
            el: '#header',
            data: {
                search_term: '',
                is_cart_opened: false,
                cart: cartView.cart
            },
            methods: {
                searchProducts: function () {
                    window.location = '/?q=' + encodeURIComponent(this.search_term);
                },
                toggleCartOpen: function () {
                    this.is_cart_opened = !this.is_cart_opened;
                }
            }
        });
    </script>
@endsection