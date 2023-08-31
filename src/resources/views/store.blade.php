@extends('layouts.tabler')

@section('head_css')
    <style type="text/css">

        .ribbon.bg-red {
            border-color: #d63939;
        }

        .bg-red {
            color: #fff!important;
            background: #d63939!important;
        }
        
        .ribbon.bg-yellow {
            border-color: #f59f00;
        }

        .bg-yellow {
            color: #fff!important;
            background: #f59f00!important;
        }
        
        .ribbon.bg-primary {
            border-color: #206bc4;
        }

        .bg-primary {
            color: #fff!important;
            background: #206bc4!important;
        }


        .ribbon {
            position: absolute;
            top: 0.75rem;
            right: -0.25rem;
            z-index: 1;
            padding: 0.25rem 0.75rem;
            font-size: .625rem;
            font-weight: 600;
            line-height: 1.5rem;
            color: #fff;
            text-align: center;
            text-transform: uppercase;
            background: #206bc4;
            border-color: #206bc4;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            min-width: 2rem;
        }
    </style>
@endsection

@section('body_content_header_extras')

@endsection


@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9" id="ecommerce-store">

	    <div class="row row-cards row-deck" id="store-statistics">
	    	<div class="col-md-12 col-lg-6">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md {{ empty($subdomain) ? 'bg-red' : 'bg-green' }} mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">{{ empty($subdomain) ? 'InActive' : 'Active' }}</a></h4>
	    					<small class="text-muted">Store Status</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<div class="col-md-12 col-lg-6">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">{{ $productCount ? number_format($productCount) : 'No Products' }}</a></h4>
	    					<small class="text-muted">Products</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<div class="col-md-12 col-lg-6">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="{{ route('ecommerce-domains') }}">Store Address</a></h4>
	    					<small class="text-muted"><a href="{{ !empty($subdomain) ? $storeUrl : '#' }}" target="_blank">{{ !empty($subdomain) ? \Illuminate\Support\Str::limit($storeUrl, 45, $end='...') : 'Not Reserved' }}</a> | <a href="{{ route('ecommerce-domains') }}">Edit</a></small>
                            <!-- str_replace("https://", "", $storeUrl) -->
	    				</div>
	    			</div>
	    		</div>
	    	</div>

            @if ( env('DORCAS_EDITION', 'business') != "business" )
                <div class="col-md-12 col-lg-6">
                    <div class="card p-3">
                        <div class="d-flex align-items-center">
                            <span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
                            <div>
                                <h4 class="m-0"><a href="javascript:void(0)">Marketplace Address</a></h4>
                                <small class="text-muted">
                                    <a href="{{ env('E_COMMERCE_URL', '') }}" target="_blank">
                                        {{ env('E_COMMERCE_URL', 'Not Set')  }}
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
	    </div>



        <div class="row col-md-12">

            @if (empty($subdomain))
                <div class="col-md-6">
			        @component('layouts.blocks.tabler.empty-fullpage')
			            @slot('title')
			                Store ID Not Chosen
			            @endslot
			            Reserve your <strong>Store ID</strong> to proceed with activating your e-commerce store.
			            @slot('buttons')
                            <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                                Reserve Store ID
                            </a>
			            @endslot
			        @endcomponent
                </div>
            @endif

            @if (!empty($subdomain))
            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="ribbon bg-primary">BASIC INFORMATION</div>
                    <div class="card-body">
                        <h3 class="card-title">Setup Store</h3>
                        <p class="text-muted">
                            <form action="" method="post" class="col s12">
                                {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_instagram_id" name="store_instagram_id" type="text"
                                                class="validate" v-model="store_settings.store_instagram_id">
                                            <label class="form-label" for="store_instagram_id">Store Instagram ID</label>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_twitter_id" name="store_twitter_id" type="text" class="validate" v-model="store_settings.store_twitter_id">
                                            <label class="form-label" for="store_twitter_id">Store Twitter ID</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_facebook_page" name="store_facebook_page" type="url"
                                                class="validate" v-model="store_settings.store_facebook_page">
                                            <label class="form-label" for="store_facebook_page">Facebook Page</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_homepage" name="store_homepage" type="url"
                                                class="validate" v-model="store_settings.store_homepage">
                                            <label class="form-label" for="store_homepage">Website URL (if any)</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_paid_notifications_email" name="store_paid_notifications_email" type="email"
                                                class="validate" v-model="store_settings.store_paid_notifications_email">
                                            <label class="form-label" for="store_paid_notifications_email">Email to send notifications on paid orders</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <span>
                                                <a href="#" v-on:click.prevent="advanced_store_settings = !advanced_store_settings">Show Additional Settings (Optional)</a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row" v-show="advanced_store_settings">
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_terms_page" name="store_terms_page" type="url" class="validate" v-model="store_settings.store_terms_page">
                                            <label class="form-label" for="store_terms_page">Terms of Service URL</label>
                                        </div>
                                    </div>
                                    <div class="row" v-show="advanced_store_settings"> 
                                        <div class="col-md-12 form-group">
                                            <input class="form-control" id="store_ga_tracking_id" name="store_ga_tracking_id" type="text" class="validate" v-model="store_settings.store_ga_tracking_id">
                                            <label class="form-label" for="store_ga_tracking_id" v-bind:class="{'active': typeof store_settings.store_ga_tracking_id !== 'undefined' && store_settings.store_ga_tracking_id.length > 0}">Google Analytics Tracking ID (UA-XXXXXXXXX-X)</label>
                                        </div>
                                    </div>
                                    <div class="row" v-show="advanced_store_settings">
                                        <div class="col-md-12 form-group">
                                            <textarea class="form-control" id="store_custom_js" name="store_custom_js" v-model="store_settings.store_custom_js"></textarea>
                                            <label class="form-label" for="store_custom_js">Custom Javascript (Paste the codes you were given)</label>
                                            <small>This allows you to add popular tools you use to your store site. e.g. Drift, Drip, Intercom, Tawk.to</small>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Save Store Settings
                                    </button>
                            </form>
                        
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-md-12 col-lg-6">

            @if (!empty($subdomain))

                    <form action="/mec/ecommerce-payments" method="post">

                        {{ csrf_field() }}

                        <div class="card">
                            <div class="ribbon bg-red">PAYMENT</div>
                            <div class="card-body">
                                <h3 class="card-title">Setup Payment Details</h3>
                                <p class="text-muted">
                                    

                                @if ( env('SETTINGS_ECOMMERCE_PAYMENT_USE_WALLET', true) == true )

                                    To receive payments, you need to <strong>activate your Payment Wallet</strong>:
                                    <ul>
                                        <li>All your store payments will be deposited into this wallet</li>
                                        <li>You can make withdrawals into your local bank account</li>
                                    </ul>

                                    <input type="hidden" name="wallet_request" value="1" />
                                    <input type="hidden" name="wallet_action" value="activate" />

                                    <div class="col-md-12">
                                        <button v-if="payment_settings.wallet_request==''" class="btn btn-primary" :class="{'btn-loading': button_activate_loading}" type="submit" name="action" id="activate_wallet_button" onclick="disableActivateButton()">Activate Payment Wallet</button>
                                        <a v-if="payment_settings.wallet_request=='1'" class="btn btn-success btn-sm" href="{{ route('ecommerce-wallet') }}">Manage Payment Wallet</a>
                                    </div>

                                @else

                                    How do you wish to handle <strong>payment</strong> for orders placed on your store:
                                    <ul>
                                        <li>You can choose to either <strong>Use your bank account</strong> OR <strong>Use an online payment provider account (e.g. Paystack / Flutterwave)</strong></li>
                                        <li v-if="payment_settings.has_marketplace">Note: any orders placed on the {{ env('DORCAS_PARTNER_PRODUCT_NAME', 'Hub') }} Marketplace will use the default online payment gateway and funds will be sent directly to your bank account</li>
                                    </ul>
                                    <fieldset class="form-fieldset">
                                        Choose Payment Option: 
                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <select id="payment_option" name="payment_option" class="form-control" v-model="payment_settings.payment_option" required>
                                                    <option value="use_bank_account">Use My Bank Account</option>
                                                    <option value="use_online_provider_paystack">Use My Paystack Account</option>
                                                    <option value="use_online_provider_flutterwave">Use My Flutterwave Account</option>
                                                </select>
                                            </div>
                                        </div>
                                        Currently Selected Option: <strong>@{{ paymentOptionSelection }}</strong>
                                        <br/>
                                        <small class="text-muted"><a href="#" v-on:click.prevent="parsePaymentAdvice(paymentSettingsAdvice)">@{{ paymentSettingsAdvice.action }}</a></small>
                                        <br/>
                                        <small class="text-muted" v-if="paymentSettingsAdvice.register.length != 0"><a :href="paymentSettingsAdvice.register_link" target="_blank">@{{ paymentSettingsAdvice.register }}</a></small>
                                    </fieldset>

                                    <!-- 
                                    <a class="btn btn-secondary btn-sm" href="{{ route('integrations-main') }}">
                                        Add Integration
                                    </a>
                                    <a class="btn btn-primary btn-sm" href="{{ env('DORCAS_STORE_PAYMENT_VENDOR_URL', 'https://dorcas.ravepay.co/auth/') }}" target="_blank">
                                        Create Vendor Account
                                    </a>
                                    -->

                                    <div class="col-md-12">
                                        <button class="btn btn-primary" type="submit" name="action">Save Payments Options</button>
                                    </div>

                                @endif

                                </p>
                            </div>
                        </div>

                    </form>


                    <div>&nbsp;</div>

                    <form action="/mec/ecommerce-logistics" method="post"><!-- class="col s12" -->

                        {{ csrf_field() }}

                        <div class="card">
                            <div class="ribbon bg-yellow">SHIPPING</div>
                            <div class="card-body">
                                <h3 class="card-title">Setup Logistics Provider</h3>
                                <p class="text-muted">
                                    How do you wish to handle <strong>shipping / delivery</strong> of orders placed on your store: <br/><br/>
                                    <ul>
                                        <li>You can choose to handle your shipments yourself and have customers choose from routes whose prices you set manually</li>
                                        <li>You can choose to have a logistics provider handle shipping; shipping costs are automatically calculated when your customers enter their delivery addresses</li>
                                    </ul>
                                    <fieldset class="form-fieldset">
                                        Choose Shipping Option: 
                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <select id="logistics_shipping" name="logistics_shipping" class="form-control" v-model="logistics_settings.logistics_shipping" required>
                                                    <option value="shipping_myself">Handle Shipping Myself</option>
                                                    <option value="shipping_provider">Use Shipping Provider</option>
                                                </select>
                                            </div>
                                        </div>
                                        Currently Selected Option: 
                                        <br/>
                                    </fieldset>
                                    If you choose <strong>Use Shipping Provider</strong> above, would you like to:
                                    <ul>
                                        <li>have the logistics provider come to pick orders at your location</li>
                                        <li>drop at a fulfilment centre @{{ logistics_fulfilment_centre ? '' : '(option currently not available)' }}</li>
                                    </ul>
                                    <fieldset class="form-fieldset">
                                        Choose Fulfilment Option: 
                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <select id="logistics_fulfilment" name="logistics_fulfilment" class="form-control" v-model="logistics_settings.logistics_fulfilment" required>
                                                    <option value="fulfilment_pickup">Provider to Come and Pickup</option>
                                                    <option value="fulfilment_centre" v-if="logistics_fulfilment_centre">Deliver Goods to Fulfilment Centre Myself</option>
                                                </select>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div class="col-md-12">
                                        <button class="btn btn-primary" type="submit" name="action">Save Logistics Options</button>
                                    </div>

                                </p>
                            </div>
                        </div>
                        
                    </form>


                @endif


            </div>
            @include('modules-integrations::modals.configurations')

        </div>



    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        new Vue({
            el: '#ecommerce-store',
            data: {
                store_owner: {!! json_encode($business) !!},
                store_settings: {!! json_encode($storeSettings) !!},
                store_subdomain: {!! !empty($subdomain) ? json_encode($subdomain) : "" !!},
                logistics_settings: {!! !empty($logisticsSettings) ? json_encode($logisticsSettings) : ["logistics_shipping" => "shipping_myself", "logistics_fulfilment" => "fulfilment_pickup"] !!},
                payment_settings: {!! !empty($paymentSettings) ? json_encode($paymentSettings) : ["payment_option" => "use_bank_account", "has_marketplace" => false] !!},
                logistics_fulfilment_centre: {!! json_encode($logisticsFulfilmentCentre) !!},
                advanced_store_settings: false,
                //integrations: {!! !empty($integrations) ? json_encode($integrations) : '[]' !!},
                integration_index: 0,
                integration: {!! json_encode($integration) !!},
                paymentOptionSelection: {!! json_encode($paymentOptionSelection) !!},
                paymentSettingsAdvice: {!! json_encode($paymentSettingsAdvice) !!},
                button_activate_loading: false
            },
            mounted: function() {
                //console.log(this.logistics_fulfilment_centre)
                //console.log(this.integration)
                //console.log(this.paymentOptionSelection)
                //console.log(this.paymentSettingsAdvice)

            },
            computed: {
                showIntegrationId: function () {
                    return typeof this.integration.id !== 'undefined';
                }
            },
            methods: {
                disableActivateButton: function (advice) {
                    document.getElementById("activate_wallet_button").disabled = true;
                    this.button_activate_loading = true;
                },
                parsePaymentAdvice: function (advice) {
                    if (typeof advice !== 'undefined' && typeof advice === 'object' && advice !== null) {

                        let link_type = advice.link_type;
                        let link = advice.link;

                        if ( link_type == "route" ) {
                            var url = link;
                            window.location.href = url;
                        } else if ( link_type == "custom" ) {
                            //viewPaymentSetting|paystack
                            let link_array = link.split("|");
                            if (link_array[0] == "viewPaymentSetting") {
                                let payment_integration = link_array[1];
                                let integration = typeof this.integration !== 'undefined' ? this.integration : null;
                                if (integration === null) {
                                    return;
                                }
                                this.integration = integration;
                                this.integration_index = 0;
                                $('#integration-configurations-modal').modal('show');
                            }
                        }
                    } else {
                        return;
                    }

                },
		        installIntegration: function ($event) {
		            var context = this;
		            //context.installing = true;
                	let index = $event.target.getAttribute('data-index');
                	var integration, act, action;
                    
                    integration = typeof this.integration !== 'undefined' ? this.integration : null;
                    act = "update";
                    action = "Updated";
                    
            		if (integration === null) {
            			return;
            		}

		            let display_name = integration.display_name;
                    let integration_id = context.showIntegrationId ? integration.id : null;
		            let integration_name = integration.name;
		            let integration_type = integration.type;
		            let integration_configurations = integration.configurations;
                    
		            Swal.fire({
		                title: "Are you sure?",
		                text: "You are about to " + act + " the " + display_name + " integration.",
		                type: "info",
		                showCancelButton: true,
		                confirmButtonText: "Yes, " + act +" it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (install_integration) => {
		                	this.installing = true;
				            return axios.post("/mit/integrations", {
                                integration_id: integration_id,
				                type: integration_type,
				                name: integration_name,
				                configurations: integration_configurations
				            }).then(function (response) {
				                console.log(response);
		                        $('#integration-configurations-modal').modal('hide');
		                        window.location = '{{ url()->current() }}';
				                return swal(action, action + ' the ' + display_name + ' integration to your account.', "success");
				            }).catch(function (error) {
				            	this.installing = false;
				                var message = '';
				                if (error.response) {
                                    
		                            var e = error.response;
		                            message = e.data.message;
				                } else if (error.request) {
                                    
				                    message = 'The request was made but no response was received';
				                } else {
                                    
				                    message = error.message;
				                }
				                context.installing = false;
				                return swal("Install Failed", message, "warning");
				            });



		                },
		                allowOutsideClick: () => !Swal.isLoading()
		            });
		        },
                
            },
        });
    </script>
@endsection

