{{-- Payment Purchase Form Step --}}
@php
    $form = $existingData;
    $vendors = \App\Models\PaymentPurchaseForm::VENDOR_OPTIONS;
    $paymentModes = \App\Models\PaymentPurchaseForm::PAYMENT_MODES;
@endphp

<form action="{{ route('tasks.completion.storeStep', ['task' => $task, 'step' => $currentStep]) }}" method="POST" id="paymentPurchaseForm">
    @csrf

    {{-- Vendor Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-store me-1"></i>Vendor Details</div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="vendor_name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                <select class="form-select @error('vendor_name') is-invalid @enderror other-toggle"
                        id="vendor_name" name="vendor_name" data-other-target="customVendorField" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor }}" {{ old('vendor_name', $form->vendor_name ?? '') == $vendor ? 'selected' : '' }}>{{ $vendor }}</option>
                    @endforeach
                </select>
                @error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="vendor_account_no" class="form-label">Vendor Account No</label>
                <input type="text" class="form-control @error('vendor_account_no') is-invalid @enderror"
                       id="vendor_account_no" name="vendor_account_no"
                       value="{{ old('vendor_account_no', $form->vendor_account_no ?? '') }}"
                       placeholder="Account number">
                @error('vendor_account_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 other-custom-field {{ old('vendor_name', $form->vendor_name ?? '') == 'Other' ? 'show' : '' }}" id="customVendorField">
                <div class="mb-3">
                    <label for="custom_vendor_name" class="form-label">Custom Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('custom_vendor_name') is-invalid @enderror"
                           id="custom_vendor_name" name="custom_vendor_name"
                           value="{{ old('custom_vendor_name', $form->custom_vendor_name ?? '') }}"
                           placeholder="Enter vendor name">
                    @error('custom_vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-money-bill-wave me-1"></i>Payment Details</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="payable_amount" class="form-label">Payable Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('payable_amount') is-invalid @enderror"
                           id="payable_amount" name="payable_amount"
                           value="{{ old('payable_amount', $form->payable_amount ?? '') }}"
                           placeholder="0.00" required>
                    @error('payable_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                <select class="form-select @error('payment_mode') is-invalid @enderror other-toggle"
                        id="payment_mode" name="payment_mode" data-other-target="customPaymentModeField" required>
                    <option value="">Select Payment Mode</option>
                    @foreach($paymentModes as $mode)
                        <option value="{{ $mode }}" {{ old('payment_mode', $form->payment_mode ?? '') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
                @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                       id="payment_date" name="payment_date"
                       value="{{ old('payment_date', isset($form->payment_date) ? $form->payment_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 other-custom-field {{ old('payment_mode', $form->payment_mode ?? '') == 'Other' ? 'show' : '' }}" id="customPaymentModeField">
                <div class="mb-3">
                    <label for="custom_payment_mode" class="form-label">Custom Payment Mode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('custom_payment_mode') is-invalid @enderror"
                           id="custom_payment_mode" name="custom_payment_mode"
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
                <label for="payment_comments" class="form-label">Payment Comments</label>
                <textarea class="form-control @error('payment_comments') is-invalid @enderror"
                          id="payment_comments" name="payment_comments" rows="3"
                          placeholder="Additional notes about this payment...">{{ old('payment_comments', $form->payment_comments ?? '') }}</textarea>
                @error('payment_comments')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
