@php
    $titles = $data['__reporter']->getColumnsTitle();

    $rows = $data['__reporter']->getTableRows();
@endphp

{!! $data['__reporter']->getBeforeHtml($data) !!}

{!! $data['__reporter']->getMainHtml($data, $titles, $rows) !!}

{!! $data['__reporter']->getAfterHtml($data) !!}
