@extends('layouts.tabler')
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
                    <div class="row">
                        <div class="col-md-12">
                            <p>
                                Provide some basic data for your online store
                            </p>
                        </div>
                    </div>
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
                                    <label class="form-label" for="store_homepage">Homepage URL</label>
                                </div>
                            </div>
                            <div class="row">
                                <span>
                                    <a href="#" v-on:click.prevent="advanced_store_settings = !advanced_store_settings">Enter Advanced Settings (Optional)</a>
                                </span>
                                <div id="store_advanced_settings" v-show="advanced_store_settings">
                                    <div class="col-md-12 form-group">
                                        <input class="form-control" id="store_terms_page" name="store_terms_page" type="url" class="validate" v-model="store_settings.store_terms_page">
                                        <label class="form-label" for="store_terms_page">Terms of Service URL</label>
                                    </div>
                                    <br/>    
                                    <div class="col-md-12 form-group">
                                        <input class="form-control" id="store_ga_tracking_id" name="store_ga_tracking_id" type="text" class="validate" v-model="store_settings.store_ga_tracking_id">
                                        <label class="form-label" for="store_ga_tracking_id" v-bind:class="{'active': typeof store_settings.store_ga_tracking_id !== 'undefined' && store_settings.store_ga_tracking_id.length > 0}">Google Analytics Tracking ID (UA-XXXXXXXXX-X)</label>
                                    </div>
                                    <br/>
                                    <div class="col-md-12 form-group">
                                        <textarea class="form-control" id="store_custom_js" name="store_custom_js" v-model="store_settings.store_custom_js"></textarea>
                                        <label class="form-label" for="store_custom_js">Custom Javascript (Paste the codes you were given)</label>
                                        <small>This allows you to add popular tools you use to your store site. e.g. Drift, Drip, Intercom, Tawk.to</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="store_paid_notifications_email" name="store_paid_notifications_email" type="email"
                                           class="validate" v-model="store_settings.store_paid_notifications_email">
                                    <label class="form-label" for="store_paid_notifications_email">Email to send notifications on paid orders</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                Save Store Settings
                            </button>
                    </form>
                </div>
            @endif
            <div class="col-md-12 col-lg-6">
                <form action="/mec/ecommerce-logistics" method="post" class="col s12">
                    {{ csrf_field() }}
                    <div class="row col-md-12">
                        @component('layouts.blocks.tabler.empty-fullpage')
                            @slot('title')
                                Setup Payment Provider
                            @endslot
                            To integrate online payment for your store, you need to integrate one of our payment partners.<br><br/>
                            You need to create a vendor account, and install the appropriate integration from the "Integration" section.<br/><br/>     

                            <a class="btn btn-secondary btn-sm" href="{{ route('integrations-main') }}">
                                Add Integration
                            </a>
                            @slot('buttons')
                                <a class="btn btn-primary btn-sm" href="{{ env('DORCAS_STORE_PAYMENT_VENDOR_URL', 'https://dorcas.ravepay.co/auth/') }}" target="_blank">
                                    Create Vendor Account
                                </a>
                            @endslot
                        @endcomponent
                    </div>

                    <div class="row col-md-12">
                        @component('layouts.blocks.tabler.empty-fullpage')
                            @slot('title')
                                Setup Logistics Provider
                            @endslot
                            There are 2 ways to handle shipment (delivery) of your orders: <br/><br/>
                            <ol>
                                <li>You can choose to handle your shipments yourself and have customers choose from routes whose prices you set manually</li>
                                <li>You can choose to have a logistics provider handle shipping; shipping costs are automatically calculated when your customers enter their delivery addresses</li>
                            </ol>
                            <br/>
                            If you choose (2) above, you have the following options:
                            <ul>
                                <li>you can decide to have the logistics provider come to pick orders at your location</li>
                                <li>you can drop at a fulfilment centre (if available)</li>
                            </ul>

                            <br/>
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
                            <br/>
                            <fieldset class="form-fieldset">
                                Choose Fulfilment Option: 
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <select id="logistics_fulfilment" name="logistics_fulfilment" class="form-control" v-model="logistics_settings.logistics_fulfilment" required>
                                            <option value="fulfilment_pickup">Provider to Come and Pickup</option>
                                            <option value="fulfilment_centre">Deliver Goods to Fulfilment Centre Myself</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="col-md-12">
                                <button class="btn btn-primary" type="submit" name="action">Save Logistics Options</button>
                            </div>

                            @slot('buttons')
                            @endslot
                        @endcomponent
                    </div>
                </form>
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
                advanced_store_settings: false
            }
        });
    </script>
@endsection

