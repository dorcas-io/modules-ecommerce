@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9" id="ecommerce-domains">
        <div id="ecommerce-domains-heading">
            You can reserve your eCommerce <strong>@{{ domain_title }}</strong>, add or purchase <strong>custom domain name(s)</strong> (if enabled) for your business:
        </div>
        <ul class="nav nav-tabs nav-justified">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#dorcas_subdomain">Store Address</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" data-toggle="tab" href="#custom_domains">Custom Domains &amp; Web-Hosting</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane container active" id="dorcas_subdomain">
                <br/>
                @if (!empty($subdomains->first()->prefix))
                    <p>You have secured <strong>{{ $dorcasEdition === 'business' ? $subdomains->first()->prefix : $subdomains->first()->prefix . "." . $subdomains->first()->domain["data"]["domain"] }}</strong> as your @{{ domain_title }}.</p>
                @endif
				<div class="row col-md-12">
					<div class="card" v-for="(domain, index) in domains" :key="domain.id">
						<div class="card-body" v-if="index == 0">
							<p>https://@{{ domain_value(domain) }}</p>
						</div>
						<div class="card-footer" v-if="show_domain_options">
							<a href="#" v-id="dorcasEdition != 'business'" class="btn btn-warning btn-sm" v-on:click.prevent="releaseDomain(index)">Delete @{{ domain_title }}</a>
							&nbsp;
							<a class="btn btn-primary btn-sm" target="_blank" v-bind:href="'https://' + domain_value(domain)">Visit @{{ domain_title }}</a>
						</div>
					</div>
				</div>
                <div class="row col-md-12" v-if="domains.length === 0 && dorcasEdition != 'business'">
                    @component('layouts.blocks.tabler.empty-fullpage')
                        @slot('title')
                            No @{{ domain_title }}
                        @endslot
                        Set your @{{ domain_title }}</strong><!-- , e.g. <strong>@{{ domain_example }} -->
                        @slot('buttons')
                            <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#dorcas-sub-domain-modal">Reserve @{{ domain_title.toUpperCase() }}</a>
                        @endslot
                    @endcomponent
                </div>
            </div>
            <div class="tab-pane container" id="custom_domains" v-bind:class="{'disabled': is_on_paid_plan}">
                <br/>
				<div class="row col-md-12">
					<div class="card" v-for="(domain, index) in domains" :key="domain.id">
						<div class="card-body" v-if="index != 0">
							<p>@{{ domain.domain }}</p>
						</div>
						<div class="card-footer">
							<a class="btn btn-primary btn-sm" target="_blank" v-bind:href="'http://www.' + domain.domain">Visit</a>
							&nbsp;
							<!-- <a href="#" class="btn btn-danger btn-sm" v-on:click.prevent="removeDomain(index)">Release</a> -->
						</div>
					</div>
	            </div>
                <div class="row col-md-12" v-if="domains.length >= 0">
                    @component('layouts.blocks.tabler.empty-fullpage')
                        @slot('title')
                            Domains &amp; Hosting Setup
                        @endslot
                        @if ((empty($domains) || $domains->count() === 0) && !$isHostingSetup)
                            To begin your e-commerce setup, you'll need to either use a domain name you already own OR buy a new one.
                        @elseif (!empty($domains) && $domains->count() > 0 && !$isHostingSetup)
                            To begin your web-hosting setup, click the button above
                        @elseif (!empty($domains) && $domains->count() > 0 && $isHostingSetup && !empty($nameservers))
                            If you added a domain you already own, you will need to set the following nameserver
                                entries in your domain's DNS.<br>
                            @foreach ($nameservers as $ns)
                                <small>{{ $ns }}</small><br>
                            @endforeach
                            <br>
                            If you bought a domain here, you can simply ignore the above instruction since it
                            has already been done for you.
                        @endif
                        @slot('buttons')
                            @if (!empty($domains) && $domains->count() > 0 && !$isHostingSetup && $isOnPaidPlan)
                                <form action="" method="post">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="domain" value="{{ $domains->first()->domain }}" />
                                    <button :class="{'btn-loading' : is_setting_up_hosting }" class="btn btn-primary btn-sm" name="setup_hosting" value="setup_hosting" v-on:click="setRequestingHosting">
                                        Setup Hosting for {{ $domains->first()->domain }}
                                    </button>
                                </form>
                            @endif
                            <a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#buy-domain-modal" v-if="domains.length == 0">
                                Buy New Domain
                            </a>
                            &nbsp;
                            <a href="#" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#add-domain-modal" v-if="domains.length == 0">
                                Add Existing Domain
                            </a>
                        @endslot
                    @endcomponent
                </div>
            </div>
        </div>

    </div>
    @include('modules-ecommerce::modals.dorcas-sub-domain')
    @include('modules-ecommerce::modals.buy-domain')
    @include('modules-ecommerce::modals.add-domain')
