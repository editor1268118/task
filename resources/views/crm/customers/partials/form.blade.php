<div class="card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Customer Type</label>
            <select name="customer_type" class="form-select" required>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ old('customer_type', $customer->customer_type ?: 'B2C') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ old('status', $customer->status ?: 'Active') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">GST Number</label>
            <input name="gst_number" class="form-control" value="{{ old('gst_number', $customer->gst_number) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Company / Customer Name</label>
            <input name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Contact Person</label>
            <input name="contact_person" class="form-control" value="{{ old('contact_person', $customer->contact_person) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Mobile</label>
            <input name="mobile" class="form-control" value="{{ old('mobile', $customer->mobile) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Alternate Mobile</label>
            <input name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $customer->alternate_mobile) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
        </div>
        <div class="col-md-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input name="city" class="form-control" value="{{ old('city', $customer->city) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input name="state" class="form-control" value="{{ old('state', $customer->state) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Country</label>
            <input name="country" class="form-control" value="{{ old('country', $customer->country) }}">
        </div>
        <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $customer->remarks) }}</textarea>
        </div>
    </div>
</div>
<div class="card-footer bg-white text-end">
    <a href="{{ route('crm.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button class="btn btn-primary">Save Customer</button>
</div>
