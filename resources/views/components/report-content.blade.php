@if (!!$data['__reporter']->getGroupItems())

    @foreach ($data['__reporter']->getGroupItems() as $index => $group)
        @php
            $data['__reporter']->setCurrentGroup($group);
            $data['__reporter']->setCurrentGroupIndex($index);
        @endphp

        @if (!!$data['__reporter']->getSubGroupItems())
            @foreach ($data['__reporter']->getSubGroupItems() as $subIndex => $subGroup)
                @php
                    $data['__reporter']->setCurrentSubGroup($subGroup);
                    $data['__reporter']->setCurrentSubGroupIndex($subIndex);
                @endphp
                {{-- {!! $data['__reporter']->getBeforeHtml($data) !!} --}}

                <x-fb-report::table :data="$data" />

                {{-- {!! $data['__reporter']->getAfterHtml($data) !!} --}}

                @if (!$loop->last)
                    <pagebreak />
                @endif
            @endforeach
            @if (!$loop->last)
                <pagebreak />
            @endif
        @else
            {{-- {!! $data['__reporter']->getBeforeHtml($data) !!} --}}

            <x-fb-report::table :data="$data" />

            {{-- {!! $data['__reporter']->getAfterHtml($data) !!} --}}

            @if (!$loop->last)
                <pagebreak />
            @endif
        @endif
    @endforeach
@else
    {{-- it is single table page --}}
    {{-- {!! $data['__reporter']->getBeforeHtml($data) !!} --}}

    <x-fb-report::table :data="$data" />

    {{-- {!! $data['__reporter']->getAfterHtml($data) !!} --}}
@endif
