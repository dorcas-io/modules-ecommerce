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
            Dorcas website builder makes it easy to create your own web page by using a drag-and-drop interface.<br>
            You need to have a <strong>reserved subdomain name</strong> or <strong>registered domain name</strong> before you can proceed.
            @slot('buttons')
                @if (empty($domains) || $domains->count() === 0)
                    <a class="btn btn-primary" href="{{ route('ecommerce-domains') }}">
                        Use Domains Manager
                    </a>
                @elseif (!empty($domains) && $domains->count() > 0 && !$isHostingSetup)
                    <form action="" method="post">
                        {{ csrf_field() }}
                        <button :class="{'btn-loading' : isHostingRequestProcessing }" class="btn btn-primary" name="action" value="setup_hosting" v-on:click="requestHosting">
                            Setup Hosting for {{ $domains->first()->domain }}
                        </button>
                    </form>
                @elseif (!empty($domains) && $domains->count() > 0 && $isHostingSetup && !$isOnPaidPlan)
                    <a class="btn btn-success" target="_blank" href="{{ config('dorcas-builder.url') . '/auth/via_dorcas?' . http_build_query($authParams ?: []) }}">
                        Start Building
                    </a>
                    <p>You will need a paid account before you publish your website</p>
                @elseif (!empty($domains) && $domains->count() === 0 && $isHostingSetup && $isOnPaidPlan)
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
            el: '#website-setup',
            data: {
                isHostingRequestProcessing: false
            },
            methods: {
                requestHosting: function () {
                    this.isHostingRequestProcessing = true;
                }
            }
        })
    </script>
@endsection