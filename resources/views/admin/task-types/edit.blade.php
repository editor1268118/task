@extends('layouts.admin')

@section('title', 'Edit Task Type')
@section('page-header', 'Edit Task Type')
@section('page-description', 'Update task type settings.')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.task-types.index') }}">Task Types</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.task-types.update', $taskType) }}" method="POST" data-loading>
            @csrf
            @method('PUT')
            @include('admin.task-types.partials.form', ['taskType' => $taskType, 'requiresOperationalForm' => $requiresOperationalForm])
        </form>
    </div>
</div>
@endsection
