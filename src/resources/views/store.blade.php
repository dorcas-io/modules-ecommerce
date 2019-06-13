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
	    					<small class="text-muted">{{ !empty($subdomain) ? $subdomain . '/store' : 'Not Reserved' }}</small>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>



        <div class="row col-md-12">
            @if (!empty($subdomain))
                <div class="col-md-12 col-lg-6">
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
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="store_terms_page" name="store_terms_page" type="url" class="validate" v-model="store_settings.store_terms_page">
                                    <label class="form-label" for="store_terms_page">Terms of Service URL</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <input class="form-control" id="store_ga_tracking_id" name="store_ga_tracking_id" type="text" class="validate" v-model="store_settings.store_ga_tracking_id">
                                    <label class="form-label" for="store_ga_tracking_id" v-bind:class="{'active': typeof store_settings.store_ga_tracking_id !== 'undefined' && store_settings.store_ga_tracking_id.length > 0}">Google Analytics Tracking ID (UA-XXXXXXXXX-X)</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <textarea class="form-control" id="store_custom_js" name="store_custom_js" v-model="store_settings.store_custom_js"></textarea>
                                    <label class="form-label" for="store_custom_js">Custom Javascript (Paste the codes you were given)</label>
                                    <small>This allows you to add popular tools you use to your store site. e.g. Drift, Drip, Intercom, Tawk.to</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                Save Settings
                            </button>
                    </form>
                </div>
            @endif
            <div class="col-md-12 col-lg-6">
		        @component('layouts.blocks.tabler.empty-fullpage')
		            @slot('title')
		                Setup Online Payment
		            @endslot
		            To integrate online payment for your store, you need to integrate one of our payment partners.<br><br/>
		            You need to create a vendor account, and install the appropriate integration from the "Integration" section.<br/><br/>     
                    <a class="btn btn-primary btn-sm" href="https://dorcas.ravepay.co/auth/" target="_blank">
                        Create Vendor Account
                    </a>
                    &nbsp;
                    <a class="btn btn-secondary btn-sm" href="{{ route('integrations-main') }}">
                        Add Integration
                    </a>
		            @slot('buttons')
		            @endslot
		        @endcomponent
            </div>
            @if (empty($subdomain))
                <div class="col-md-6">
			        @component('layouts.blocks.tabler.empty-fullpage')
			            @slot('title')
			                No Subdomainn
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
                store_settings: {!! json_encode($storeSettings) !!}
            }
        });
    </script>
@endsection

