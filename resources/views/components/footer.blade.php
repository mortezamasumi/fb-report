@use('Mortezamasumi\FbEssentials\Facades\FbPersian')

<table class="report-footer">
    <tr>
        <td width="50%">
            {{ __('fb-report::fb-report.footer_date', [
                'date' => FbPersian::jDateTime(__('fb-essentials::fb-essentials.date_format.time_simple'), now()),
            ]) }}
        </td>

        <td width="50%">
            @lang('fb-report::fb-report.footer_page')
        </td>
    </tr>
</table>
