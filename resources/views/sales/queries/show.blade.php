@extends('layouts.admin')

@section('title', $query->query_no)
@section('page-header', $query->query_no.' - '.($query->query_title ?: $query->client_name))
@section('page-description', $query->effective_service_type.' query from '.$query->source)

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('sales.queries.edit', $query) }}" class="btn btn-sm btn-outline-primary">Edit</a>
    @if($query->canConvert())
        <form action="{{ route('sales.queries.convert', $query) }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-success" onclick="return confirm('Convert this confirmed query to a task?')">Convert To Task</button>
        </form>
    @elseif($query->convertedTask)
        <a href="{{ route('tasks.show', $query->convertedTask) }}" class="btn btn-sm btn-success">Open Task {{ $query->convertedTask->task_no }}</a>
    @endif
</div>
@endsection

@push('styles')
<style>
    .query-tabs .nav-link {
        color: #475569;
        border: 1px solid transparent;
        font-weight: 700;
    }
    .query-tabs .nav-link:hover {
        color: #0f172a;
        background: #e0e7ff;
        border-color: #c7d2fe;
    }
    .query-tabs .nav-link.active {
        color: #fff;
        background: #4f46e5;
        border-color: #4f46e5;
    }
    .query-timeline-item {
        border-left: 3px solid #c7d2fe;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    .discussion-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 800;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs query-tabs gap-1" role="tablist">
                    @foreach(['overview' => 'Overview', 'discussions' => 'Discussions', 'followups' => 'Follow-Ups', 'timeline' => 'Activity Timeline'] as $id => $label)
                        <li class="nav-item">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#{{ $id }}" type="button">{{ $label }}</button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row g-3">
                            @foreach([
                                'Query Date' => $query->query_date?->format('d M Y'),
                                'Query Details' => $query->query_title ?: '-',
                                'Service' => $query->effective_service_type,
                                'Company' => $query->company_name ?? '-',
                                'Mobile' => $query->mobile,
                                'Email' => $query->email ?? '-',
                                'Destination' => $query->destination ?? '-',
                                'Travel Date' => $query->travel_date?->format('d M Y') ?? '-',
                                'Pax' => $query->number_of_pax ?? '-',
                                'Expected Sale' => $query->expected_sale_amount ? 'INR '.number_format((float) $query->expected_sale_amount, 2) : '-',
                                'Age' => $query->age_days.' days',
                            ] as $label => $value)
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded h-100">
                                        <small class="text-muted">{{ $label }}</small>
                                        <div class="fw-semibold">{{ $value }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade" id="discussions">
                        <form action="{{ route('sales.queries.discussions.store', $query) }}" method="POST" class="border rounded p-3 mb-4">
                            @csrf
                            <h6 class="fw-bold mb-3">Add Discussion</h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <select name="discussion_type" class="form-select" required>
                                        @foreach($discussionTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="mentioned_user_id" class="form-select">
                                        <option value="">Mention user</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <textarea name="message" class="form-control" rows="2" placeholder="Call, WhatsApp, negotiation, client requirement, or internal note..." required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-primary">Save Discussion</button>
                                </div>
                            </div>
                        </form>

                        @forelse($query->discussions as $discussion)
                            <div class="d-flex gap-3 border-bottom py-3">
                                <div class="discussion-avatar">{{ substr($discussion->creator?->name ?? 'S', 0, 1) }}</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <strong>{{ $discussion->creator?->name ?? 'System' }}</strong>
                                            <span class="badge bg-light text-dark border ms-1">{{ $discussion->discussion_type }}</span>
                                            @if($discussion->mentionedUser)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">@ {{ $discussion->mentionedUser->name }}</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $discussion->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}</small>
                                    </div>
                                    <div class="mt-2" style="white-space: pre-wrap;">{{ $discussion->message }}</div>

                                    @if($discussion->canBeManagedBy(auth()->user()))
                                        <div class="mt-2 d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editDiscussion{{ $discussion->id }}">Edit</button>
                                            <form action="{{ route('sales.queries.discussions.destroy', [$query, $discussion]) }}" method="POST" onsubmit="return confirm('Delete this discussion?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                        <div class="collapse mt-3" id="editDiscussion{{ $discussion->id }}">
                                            <form action="{{ route('sales.queries.discussions.update', [$query, $discussion]) }}" method="POST" class="border rounded p-3 bg-light">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <select name="discussion_type" class="form-select form-select-sm" required>
                                                            @foreach($discussionTypes as $type)
                                                                <option value="{{ $type }}" {{ $discussion->discussion_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select name="mentioned_user_id" class="form-select form-select-sm">
                                                            <option value="">Mention user</option>
                                                            @foreach($employees as $employee)
                                                                <option value="{{ $employee->id }}" {{ $discussion->mentioned_user_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <textarea name="message" class="form-control form-control-sm" rows="2" required>{{ $discussion->message }}</textarea>
                                                    </div>
                                                    <div class="col-12 text-end">
                                                        <button class="btn btn-sm btn-primary">Update Discussion</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">No discussions yet.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="followups">
                        <form action="{{ route('sales.queries.followups.store', $query) }}" method="POST" class="border rounded p-3 mb-3">
                            @csrf
                            <h6 class="fw-bold mb-3">Quick Follow-Up</h6>
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <select name="discussion_type" class="form-select" required>
                                        @foreach($discussionTypes as $type)
                                            <option value="{{ $type }}" {{ $type === 'Follow-Up' ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><input type="date" name="followup_date" value="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
                                <div class="col-md-2"><input type="date" name="next_followup_date" class="form-control"></div>
                                <div class="col-md-4"><input name="remarks" class="form-control" placeholder="Follow-up remarks" required></div>
                                <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
                            </div>
                        </form>
                        @forelse($query->followups as $followup)
                            <div class="border-bottom py-2">
                                <strong>{{ $followup->followup_date->format('d M Y') }}</strong> by {{ $followup->creator?->name ?? '-' }}
                                <div>{{ $followup->remarks }}</div>
                                <small class="text-muted">Next: {{ $followup->next_followup_date?->format('d M Y') ?? '-' }}</small>
                            </div>
                        @empty
                            <p class="text-muted">No follow-ups added.</p>
                        @endforelse
                    </div>

                    <div class="tab-pane fade" id="timeline">
                        @forelse($query->activities as $activity)
                            <div class="query-timeline-item">
                                <div class="small text-muted">{{ $activity->activity_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }} - {{ $activity->user?->name ?? 'System' }}</div>
                                <div><strong>{{ $activity->action }}</strong></div>
                                @if($activity->remarks)<div>{{ $activity->remarks }}</div>@endif
                                @if($activity->properties)
                                    <div class="small text-muted mt-1">
                                        @foreach($activity->properties as $key => $value)
                                            @continue(is_array($value))
                                            <span class="me-2">{{ Str::headline($key) }}: {{ $value }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">No activity yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Status</span><strong>{{ $query->status }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Stage</span><strong>{{ $query->stage }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Expected Sale</span><strong>{{ $query->expected_sale_amount ? 'INR '.number_format((float) $query->expected_sale_amount, 2) : '-' }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Assigned To</span><strong>{{ $query->assignedTo?->name ?? 'Unassigned' }}</strong></div>
                <div class="d-flex justify-content-between"><span>Next Follow-Up</span><strong>{{ $query->next_followup_date?->format('d M Y') ?? '-' }}</strong></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>Quick Status Update</strong></div>
            <div class="card-body">
                <form action="{{ route('sales.queries.quick-status', $query) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label class="form-label small">Stage</label>
                    <select name="stage" class="form-select mb-2" required>
                        @foreach($stages as $stage)
                            <option value="{{ $stage }}" {{ $query->stage === $stage ? 'selected' : '' }}>{{ $stage }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select mb-2" required>
                        @foreach(['Open', 'Confirmed', 'Lost', 'Cancelled'] as $status)
                            <option value="{{ $status }}" {{ $query->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small">Lost Reason</label>
                    <select name="lost_reason" class="form-select mb-2">
                        <option value="">Select if lost</option>
                        @foreach($lostReasons as $reason)
                            <option value="{{ $reason }}" {{ $query->lost_reason === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small">Next Follow-Up</label>
                    <input type="date" name="next_followup_date" value="{{ $query->next_followup_date?->format('Y-m-d') }}" class="form-control mb-2">
                    <label class="form-label small">Remarks</label>
                    <textarea name="latest_remark" class="form-control mb-2" rows="2">{{ $query->latest_remark }}</textarea>
                    <button class="btn btn-primary w-100">Update Query</button>
                </form>
            </div>
        </div>

        @hasanyrole('super-admin|manager')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Reassign Query</strong></div>
            <div class="card-body">
                <form action="{{ route('sales.queries.reassign', $query) }}" method="POST">
                    @csrf
                    <select name="assigned_to" class="form-select mb-2" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $query->assigned_to == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="reason" class="form-control mb-2" placeholder="Reason" required></textarea>
                    <button class="btn btn-outline-primary w-100">Reassign</button>
                </form>
            </div>
        </div>
        @endhasanyrole
    </div>
</div>
@endsection
