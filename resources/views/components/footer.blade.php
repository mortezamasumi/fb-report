<table class="report-footer">
    <tr>
        <td width="50%">
            {{ __('fb-report::fb-report.footer_date', [
                'date' => __jdatetime(__f_datetime(), now()),
            ]) }}
        </td>

        <td width="50%">
            @lang('fb-report::fb-report.footer_page')
        </td>
    </tr>
</table>