</div>


@endsection
@section('body_js')
    <script type="text/javascript">


    
        new Vue({
            el: '#ecommerce-domains-heading',
            data: {
                dorcasEdition:  '{!! $dorcasEdition !!}'
            },
            computed: {
                domain_title: function() {
                    return this.dorcasEdition == "business" ? 'primary domain' : 'subdomain';
                }
            },
            methods: {
               
            }
        });

        new Vue({
            el: '#dorcas-sub-domain-modal',
            data: {
                domain: '',
                is_available: false,
                is_queried: false,
                is_querying: false,
                dorcasEdition:  '{!! $dorcasEdition !!}'
            },
            computed: {
                actual_domain: function () {
                    return this.domain.replace(' ', '').toLowerCase().trim();
                }
            },
            methods: {
                removeStatus: function () {
                    this.is_available = false;
                    this.is_queried = false;
                    this.is_querying = false
                    $('#domain_result').html('...');
                },
                checkAvailability: function () {
                    var context = this;
                    this.is_querying =  true;
                    $('#domain_result').html('...');
                    axios.get("/mec/ecommerce-domains-issuances-availability", {
                        params: {id: context.actual_domain}
                    }).then(function (response) {
                        //console.log(response);
                        context.is_querying = false;
                        context.is_queried = true;
                        context.is_available = response.data.is_available;
                        //Materialize.toast(context.is_available ? 'The subdomain is available' : 'The subdomain is unavailable', 4000);
                        $('#domain_result').html(context.is_available ? 'This choice is available' : 'The choice is unavailable');
                    })
                        .catch(function (error) {
                            var message = '';
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                //var e = error.response.data.errors[0];
                                //message = e.title;
                                var e = error.response;
                                message = e.data.message;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            context.is_querying = false;
                            //Materialize.toast('Error: '+message, 4000);
                            swal("Error", message, "warning");
                        });
                }
            }
        });
        new Vue({
            el: '#dorcas_subdomain',
            data: {
                domains: {!! json_encode($subdomains) !!},
                dorcasEdition:  '{!! $dorcasEdition !!}'
            },
            mounted: function() {
            	//console.log("Sub")
            	//console.log(this.domains)
            },
            computed: {
                domain_title: function() {
                    return this.dorcasEdition == "business" ? 'Store Address' : 'Store Address';
                },
                domain_example: function() {
                    return this.dorcasEdition == "business" ? 'mybusiness.com' : 'mybusiness.parentdomain.com';
                },
                show_domain_options: function() {
                    return this.dorcasEdition == "business" ? false : true;
                }
                // domain_value: function(subdomain) {
                //     return this.dorcasEdition == "business" ? subdomain.prefix : subdomain.prefix + "." + subdomain.domain.data.domain;
                // }
            },
            methods: {
                domain_value: function(subdomain) {
                    return this.dorcasEdition == "business" ? subdomain.prefix : subdomain.prefix + "." + subdomain.domain.data.domain;
                },
                releaseDomain: function (index) {
                    var subdomain = this.domains[index];
                    //console.log(subdomain, index);
                    var context = this;
                    //var name = subdomain.prefix + '.' + subdomain.domain.data.domain;
                    var name = this.domain_value(subdomain);
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to delete the address " + name + '\n \n You must choose another after for your store to be active',
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (domain_release) => {
		                    return axios.delete("/mec/ecommerce-domains-issuances/" + subdomain.id)
		                        .then(function (response) {
		                            //console.log(response);
		                            context.domains.splice(index, 1);
		                            return swal("Released!", "The domain was successfully released.", "success");
		                        })
		                        .catch(function (error) {
		                            var message = '';
		                            console.log(error);
		                            if (error.response) {
		                                // The request was made and the server responded with a status code
		                                // that falls out of the range of 2xx
		                                //var e = error.response.data.errors[0];
		                                //message = e.title;
		                                var e = error.response;
		                                message = e.data.message;
		                            } else if (error.request) {
		                                // The request was made but no response was received
		                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
		                                // http.ClientRequest in node.js
		                                message = 'The request was made but no response was received';
		                            } else {
		                                // Something happened in setting up the request that triggered an Error
		                                message = error.message;
		                            }
		                            return swal("Release Failed", message, "warning");
		                        });
		                },
		                allowOutsideClick: () => !Swal.isLoading()
                    });
                }
            }
        });

        new Vue({
            el: '#custom_domains',
            data: {
                domains: {!! json_encode(!empty($domains) ? $domains : []) !!},
                is_setting_up_hosting: false,
                is_on_paid_plan: {!! json_encode($isOnPaidPlan) !!}
            },
            mounted: function() {
            	//console.log("Custom")
            	//console.log(this.domains)
            },
            methods: {
                removeDomain: function (index) {
                    let context = this;
                    let domain = this.domains[index] !== 'undefined' ? this.domains[index] : {};
                    if (typeof domain.id === 'undefined') {
                        return;
                    }
                    Swal.fire({
                        title: "Remove Domain?",
                        text: "You are about to remove domain " + domain.domain + ", and associated hosting from your "+
                            "account. For domains purchased via your account, this does not mean the domain will be " +
                            "deleted from the registrar.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (domain_remove) => {
	                        return axios.delete("/mec/ecommerce-domains/" + domain.id)
	                            .then(function (response) {
	                                console.log(response);
	                                context.domains.splice(index, 1);
	                                return swal("Deleted!", "The domain was successfully deleted from your account.", "success");
	                            })
	                            .catch(function (error) {
	                                var message = '';
	                                console.log(error);
	                                if (error.response) {
	                                    // The request was made and the server responded with a status code
	                                    // that falls out of the range of 2xx
	                                    //var e = error.response.data.errors[0];
	                                    //message = e.title;
		                                var e = error.response;
		                                message = e.data.message;
	                                } else if (error.request) {
	                                    // The request was made but no response was received
	                                    // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
	                                    // http.ClientRequest in node.js
	                                    message = 'The request was made but no response was received';
	                                } else {
	                                    // Something happened in setting up the request that triggered an Error
	                                    message = error.message;
	                                }
	                                return swal("Delete Failed", message, "warning");
	                            });
		                },
		                allowOutsideClick: () => !Swal.isLoading()
                    });
                },
                setRequestingHosting: function () {
                    this.is_setting_up_hosting = true;
                },
            }
        });

        new Vue({
            el: '#buy-domain-modal',
            data: {
                domain: '',
                extension: 'com',
                is_available: false,
                is_queried: false,
                is_querying: false,
                wallet:  {!! json_encode($wallet) !!},
                dorcasEdition:  '{!! $dorcasEdition !!}',
                domain_amount: 2000,
                domain_amount_formatted: '',
                is_purchasing: false
            },
            mounted: function() {
                //console.log(headerAuthVue.loggedInUserCompany.extra_data.wallet.NGN.balance)
                this.purchaseDomainOnPayment();
                //console.log(this.wallet_balance);
                //console.log(this.dorcasEdition);
            },
            computed: {
                actual_domain: function () {
                    return this.domain.replace(' ', '').toLowerCase().trim() + '.' + this.extension;
                },
                wallet_balance: function() {
                    return typeof headerAuthVue.loggedInUserCompany.extra_data.wallet.NGN.balance !== undefined ? headerAuthVue.loggedInUserCompany.extra_data.wallet.NGN.balance : 0
                },
                valid_domain_entry: function () {
                    return this.domain !== '';
                }
            },
            watch: {
                extension: function (oldExt, newExt) {
                    if (oldExt !== newExt) {
                        this.is_available = false;
                    }
                }
            },
            methods: {
                purchaseDomainOnPayment() {
                    //open Tab
                    var url = document.location.toString();
                    if (url.match('purchase_domain_valid')) {
                        $('.nav-tabs a[href="#custom_domains"]').tab('show');
                        $('#buy-domain-modal').modal('show');
                        let url_data = url.split('__')[1]
                        this.domain = url_data.split('_')[0];
                        this.extension = url_data.split('_')[1];
                        this.checkAvailability();
                    }
                },
                payForDomain: function() {
                    //console.log("buy"+this.domain_amount);
                    if (this.extension=="com") {
                        this.domain_amount = 5000;
                    } else if (this.extension=="com.ng") {
                        this.domain_amount = 2000;
                    }
                    let domain_plus_extension = this.domain + '_' + this.extension;
                    let fund_amount = this.domain_amount - this.wallet_balance;
                    let item = { display_name : 'Hub Item', variable_name: 'domain_purchase', value: this.domain + '.' + this.extension }
                    this.is_purchasing = true;
                    assistantVue.showPaystackDialog(fund_amount, item, '{{ url()->current() }}'+ '?purchase_domain_valid__' + domain_plus_extension);
                },
                numberWithCommas: function (x) {
                    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                },
                checkAvailability: function () {
                    this.is_purchasing = false;
                    //console.log(this.numberWithCommas(this.domain_amount));
                    var context = this;
                    this.is_querying =  true;
                    if (context.extension=="com") {
                        this.domain_amount = 5000;
                        this.domain_amount_formatted = "5,000";
                    } else if (context.extension=="com.ng") {
                        this.domain_amount = 2000;
                        this.domain_amount_formatted = "2,000";
                    }
                    axios.get("/mec/ecommerce-domains-availability", {
                        params: {domain: context.domain, extension: context.extension}
                    }).then(function (response) {
                        console.log(response);
                        context.is_querying = false;
                        context.is_queried = true;
                        context.is_available = response.data.is_available;
                        //Materialize.toast(context.is_available ? 'The domain is available' : 'The domain is not available', 4000);
                        let balance_msg = 'You can go ahead to purchase and secure it.';
                        if (context.wallet_balance<context.domain_amount) {
                            let topup = this.domain_amount - this.wallet_balance
                            balance_msg = 'You need to top up your Wallet balance with NGN' + context.numberWithCommas(topup) + ' after which you can purchase and secure it';
                        }
                        if (context.is_available) {
                        	swal("Status", 'The domain is available at NGN' + context.domain_amount_formatted + '. ' + balance_msg, "success");
                        } else {
                        	swal("Status", 'The domain is not available', "warning");
                        }
                    }).catch(function (error) {
                        console.log(error)
                            var message = '';
                            if (error.response) {
                                // The request was made and the server responded with a status code
                                // that falls out of the range of 2xx
                                //var e = error.response.data.errors[0];
                                //message = e.title;
                                var e = error.response;
                                message = e.data.message;
                            } else if (error.request) {
                                // The request was made but no response was received
                                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                // http.ClientRequest in node.js
                                message = 'The request was made but no response was received';
                            } else {
                                // Something happened in setting up the request that triggered an Error
                                message = error.message;
                            }
                            context.is_querying = false;
                            //Materialize.toast('Error: '+message, 4000);
                        	swal("Error Checking Domain Availability", message, "warning");
                        });
                },
                removeStatus: function () {
                    this.is_available = false;
                    this.is_queried = false;
                    this.is_querying = false
                },
            }
        });

        /*new Vue({
            el: '#add-domain-modal',
            data: {
            	adding_existing_domain: false
            },
            methods: {
                addExistingDomain: function () {
                    this.adding_existing_domain = true;
                },
            }
        });*/

    </script>
@endsection