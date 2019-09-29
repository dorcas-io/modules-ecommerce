<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="Dorcas.nd" />
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/bootstrap.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/dark.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/font-icons.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/animate.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/magnific-popup.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/webstore/css/responsive.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/blog/css/daterangepicker.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ cdn('apps/blog/css/select-boxes.css') }}" type="text/css" />
    <link href="{{ cdn('vendors/sweetalert/dist/sweetalert.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ cdn('vendors/summernote/summernote.css') }}" type="text/css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @section('head_meta')
        <meta name="description" content="">
        <meta name="keywords" content="" />
        <meta name="author" content="" />
    @show
    <title>@section('head_title'){{ $page['title'] or 'We\'re Sorry' }} | {{ !empty($partnerHubConfig) ? $partnerHubConfig['product_name'] : config('app.name') }}@show</title>
    @yield('head_css')
</head>
<body @section('body_class')class="stretched" @show>
@section('body')
    <div id="wrapper" class="clearfix">
        @section('header')
            @include('blog.layouts.blocks.header')
        @show
        @section('body_main')
            @section('body_main_heading')
                <section id="page-title">
                    <div class="container clearfix">
                        <!-- <h1>{{ $blogName }}</h1> -->
                        <h1>{{ $page['header']['title'] }}</h1>
                        <span>{{ $page['header']['subTitle'] or '' }}</span>
                        @include('blog.layouts.blocks.breadcrumbs')
                    </div>
                </section><!-- #page-title end -->
            @show
            @section('body_main_content')
                <section id="content">
                    <div class="content-wrap">
                        @section('body_main_content_container')
                            <div class="container clearfix" id="main_content_container">
                                @yield('body_main_content_container_body')
                            </div>
                        @show
                    </div>
                </section>
            @show
        @show
        @section('footer')
            @include('blog.layouts.blocks.footer')
        @show
    </div><!-- #wrapper end -->
@show
<div id="gotoTop" class="icon-angle-up"></div>
<!-- axios Library -->
<script type="text/javascript" src="{{ cdn('js/axios.min.js') }}"></script>
<!-- Vue.js Library -->
<script type="text/javascript" src="{{ cdn('js/vue.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/webstore/js/jquery.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/webstore/js/plugins.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/webstore/js/functions.js') }}"></script>
<script type="text/javascript" src="{{ cdn('js/moment.js') }}"></script>
<script type="text/javascript" src="{{ cdn('vendors/summernote/summernote.min.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/blog/js/daterangepicker.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/blog/js/select-boxes.js') }}"></script>
<script type="text/javascript" src="{{ cdn('apps/webstore/js/dorcas.js') }}"></script>
<script type="text/javascript" src="{{ cdn('vendors/sweetalert/dist/sweetalert.min.js') }}"></script>
@if (app()->environment() === 'production')
    @include('blog.layouts.blocks.production-js')
@endif
<script>
    $(function () {
        $('.summernote').summernote({
            height: 300
        });
        $('.select2').select2();
        $('.single-date-picker-to-future').daterangepicker({
            timePicker: true,
            autoclose: true,
            startDate: moment().startOf('hour'),
            singleDatePicker: true,
            showDropdowns: true,
            minYear: {!! json_encode(date('Y')) !!},
            locale: {
                format: 'DD-MM-YYYY HH:mm'
            }
        });
    });
</script>
@yield('body_js')
</body>
</html>