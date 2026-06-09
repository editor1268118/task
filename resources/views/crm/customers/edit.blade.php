@extends('layouts.admin')

@section('title', 'Edit Customer')
@section('page-header', 'Edit Customer')

@section('content')
<form action="{{ route('crm.customers.update', $customer) }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @method('PUT')
    @include('crm.customers.partials.form')
</form>
@endsection
