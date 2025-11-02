@extends('fb-report::components.layout')

@section('title', $__data['__reporter']->getReportTitle($__data))

@push('rtl-support')
    <x-fb-report::rtl-support :data="$__data" />
@endpush

@push('styles')
    <x-fb-report::styles :data="$__data" />
@endpush

@push('custom-styles')
    {!! $__data['__reporter']->getStyles($__data) !!}
@endpush

@section('head')
    {!! $__data['__reporter']->getHtmlHead($__data) !!}
@endsection

@section('before-report')
    {!! $__data['__reporter']->getGroupBeforeHtml($__data) !!}
@endsection

@section('report-content')
    <x-fb-report::report :data="$__data" />
@endsection

@section('after-report')
    {!! $__data['__reporter']->getGroupAfterHtml($__data) !!}
@endsection
