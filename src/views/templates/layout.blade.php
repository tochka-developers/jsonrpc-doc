<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @section('metatags')
    @show
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('vendor/jsonrpcdoc/css/highlight.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('vendor/jsonrpcdoc/css/theme.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ asset('vendor/jsonrpcdoc/css/style.css') }}" type="text/css"/>
</head>
<body class="wy-body-for-nav">
<div class="wy-grid-for-nav">
    <nav data-toggle="wy-nav-shift" class="wy-nav-side">
        <div class="wy-side-scroll">
            <div class="wy-side-nav-search">
                @isLumen
                    <a href="{{ route('jsonrpcdoc.main') }}" class="icon icon-home"> {{ $serviceName }}</a>
                @else
                    <a href="{{ route('jsonrpcdoc.main', [], false) }}" class="icon icon-home"> {{ $serviceName }}</a>
                @endisLumen

                <div class="version">{{ $serviceVersion }}</div>

                {{--@include('jsonrpcdoc::templates.searchbox')--}}
            </div>

            <div class="wy-menu wy-menu-vertical" data-spy="affix" role="navigation" aria-label="main navigation">
                @isLumen
                    @include('jsonrpcdoc::templates.menuLumen', ['exposed' => false])
                @else
                    @include('jsonrpcdoc::templates.menu', ['exposed' => false])
                @endisLumen
            </div>
        </div>
    </nav>

    <section data-toggle="wy-nav-shift" class="wy-nav-content-wrap">

        <nav class="wy-nav-top" aria-label="top navigation">
            <i data-toggle="wy-nav-top" class="fa fa-bars"></i>
            @isLumen
                <a href="{{ route('jsonrpcdoc.main') }}">{{ env('APP_NAME') }}</a>
            @else
                <a href="{{ route('jsonrpcdoc.main', [], false) }}">{{ env('APP_NAME') }}</a>
            @endisLumen
        </nav>

        <div class="wy-nav-content">
            <div class="rst-content">

                @isLumen
                    @include('jsonrpcdoc::templates.breadcrumbsLumen')
                @else
                    @include('jsonrpcdoc::templates.breadcrumbs')
                @endisLumen

                <div role="main" class="document" itemscope="itemscope" itemtype="http://schema.org/Article">
                    <div itemprop="articleBody">
                        @yield('content')
                    </div>
                </div>

                @include('jsonrpcdoc::templates.footer')
            </div>
        </div>

    </section>

</div>

<script type="text/javascript" src="{{ asset('vendor/jsonrpcdoc/js/highlight.pack.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/jsonrpcdoc/js/theme.js') }}"></script>

</body>
</html>