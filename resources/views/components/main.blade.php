<html dir="{{ $dir }}" lang="{{ $lang }}">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <title>{{ config('app.name') }} - @yield('title')</title>

        <x-fb-report::styles :data="$__data" />

        {!! $__data['__reporter']->getStyles($__data) !!}

        {!! $__data['__reporter']->getHtmlHead($__data) !!}

        @if ($dir === 'rtl')
            <x-fb-report::rtl-support :data="$__data" />
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
            {!! $__data['__reporter']->getReportBody($__data) !!}
        </div>
    </body>

</html>
