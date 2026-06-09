@extends('layouts.admin')

@section('title', 'Add Customer')
@section('page-header', 'Add Customer')

@section('content')
<form action="{{ route('crm.customers.store') }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @include('crm.customers.partials.form')
</form>
@endsection
