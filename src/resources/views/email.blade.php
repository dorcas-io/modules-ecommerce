@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9">

	    <div class="row container" id="ecommerce-emails">
	        <div class="row mt-3" v-if="emails.length > 0">
	            <webmail-account class="col s12 m4" v-for="(email, index) in emails" :key="email.user" :email="email" :index="index" v-on:delete-email="deleteEmail"></webmail-account>
	        </div>
	        <div class="col-md-12" v-if="emails.length === 0">
		        @component('layouts.blocks.tabler.empty-fullpage')
		            @slot('title')
		                Email Manager
		            @endslot
		            You can create one or more email accounts for your domain<br>
		            @if (empty($domains) || $domains->count() === 0)
		            	<p>You need to have a <strong>registered domain name</strong> before you can proceed.</p>
		            @elseif (!empty($domains) && $domains->count() > 0 && !$isHostingSetup)
		                <p>You have secured <strong>{{ $domains->first()->domain }}</strong> as your primary domain name.</p>
		                <p>Click the button above to setup the web-hosting space (that will host your email content)</p>
		            @elseif (!empty($domains) && $domains->count() > 0 && $isHostingSetup)
		            @endif
		            @slot('buttons')
		                @if (empty($domains) || $domains->count() === 0)
		                    <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
		                        Add or Buy A Domain
		                    </a>
		                @elseif (!empty($domains) && $domains->count() > 0 && !$isHostingSetup)
		                    <form action="{{ route('ecommerce-website') }}" method="post">
		                        {{ csrf_field() }}
		                        <button :class="{'btn-loading' : isHostingRequestProcessing }" class="btn btn-primary" name="action" value="setup_hosting" v-on:click="requestHosting">
		                            Setup Hosting for {{ $domains->first()->domain }}
		                        </button>
		                    </form>
		                @elseif (!empty($domains) && $domains->count() > 0 && $isHostingSetup)
		                    <a class="btn btn-success" href="#" v-on:click.prevent="createEmail">
		                        Add Email Account
		                    </a>
		                @endif
		            @endslot
		        @endcomponent
	        </div>
	        @include('ecommerce.modals.new-email')
	    </div>
    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        var vm = new Vue({
            el: '#ecommerce-emails',
            data: {
                domains: {!! json_encode(!empty($domains) ? $domains : []) !!},
                emails: {!! json_encode(!empty($emails) ? $emails : []) !!},
                email: {username: '', domain: '', quota: 25},
                isHostingRequestProcessing: false
            },
            methods: {
                requestHosting: function () {
                    this.isHostingRequestProcessing = true;
                },
                createEmail: function () {
                    $('#add-email-modal').modal('show');
                },
                deleteEmail: function (index) {
                    let email = typeof this.emails[index] !== 'undefined' ? this.emails[index] : null;
                    if (email === null) {
                        return;
                    }
                    let context = this;
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You are about to delete email " + email.login,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, delete it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (email_delete) => {
	                        return axios.delete("/mec/ecommerce-emails/" + email.user)
	                            .then(function (response) {
	                                console.log(response);
	                                context.emails.splice(index, 1);
	                                return swal("Deleted!", "The email was successfully deleted.", "success");
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
	                                return swal("Delete Failed", message, "warning");
	                            });
		                },
		                allowOutsideClick: () => !Swal.isLoading()
                    });



                }
            }
        });

        new Vue({
            el: '#sub-menu-action',
            data: {

            },
            methods: {
                createEmail: function () {
                    vm.createEmail();
                }
            }
        })
    </script>
@endsection

