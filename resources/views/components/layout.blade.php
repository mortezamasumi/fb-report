<html dir="{{ $dir }}" lang="{{ $lang }}">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <title>{{ config('app.name') }} - @yield('title')</title>

        <!-- Styles section (can be extended by child views) -->
        @stack('styles')

        @stack('custom-styles')

        <!-- Inline head content -->
        @yield('head')

        <!-- RTL-specific CSS definitions -->
        @if ($dir === 'rtl')
            @stack('rtl-support')
        @endif
    </head>

    <body>
        @section('header')
            <htmlpageheader name="Header">
                {!! $__data['__reporter']->getReportHeader($__data) !!}
            </htmlpageheader>

            <sethtmlpageheader name="Header" show-this-page="1" />
        @show

        @section('footer')
            <htmlpagefooter name="Footer">
                {!! $__data['__reporter']->getReportFooter($__data) !!}
            </htmlpagefooter>

            <sethtmlpagefooter name="Footer" show-this-page="1" />
        @show

        <div class="container">
            @yield('before-report')
        </div>

        <div class="container">
            @yield('report-content')
        </div>

        <div class="container">
            @yield('after-report')
        </div>
    </body>

</html>
