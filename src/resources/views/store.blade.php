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
	    	<div class="col-md-12 col-lg-4">
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
	    	<div class="col-md-12 col-lg-4">
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
	    	<div class="col-md-12 col-lg-4">
	    		<div class="card p-3">
	    			<div class="d-flex align-items-center">
	    				<span class="stamp stamp-md bg-blue mr-3"><i class="fe fe-grid"></i></span>
	    				<div>
	    					<h4 class="m-0"><a href="javascript:void(0)">Store Domain</a></h4>
	    					<small class="text-muted"><a href="{{ !empty($subdomain) ? $storeUrl : '#' }}" target="_blank">{{ !empty($subdomain) ? $storeUrl : 'Not Reserved' }}</a></small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>



        <div class="row col-md-12">
            @if (!empty($subdomain))
                <div class="col-md-12 col-lg-6">
                    <div class="card">
                        <div class="ribbon bg-primary">FIRST</div>
                        <div class="card-body">
                            <h3 class="card-title">Setup Basic Store Information</h3>
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

                <div class="row col-md-12">

                    <form action="/mec/ecommerce-payments" method="post" class="col s12">

                        {{ csrf_field() }}

                        <div class="card">
                            <div class="ribbon bg-red">SECOND</div>
                            <div class="card-body">
                                <h3 class="card-title">Setup Payment Details</h3>
                                <p class="text-muted">
                                    
                                    How do you wish to handle <strong>payment</strong> for orders placed on your store: <br/><br/>
                                    <ul>
                                        <li>Let them use my bank account</li>
                                        <li>I have an online payment provider account (e.g. Paystack / Flutterwave)</li>
                                        <li v-if="payment_settings.has_marketplace">Since you're automatically on the {{ env('DORCAS_PARTNER_PRODUCT_NAME', 'Hub') }} platform, anny orders placed there will use their own online payment provider</li>
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

                                </p>
                            </div>
                        </div>

                    </form>

                </div>


                <div class="row col-md-12">

                    <form action="/mec/ecommerce-logistics" method="post" class="col s12">

                        {{ csrf_field() }}

                        <div class="card">
                            <div class="ribbon bg-yellow">THIRD</div>
                            <div class="card-body">
                                <h3 class="card-title">Setup Logistics Provider</h3>
                                <p class="text-muted">
                                    How do you wish to handle <strong>shipping / delivery<strong> of orders placed on your store: <br/><br/>
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
                                    </fieldset>
                                    If you choose <strong>Use Shipping Provider</strong> above, would you like to:
                                    <ul>
                                        <li>have the logistics provider come to pick orders at your location</li>
                                        <li>drop at a fulfilment centre {{ $logisticsFulfilmentCentre ? '' : '(option currently not available)' }}</li>
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

                </div>


            </div>

            @if (empty($subdomain))
                <div class="col-md-6">
			        @component('layouts.blocks.tabler.empty-fullpage')
			            @slot('title')
			                No Subdomain
			            @endslot
			            Reserve your <strong>dorcas sub-domain</strong> to proceed with activating your online store.
			            @slot('buttons')
                            <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                                Reserve SubDomain
                            </a>
			            @endslot
			        @endcomponent
                </div>
            @endif
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
                logistics_settings: {!! !empty($logisticsSettings) ? json_encode($logisticsSettings) : ["logistics_shipping" => "shipping_myself", "logistics_fulfilment" => "fulfilment_pickup"] !!},
                payment_settings: {!! !empty($paymentSettings) ? json_encode($paymentSettings) : ["payment_option" => "use_bank_account", "has_marketplace" => "no"] !!},
                logistics_fulfilment_centre: {!! json_encode($logisticsFulfilmentCentre) !!},
                advanced_store_settings: false
            }
        });
    </script>
@endsection

