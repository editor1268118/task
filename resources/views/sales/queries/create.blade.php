@extends('layouts.admin')

@section('title', 'Create Query')
@section('page-header', 'Create Query')
@section('page-description', 'Register a new client query before task creation.')

@section('content')
<form action="{{ route('sales.queries.store') }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    <div class="card-header bg-white"><strong>Query No: {{ $queryNo }}</strong></div>
    <div class="card-body">
        @include('sales.queries.partials.form')
    </div>
</form>
@endsection
