<div class="report-header">
	<div class="report-header-title">
		{!! $data['__reporter']->getReportTitle($data) ?? '' !!}
	</div>
	<div class="report-header-description">
		{!! $data['__reporter']->getReportDescription($data) ?? '' !!}
	</div>
</div>
