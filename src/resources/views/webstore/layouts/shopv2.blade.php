<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Language" content="en" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @section('head_meta')
        <meta name="description" content="">
        <meta name="keywords" content="" />
        <meta name="author" content="" />
    @show
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#4188c9">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <link rel="icon" href="{{ cdn('favicon.ico') }}" type="image/x-icon"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ cdn('favicon.ico')  }}" />
    <!-- Generated: 2018-04-16 09:29:05 +0200 -->
    <title>@section('head_title'){{ $page['title'] ?? 'We\'re Sorry' }} | {{ !empty($partnerHubConfig['product_name']) ? $partnerHubConfig['product_name'] : config('app.name') }}@show</title>
    @include('layouts.blocks.tabler.favicons')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <!-- Dashboard Core -->
    <link href="{{ cdn('apps/tabler/css/dashboard.css') }}" rel="stylesheet" />
    <!-- c3.js Charts Plugin -->
    <link href="{{ cdn('apps/tabler/plugins/charts-c3/plugin.css') }}" rel="stylesheet" />
    <link href="{{ cdn('apps/tabler/plugins/iconfonts/plugin.css') }}" rel="stylesheet" />
    <link href="{{ cdn('apps/tabler/plugins/prismjs/plugin.css') }}" rel="stylesheet" />
    <link href="{{ cdn('apps/tabler/css/bootstrap-table.min.css') }}" rel="stylesheet" />
    <link href="{{ cdn('css/hopscotch.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/45fbd50176.js"></script>
    <style type="text/css">
        .combodate {
            display: block;
            width: 100%;
        }
        .combodate .form-control {
            display: inline-block;
        }
        /*.dropdown>.dropdown-menu {
            transition: 0s !important;
            transition-delay: 1s !important;
        }
        .dropdown:hover>.dropdown-menu {
            display: block !important;
            transition-delay: 0s !important;
        }*/
    </style>
    @yield('head_css')
    @yield('head_js')
</head>
<body @section('body_class') class="" @show>
<div class="page" id="tabler-page">
    @section('body')
        <div class="page-main">
            @section('body_header')
                <div class="header py-4" id="tabler-header">
                    <div class="container">
                        <div class="d-flex">
                            @include('layouts.blocks.tabler.header-logo')
                            <div class="d-flex order-lg-2 ml-auto" id="header-options">

                                @section('body_header_notification')
                                    @include('layouts.blocks.tabler.notification')
                                @show
                                @if (\Illuminate\Support\Facades\Auth::check() && !empty($dorcasUser))
                                    @include('layouts.blocks.tabler.auth-options')
                                @endif
                            </div>
                            <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                                <span class="header-toggler-icon"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @show
            @section('body_main_content')
                    <div class="my-3 my-md-5" id="tabler-content">
                        <div class="container">
                            @section('body_content_header')
                                <div class="page-header">
                                    <h1 class="page-title">
                                        @if (!empty(request('overview_mode')))
                                            Learning Mode &raquo; <a class="btn btn-primary btn-sm" href="{{ route('welcome-overview') }}">Return to Overview Page</a>
                                        @else
                                            {!! $page['header']['title'] ?: 'Dashboard' !!}
                                        @endif
                                    </h1>
                                    @yield('body_content_header_extras')
                                </div>
                            @show
                            <div class="container clearfix" id="main_content_container">
                                @yield('body_main_content_container_body')
                            </div>
                        </div>
                    </div>
            @show
        </div>
        @section('footer')
            @section('footer_top')
                &nbsp;
            @show
            @include('layouts.blocks.tabler.footer')
        @show
    @show
</div>
<!-- Dashboard Core -->
<script src="{{ cdn('apps/tabler/js/vendors/jquery-3.2.1.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/vendors/bootstrap.bundle.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/plugins/prismjs/js/prism.pack.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/dashboard.js') }}"></script>
<!-- c3.js Charts Plugin -->
<!-- <script src="{{ cdn('apps/tabler/plugins/charts-c3/js/d3.v3.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/plugins/charts-c3/js/c3.min.js') }}"></script> -->
<!-- Input Mask Plugin -->
<script src="{{ cdn('apps/tabler/plugins/input-mask/js/jquery.mask.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/core.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/axios.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/moment.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/vue.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/sweetalert.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/voca.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/tabler-components.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/bootstrap-table/bootstrap-table.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/moment.min.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/lib/combodate.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/app.js') }}"></script>


<!--custom-script.js - Add your own theme custom JS-->
<script src="{{ cdn('apps/tabler/js/custom-vue.js') }}"></script>
<script src="{{ cdn('apps/tabler/js/custom_script.js') }}"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>

<script src="{{ cdn('js/hopscotch.js') }}"></script>
<script src="{{ cdn('js/dorcas_hub_tours.js') }}"></script>


@if (app()->environment() === 'production')
    @include('webstore.layouts.blocks.production-js')
@endif
@if (!empty($storeSettings['store_ga_tracking_id']))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $storeSettings['store_ga_tracking_id'] }}UA-116344553-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $storeSettings['store_ga_tracking_id'] }}');
    </script>
@endif
{!! $storeSettings['store_custom_js'] ?? '' !!}

@yield('body_js')

</body>
</html>
