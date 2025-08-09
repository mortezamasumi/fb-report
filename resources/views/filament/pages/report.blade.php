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
        <iframe src="data:text/html;charset=utf-8;base64,{{ $base64Pdf }}" class="md:hidden block"
            style="background: transparent; border: none; margin: auto; width: 240px; height:80px;">
        </iframe>

        <iframe src="data:text/html;charset=utf-8;base64,{{ $base64Pdf }}" class="md:block hidden w-full"
            style="background: transparent; border: none; width: 100%; {{ $height }};">
        </iframe>
    @else
        <iframe src="data:application/pdf;base64,{{ $base64Pdf }}" class="md:hidden block"
            style="background: transparent; border: none; margin: auto; width: 240px; height:80px;">
        </iframe>

        <iframe src="data:application/pdf;base64,{{ $base64Pdf }}" class="md:block hidden w-full"
            style="background: transparent; border: none; width: 100%; {{ $height }};">
        </iframe>
    @endif

</x-filament-panels::page>
