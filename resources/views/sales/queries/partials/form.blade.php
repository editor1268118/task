@php($queryModel = $query ?? null)

@if(session('duplicate_warning'))
    <div class="alert alert-warning">
        <strong>Possible Duplicate Query Found.</strong>
        <div class="small mb-2">Matching email or company exists. Mobile numbers can repeat for multiple queries.</div>
        @foreach(session('duplicate_queries', collect()) as $duplicate)
            <div class="small">
                <a href="{{ route('sales.queries.show', $duplicate) }}">{{ $duplicate->query_no }}</a>
                - {{ $duplicate->client_name }} {{ $duplicate->mobile ? '(' . $duplicate->mobile . ')' : '' }}
            </div>
        @endforeach
        <input type="hidden" name="duplicate_confirmed" value="1">
    </div>
@endif

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Query Date <span class="text-danger">*</span></label>
        <input type="date" name="query_date" class="form-control @error('query_date') is-invalid @enderror" value="{{ old('query_date', $queryModel?->query_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        @error('query_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Query Time</label>
        <input type="time" name="query_time" class="form-control @error('query_time') is-invalid @enderror" value="{{ old('query_time', $queryModel?->query_time ? substr($queryModel->query_time, 0, 5) : now()->format('H:i')) }}">
        @error('query_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Query Details <span class="text-danger">*</span></label>
        <input name="query_title" class="form-control @error('query_title') is-invalid @enderror" value="{{ old('query_title', $queryModel?->query_title) }}" placeholder="e.g. Goa family package for 4 pax" required>
        @error('query_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Service Type <span class="text-danger">*</span></label>
        <select name="service_type" id="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
            <option value="">Select</option>
            @foreach($serviceTypes as $type)
                <option value="{{ $type }}" {{ old('service_type', $queryModel?->service_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" class="form-select" required>
            @foreach($priorities as $priority)
                <option value="{{ $priority }}" {{ old('priority', $queryModel?->priority ?? 'Medium') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Client Name <span class="text-danger">*</span></label>
        <input name="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name', $queryModel?->client_name) }}" required>
        @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Company</label>
        <input name="company_name" class="form-control" value="{{ old('company_name', $queryModel?->company_name) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Mobile <span class="text-danger">*</span></label>
        <input name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $queryModel?->mobile) }}" required>
        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $queryModel?->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Destination</label>
        <input name="destination" class="form-control" value="{{ old('destination', $queryModel?->destination) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Travel Date</label>
        <input type="date" name="travel_date" class="form-control" value="{{ old('travel_date', $queryModel?->travel_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Adult <span class="text-danger">*</span></label>
        <input type="number" min="0" name="adult_count" id="adult_count" class="form-control @error('adult_count') is-invalid @enderror" value="{{ old('adult_count', $queryModel?->adult_count ?? $queryModel?->number_of_pax ?? 1) }}" required>
        @error('adult_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Child</label>
        <input type="number" min="0" name="child_count" id="child_count" class="form-control @error('child_count') is-invalid @enderror" value="{{ old('child_count', $queryModel?->child_count ?? 0) }}">
        @error('child_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Total Pax <span class="text-danger">*</span></label>
        <input type="number" min="1" name="number_of_pax" id="number_of_pax" class="form-control @error('number_of_pax') is-invalid @enderror" value="{{ old('number_of_pax', $queryModel?->number_of_pax ?? 1) }}" readonly required>
        @error('number_of_pax')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Source <span class="text-danger">*</span></label>
        <select name="source" class="form-select" required>
            <option value="">Select</option>
            @foreach($sources as $source)
                <option value="{{ $source }}" {{ old('source', $queryModel?->source) === $source ? 'selected' : '' }}>{{ $source }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Assigned To <span class="text-danger">*</span></label>
        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
            <option value="">Select employee</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ old('assigned_to', $queryModel?->assigned_to) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
            @endforeach
        </select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Stage <span class="text-danger">*</span></label>
        <select name="stage" class="form-select @error('stage') is-invalid @enderror" required>
            @foreach($stages as $stage)
                <option value="{{ $stage }}" {{ old('stage', $queryModel?->stage ?? 'New Query') === $stage ? 'selected' : '' }}>{{ $stage }}</option>
            @endforeach
        </select>
        @error('stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['Open', 'Confirmed', 'Lost', 'Cancelled'] as $status)
                <option value="{{ $status }}" {{ old('status', $queryModel?->status ?? 'Open') === $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Next Follow-Up</label>
        <input type="date" name="next_followup_date" class="form-control @error('next_followup_date') is-invalid @enderror" value="{{ old('next_followup_date', $queryModel?->next_followup_date?->format('Y-m-d')) }}">
        <small class="text-muted">Required for Follow Up, Pricing Shared, Negotiation.</small>
        @error('next_followup_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Lost Reason</label>
        <select name="lost_reason" class="form-select @error('lost_reason') is-invalid @enderror">
            <option value="">Select if lost</option>
            @foreach($lostReasons as $reason)
                <option value="{{ $reason }}" {{ old('lost_reason', $queryModel?->lost_reason) === $reason ? 'selected' : '' }}>{{ $reason }}</option>
            @endforeach
        </select>
        @error('lost_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Latest Remark</label>
        <textarea name="latest_remark" rows="3" class="form-control" placeholder="Client requirement, package details, pricing notes, or important query context">{{ old('latest_remark', $queryModel?->latest_remark) }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">Save Query</button>
    <a href="{{ route('sales.queries.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adultInput = document.getElementById('adult_count');
        const childInput = document.getElementById('child_count');
        const paxInput = document.getElementById('number_of_pax');

        if (!adultInput || !childInput || !paxInput) {
            return;
        }

        function syncPax() {
            const adults = Math.max(0, parseInt(adultInput.value || '0', 10));
            const children = Math.max(0, parseInt(childInput.value || '0', 10));
            paxInput.value = adults + children;
        }

        adultInput.addEventListener('input', syncPax);
        childInput.addEventListener('input', syncPax);
        syncPax();
    });
</script>
@endpush
