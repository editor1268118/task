@extends('layouts.admin')

@section('title', 'Edit Query')
@section('page-header', 'Edit Query '.$query->query_no)
@section('page-description', 'Update query details, stage, assignment, and follow-up planning.')

@section('content')
<form action="{{ route('sales.queries.update', $query) }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
        @include('sales.queries.partials.form', ['query' => $query])
    </div>
</form>
@endsection
