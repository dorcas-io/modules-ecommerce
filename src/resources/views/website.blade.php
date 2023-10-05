@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9" id="ecommerce-website">

        @component('layouts.blocks.tabler.empty-fullpage')
            @slot('title')
                Website Builder
            @endslot
            Dorcas website builder makes it easy to create your own website page(s) by using a drag-and-drop interface.<br>

            @if (empty($subdomains) || $subdomains->count() === 0)
                <p>You need to have a <strong>reserved Dorcas subdomain prefix</strong> before you can proceed</p>

            @elseif ( (!empty($subdomains) && $subdomains->count() > 0) && (empty($domains) || $domains->count() === 0) )
                <!-- <p>You have secured <strong>{{ $subdomains->first()->prefix }}.{{ $subdomains->first()->domain["data"]["domain"] }}</strong> as your Hub Subdomain.</p> -->
                <br/>
                <p>Your website will be available at <strong><a href="https://{{ $subdomains->first()->prefix }}.dorcas.website" target="_blank">https://{{ $subdomains->first()->prefix }}.dorcas.website</a></strong></p>
                <p>Click the button above to start building your website</p>
                <small><strong>Note:</strong> You will need a domain name &amp; paid account later on to be able to publish your website to the whole world</small>

            @elseif ( (!empty($subdomains) && $subdomains->count() > 0) && (!empty($domains) && $domains->count() > 0) && !$isOnPaidPlan)
                <p>You have secured <strong>{{ $domains->first()->domain }}</strong> as your primary domain name and setup your web-hosting space.</p>
                <p>Click the button above to start building your website</p>
                <small><strong>Note:</strong> You will need a paid account later on to be able to publish your website to the whole world</small>

            @elseif ((!empty($subdomains) && $subdomains->count() > 0) && !empty($domains) && $domains->count() > 0 && $isOnPaidPlan && !$isHostingSetup)
                <p>You have secured <strong>{{ $domains->first()->domain }}</strong> as your primary domain name.</p>
                <p>Click the button above to setup your web-hosting space</p>

            @elseif (!empty($subdomains) && $subdomains->count() > 0 && !empty($domains) && $domains->count() > 0 && $isOnPaidPlan && $isHostingSetup)
                <p>You have secured <strong>{{ $domains->first()->domain }}</strong> as your primary domain name and setup your web-hosting space.</p>
                <p>Click the button above to start building your website</p>

            @endif
            @slot('buttons')
                @if (empty($subdomains) || $subdomains->count() === 0)
                    <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                        Reserve Subdomain
                    </a>
                @elseif ((!empty($subdomains) && $subdomains->count() > 0) && (empty($domains) || $domains->count() === 0))
                    <a class="btn btn-success" target="_blank" href="{{ config('dorcas-builder.url') . '/auth/via_dorcas?' . http_build_query($authParams ?: []) }}">
                        Start Building
                    </a>
                    &nbsp;
                    <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                        Buy Domain Name
                    </a>
                @elseif ((!empty($subdomains) && $subdomains->count() > 0) && !empty($domains) && $domains->count() > 0 && !$isOnPaidPlan)
                    <a class="btn btn-success" target="_blank" href="{{ config('dorcas-builder.url') . '/auth/via_dorcas?' . http_build_query($authParams ?: []) }}">
                        Start Building
                    </a>
                @elseif ((!empty($subdomains) && $subdomains->count() > 0) && !empty($domains) && $domains->count() > 0 && $isOnPaidPlan && !$isHostingSetup)
                    <form action="" method="post">
                        {{ csrf_field() }}
                        <button :class="{'btn-loading' : isHostingRequestProcessing }" class="btn btn-primary" name="action" value="setup_hosting" v-on:click="requestHosting">
                            Setup Hosting for {{ $domains->first()->domain }}
                        </button>
                    </form>
                @elseif ((!empty($subdomains) && $subdomains->count() > 0) && !empty($domains) && $domains->count() > 0 && $isOnPaidPlan && $isHostingSetup)
                    <a class="btn btn-success" target="_blank" href="{{ config('dorcas-builder.url') . '/auth/via_dorcas?' . http_build_query($authParams ?: []) }}">
                        Start Building
                    </a>
                @endif
            @endslot
        @endcomponent

    </div>

</div>


@endsection
@section('body_js')
    <script>
        new Vue({
            el: '#ecommerce-website',
            data: {
                isHostingRequestProcessing: false,
                subdomains: {!! json_encode($subdomains) !!}
            },
            mounted: function() {
                console.log(this.subdomains);
            },
            methods: {
                requestHosting: function () {
                    this.isHostingRequestProcessing = true;
                }
            }
        })
    </script>
@endsection