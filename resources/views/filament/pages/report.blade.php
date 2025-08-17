@php
    $height = !!$this->returnUrl ? 'height: calc(100vh - 264px);' : 'height: calc(100vh - 200px);';
@endphp

<x-filament-panels::page>

    @if ($this->returnUrl)
        <x-filament::button color="gray" tag="a" :href="$this->returnUrl"
            style="margin-left: auto; margin-right: auto; width: 240px;">
            @lang('fb-report::fb-report.return')
        </x-filament::button>
    @endif

    @if ($this->reporter?->getShowHtml())
        <iframe src="data:text/html;charset=utf-8;base64,{{ $base64Pdf }}"
            style="background: transparent; border: none; width: 100%; {{ $height }};"></iframe>
    @else
        <x-filament::button tag="a" href="data:application/pdf;base64,{{ $base64Pdf }}" download="report.pdf"
            class="show-on-small">

            @lang('fb-report::fb-report.download')

        </x-filament::button>

        <iframe src="data:application/pdf;base64,{{ $base64Pdf }}" class="show-on-wide"
            style="background: transparent; border: none; width: 100%; {{ $height }};">
        </iframe>
    @endif

</x-filament-panels::page>
