@extends('layouts.admin')

@section('title', 'Review Center')
@section('page-header', 'Review & Approval Center')
@section('page-description', 'Manage pending operational and finance approvals.')

@section('content')
<div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Pending Reviews -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock me-2"></i> Pending Reviews</h6>
                </div>
                <div class="card-body">
                    @if($pendingReviews->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-gray-800">All Caught Up!</h5>
                            <p class="text-muted">You have no pending reviews in your queue.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Task</th>
                                        <th>Department</th>
                                        <th>Submitted By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingReviews as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tasks.show', $task) }}" class="fw-bold text-decoration-none">
                                                {{ $task->task_no }} - {{ $task->title }}
                                            </a>
                                            <div class="small text-muted">{{ $task->updated_at->diffForHumans() }}</div>
                                        </td>
                                        <td>{{ $task->department->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ substr($task->assignee->name ?? 'U', 0, 1) }}
                                                </div>
                                                {{ $task->assignee->name ?? 'Unassigned' }}
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#actionModal{{ $task->id }}" data-action="approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#actionModal{{ $task->id }}" data-action="reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#actionModal{{ $task->id }}" data-action="request_correction">
                                                <i class="fas fa-undo"></i>
                                            </button>

                                            <!-- Action Modal -->
                                            <div class="modal fade" id="actionModal{{ $task->id }}" tabindex="-1" aria-labelledby="actionModalLabel{{ $task->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('reviews.action', $task) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="actionModalLabel{{ $task->id }}">Review Action</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" id="modalAction{{ $task->id }}" value="">
                                                                <div class="mb-3">
                                                                    <label for="comment" class="form-label">Review Comment (Optional)</label>
                                                                    <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Add any notes regarding this decision..."></textarea>
                                                                </div>
                                                                <div class="alert alert-info py-2 mb-0">
                                                                    <small><i class="fas fa-info-circle me-1"></i> You are submitting a review for <strong>{{ $task->task_no }}</strong>.</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary" id="modalSubmitBtn{{ $task->id }}">Confirm Action</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Modal -->
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Recent Review Logs -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i> My Recent Reviews</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($myLogs as $log)
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="{{ route('tasks.show', $log->task_id) }}" class="fw-bold text-decoration-none">
                                    {{ $log->task->task_no ?? 'Unknown Task' }}
                                </a>
                                @if($log->status === 'approve')
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Approved</span>
                                @elseif($log->status === 'reject')
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-undo"></i> Correction</span>
                                @endif
                            </div>
                            @if($log->comment)
                                <p class="small text-muted mb-1 fst-italic">"{{ Str::limit($log->comment, 60) }}"</p>
                            @endif
                            <small class="text-xs text-gray-500">{{ $log->created_at->timezone(config('app.display_timezone'))->format('d M Y h:i A') }}</small>
                        </div>
                        @empty
                        <p class="text-muted small text-center mb-0">No recent review activity.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');
                const modalId = modal.getAttribute('id').replace('actionModal', '');
                
                const actionInput = modal.querySelector('#modalAction' + modalId);
                const submitBtn = modal.querySelector('#modalSubmitBtn' + modalId);
                
                actionInput.value = action;
                
                if(action === 'approve') {
                    submitBtn.className = 'btn btn-success';
                    submitBtn.innerText = 'Approve';
                } else if(action === 'reject') {
                    submitBtn.className = 'btn btn-danger';
                    submitBtn.innerText = 'Reject';
                } else {
                    submitBtn.className = 'btn btn-warning';
                    submitBtn.innerText = 'Request Correction';
                }
            });
        });
    });
</script>
@endpush
