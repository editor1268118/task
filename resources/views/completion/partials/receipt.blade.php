{{-- Receipt Form Step --}}
@php
    $form = $existingData;
    $clientTypes = \App\Models\ReceiptForm::CLIENT_TYPES;
    $paymentModes = \App\Models\ReceiptForm::PAYMENT_MODES;
@endphp

<form action="{{ route('tasks.completion.storeStep', ['task' => $task, 'step' => $currentStep]) }}" method="POST" id="receiptForm">
    @csrf

    {{-- Client Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-user-tie me-1"></i>Client Details</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="client_type" class="form-label">Client Type <span class="text-danger">*</span></label>
                <select class="form-select @error('client_type') is-invalid @enderror"
                        id="client_type" name="client_type" required>
                    <option value="">Select Client Type</option>
                    @foreach($clientTypes as $type)
                        <option value="{{ $type }}" {{ old('client_type', $form->client_type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('client_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="client_company_name" class="form-label">Client / Company Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('client_company_name') is-invalid @enderror"
                       id="client_company_name" name="client_company_name"
                       value="{{ old('client_company_name', $form->client_company_name ?? '') }}"
                       placeholder="Client or company name" required>
                @error('client_company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="contact_no" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('contact_no') is-invalid @enderror"
                       id="contact_no" name="contact_no"
                       value="{{ old('contact_no', $form->contact_no ?? '') }}"
                       placeholder="Phone number" required>
                @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Receipt Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-receipt me-1"></i>Receipt Details</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="receipt_date" class="form-label">Receipt Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('receipt_date') is-invalid @enderror"
                       id="receipt_date" name="receipt_date"
                       value="{{ old('receipt_date', isset($form->receipt_date) ? $form->receipt_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                @error('receipt_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="r_payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                <select class="form-select @error('payment_mode') is-invalid @enderror other-toggle"
                        id="r_payment_mode" name="payment_mode" data-other-target="receiptCustomPaymentMode" required>
                    <option value="">Select Payment Mode</option>
                    @foreach($paymentModes as $mode)
                        <option value="{{ $mode }}" {{ old('payment_mode', $form->payment_mode ?? '') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
                @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="amount_received" class="form-label">Amount Received <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('amount_received') is-invalid @enderror"
                           id="amount_received" name="amount_received"
                           value="{{ old('amount_received', $form->amount_received ?? '') }}"
                           placeholder="0.00" required>
                    @error('amount_received')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4 other-custom-field {{ old('payment_mode', $form->payment_mode ?? '') == 'Other' ? 'show' : '' }}" id="receiptCustomPaymentMode">
                <div class="mb-3">
                    <label for="r_custom_payment_mode" class="form-label">Custom Payment Mode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('custom_payment_mode') is-invalid @enderror"
                           id="r_custom_payment_mode" name="custom_payment_mode"
                           value="{{ old('custom_payment_mode', $form->custom_payment_mode ?? '') }}"
                           placeholder="Enter payment mode">
                    @error('custom_payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Comments --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-comment-alt me-1"></i>Comments</div>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="r_comments" class="form-label">Comments</label>
                <textarea class="form-control @error('comments') is-invalid @enderror"
                          id="r_comments" name="comments" rows="3"
                          placeholder="Additional notes about this receipt...">{{ old('comments', $form->comments ?? '') }}</textarea>
                @error('comments')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Wizard Footer --}}
    <div class="wizard-footer d-flex justify-content-between align-items-center">
        <div>
            @if($currentStep > 1)
                <a href="{{ route('tasks.completion.wizard', ['task' => $task, 'step' => $currentStep - 1]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Previous
                </a>
            @else
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button type="submit" formaction="{{ route('tasks.completion.saveDraft', ['task' => $task, 'step' => $currentStep]) }}" class="btn btn-outline-warning">
                <i class="fas fa-save me-1"></i> Save Draft
            </button>
            <button type="submit" class="btn btn-primary">
                Submit & Next <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</form>
