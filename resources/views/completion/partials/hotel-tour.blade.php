{{-- Hotel & Tour Package Form Step --}}
@php
    $form = $existingData;
    $bookingTypes = \App\Models\HotelTourForm::BOOKING_TYPES;
    $serviceTypes = \App\Models\HotelTourForm::SERVICE_TYPES;
    $tripTypes    = \App\Models\HotelTourForm::TRIP_TYPES;
@endphp

<form action="{{ route('tasks.completion.storeStep', ['task' => $task, 'step' => $currentStep]) }}" method="POST" id="hotelTourForm">
    @csrf

    {{-- Booking Info --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-calendar-alt me-1"></i>Booking Information</div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="booking_date" class="form-label">Booking Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('booking_date') is-invalid @enderror"
                       id="booking_date" name="booking_date"
                       value="{{ old('booking_date', isset($form->booking_date) ? $form->booking_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="ht_state" class="form-label">State</label>
                <input type="text" class="form-control @error('state') is-invalid @enderror"
                       id="ht_state" name="state"
                       value="{{ old('state', $form->state ?? '') }}" placeholder="State">
                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="ht_city" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('city') is-invalid @enderror"
                       id="ht_city" name="city"
                       value="{{ old('city', $form->city ?? '') }}" placeholder="City" required>
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Client & Billing --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-user-tie me-1"></i>Client & Billing</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="ht_client_type" class="form-label">Client Type</label>
                <input type="text" class="form-control @error('client_type') is-invalid @enderror"
                       id="ht_client_type" name="client_type"
                       value="{{ old('client_type', $form->client_type ?? '') }}" placeholder="Client type">
                @error('client_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="ht_billed_to" class="form-label">Billed To</label>
                <input type="text" class="form-control @error('billed_to') is-invalid @enderror"
                       id="ht_billed_to" name="billed_to"
                       value="{{ old('billed_to', $form->billed_to ?? '') }}" placeholder="Billed to">
                @error('billed_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="confirmation_no" class="form-label">Confirmation No</label>
                <input type="text" class="form-control @error('confirmation_no') is-invalid @enderror"
                       id="confirmation_no" name="confirmation_no"
                       value="{{ old('confirmation_no', $form->confirmation_no ?? '') }}" placeholder="Booking confirmation number">
                @error('confirmation_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Service Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-concierge-bell me-1"></i>Service Details</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="booking_type" class="form-label">Booking Type <span class="text-danger">*</span></label>
                <select class="form-select @error('booking_type') is-invalid @enderror"
                        id="booking_type" name="booking_type" required>
                    <option value="">Select Booking Type</option>
                    @foreach($bookingTypes as $bt)
                        <option value="{{ $bt }}" {{ old('booking_type', $form->booking_type ?? '') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                    @endforeach
                </select>
                @error('booking_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="service_type" class="form-label">Service Type <span class="text-danger">*</span></label>
                <select class="form-select @error('service_type') is-invalid @enderror"
                        id="service_type" name="service_type" required>
                    <option value="">Select Service Type</option>
                    @foreach($serviceTypes as $st)
                        <option value="{{ $st }}" {{ old('service_type', $form->service_type ?? '') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
                @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="trip_type" class="form-label">Trip Type <span class="text-danger">*</span></label>
                <select class="form-select @error('trip_type') is-invalid @enderror"
                        id="trip_type" name="trip_type" required>
                    <option value="">Select Trip Type</option>
                    @foreach($tripTypes as $tt)
                        <option value="{{ $tt }}" {{ old('trip_type', $form->trip_type ?? '') == $tt ? 'selected' : '' }}>{{ $tt }}</option>
                    @endforeach
                </select>
                @error('trip_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- PAX & Room Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-hotel me-1"></i>Guest & Room Details</div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="no_of_pax" class="form-label">No. of Pax <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control @error('no_of_pax') is-invalid @enderror"
                       id="no_of_pax" name="no_of_pax"
                       value="{{ old('no_of_pax', $form->no_of_pax ?? '') }}" required>
                @error('no_of_pax')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5 mb-3">
                <label for="pax_name" class="form-label">Pax Name</label>
                <input type="text" class="form-control @error('pax_name') is-invalid @enderror"
                       id="pax_name" name="pax_name"
                       value="{{ old('pax_name', $form->pax_name ?? '') }}" placeholder="Guest names">
                @error('pax_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 mb-3">
                <label for="no_of_rooms" class="form-label">Rooms</label>
                <input type="number" min="1" class="form-control @error('no_of_rooms') is-invalid @enderror"
                       id="no_of_rooms" name="no_of_rooms"
                       value="{{ old('no_of_rooms', $form->no_of_rooms ?? '') }}">
                @error('no_of_rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 mb-3">
                <label for="hotel_room_type" class="form-label">Room Type</label>
                <input type="text" class="form-control @error('hotel_room_type') is-invalid @enderror"
                       id="hotel_room_type" name="hotel_room_type"
                       value="{{ old('hotel_room_type', $form->hotel_room_type ?? '') }}" placeholder="e.g. Deluxe">
                @error('hotel_room_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="check_in_date" class="form-label">Check-in Date</label>
                <input type="date" class="form-control @error('check_in_date') is-invalid @enderror"
                       id="check_in_date" name="check_in_date"
                       value="{{ old('check_in_date', isset($form->check_in_date) ? $form->check_in_date->format('Y-m-d') : '') }}">
                @error('check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="check_out_date" class="form-label">Check-out Date</label>
                <input type="date" class="form-control @error('check_out_date') is-invalid @enderror"
                       id="check_out_date" name="check_out_date"
                       value="{{ old('check_out_date', isset($form->check_out_date) ? $form->check_out_date->format('Y-m-d') : '') }}">
                @error('check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Financial Details --}}
    <div class="form-section">
        <div class="form-section-title"><i class="fas fa-calculator me-1"></i>Financial Details</div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="sale_amount" class="form-label">Sale Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('sale_amount') is-invalid @enderror"
                           id="sale_amount" name="sale_amount"
                           value="{{ old('sale_amount', $form->sale_amount ?? '') }}" placeholder="0.00" required>
                    @error('sale_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="purchased_amount" class="form-label">Purchased Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('purchased_amount') is-invalid @enderror"
                           id="purchased_amount" name="purchased_amount"
                           value="{{ old('purchased_amount', $form->purchased_amount ?? '') }}" placeholder="0.00" required>
                    @error('purchased_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="sale_gst" class="form-label">Sale GST</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('sale_gst') is-invalid @enderror"
                           id="sale_gst" name="sale_gst"
                           value="{{ old('sale_gst', $form->sale_gst ?? '') }}" placeholder="0.00">
                    @error('sale_gst')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="gst_expected" class="form-label">GST Expected</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('gst_expected') is-invalid @enderror"
                           id="gst_expected" name="gst_expected"
                           value="{{ old('gst_expected', $form->gst_expected ?? '') }}" placeholder="0.00">
                    @error('gst_expected')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="tcs_calculation" class="form-label">TCS Calculation</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('tcs_calculation') is-invalid @enderror"
                           id="tcs_calculation" name="tcs_calculation"
                           value="{{ old('tcs_calculation', $form->tcs_calculation ?? '') }}" placeholder="0.00">
                    @error('tcs_calculation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="ht_vendor_name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('vendor_name') is-invalid @enderror"
                       id="ht_vendor_name" name="vendor_name"
                       value="{{ old('vendor_name', $form->vendor_name ?? '') }}" required>
                @error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="vendor_tds" class="form-label">Vendor TDS</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('vendor_tds') is-invalid @enderror"
                           id="vendor_tds" name="vendor_tds"
                           value="{{ old('vendor_tds', $form->vendor_tds ?? '') }}" placeholder="0.00">
                    @error('vendor_tds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="discount" class="form-label">Discount</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control @error('discount') is-invalid @enderror"
                           id="discount" name="discount"
                           value="{{ old('discount', $form->discount ?? '') }}" placeholder="0.00">
                    @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
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
