<table class="report-table">
    <tr>
        @foreach ($titles as $column)
            <th width="{{ $column['width'] }}%">
                {!! $column['text'] !!}
            </th>
        @endforeach
    </tr>

    @foreach ($rows as $row)
        <tr>
            @foreach ($row as $column)
                <td width="{{ $column['width'] }}%" style="{{ $column['style'] }}">
                    {!! $column['text'] !!}
                </td>
            @endforeach
        </tr>
    @endforeach
</table>
