@extends('layouts.admin')

@section('title', 'Add Task Type')
@section('page-header', 'Add Task Type')
@section('page-description', 'Create a task type available during task creation.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.task-types.index') }}">Task Types</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.task-types.store') }}" method="POST" data-loading>
            @csrf
            @include('admin.task-types.partials.form')
        </form>
    </div>
</div>
@endsection
