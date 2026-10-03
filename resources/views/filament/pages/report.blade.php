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

    @if ($this->showLoadingScreen && $this->generationState === 'pending')
        <div wire:init="generateReport"
            class="flex min-h-[calc(100vh-16rem)] flex-col items-center justify-center gap-4 text-center" role="status"
            aria-live="polite">
            <span aria-hidden="true"
                class="size-9 animate-spin rounded-full border-4 border-gray-200 border-t-primary-600 dark:border-gray-700"></span>
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    @lang('fb-report::fb-report.preparing')
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @lang('fb-report::fb-report.preparing_description')
                </p>
            </div>
        </div>
    @elseif ($this->showLoadingScreen && $this->generationState === 'failed')
        <div class="flex min-h-[calc(100vh-16rem)] flex-col items-center justify-center gap-4 text-center"
            role="alert">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    @lang('fb-report::fb-report.generation_failed')
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @lang('fb-report::fb-report.generation_failed_description')
                </p>
            </div>
            <x-filament::button wire:click="generateReport" wire:loading.attr="disabled" wire:target="generateReport">
                @lang('fb-report::fb-report.retry')
            </x-filament::button>
        </div>
    @endif

    @if (!$this->showLoadingScreen || $this->generationState === 'ready')
        @if ($this->reporter?->getShowHtml())
            <iframe src="data:text/html;charset=utf-8;base64,{{ $base64Pdf }}"
                style="background: transparent; border: none; width: 100%; {{ $height }};"></iframe>
        @else
            <x-filament::button tag="a" href="data:application/pdf;base64,{{ $base64Pdf }}"
                download="report.pdf" class="show-on-small">

                @lang('fb-report::fb-report.download')

            </x-filament::button>

            <iframe src="data:application/pdf;base64,{{ $base64Pdf }}" class="show-on-wide"
                style="background: transparent; border: none; width: 100%; {{ $height }};">
            </iframe>
        @endif
    @endif

</x-filament-panels::page>
