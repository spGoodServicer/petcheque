@extends('layouts.app')
@section('title', __('contact.view_contact'))
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>{{ __('lang_v1.View Payee ledger') }}</h1>
</section>
<!-- Main content -->
<section class="content no-print">
<!-- app css -->
@if(!empty($for_pdf))
	<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
@endif

@endsection