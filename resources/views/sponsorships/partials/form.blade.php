@php
    $isEdit = isset($sponsorship);
    $record = $sponsorship ?? null;
    $action = $isEdit ? route('sponsorships.update', $record->id) : route('sponsorships.store');
    $selectedParticipant = old('participant_id', $record?->participant_id ?? request('participant_id'));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="workspace-subpanel p-5">
        <h2 class="text-lg font-bold mb-5">Sponsorship Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="workspace-field-label">Participant / Project *</label>
                <select name="participant_id" required class="workspace-select px-4 py-3">
                    <option value="">Select Participant</option>
                    @foreach($participants as $participant)
                        <option value="{{ $participant->id }}" @selected((string) $selectedParticipant === (string) $participant->id)>
                            {{ $participant->project_name ?? $participant->account_name }} ({{ $participant->local_participant_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="workspace-field-label">Sponsor Name</label>
                <input type="text" name="sponsor_name" value="{{ old('sponsor_name', $record?->sponsor_name ?? $record?->sponsored_by) }}" class="workspace-input px-4 py-3">
            </div>

            <div>
                <label class="workspace-field-label">Sponsored By</label>
                <input type="text" name="sponsored_by" value="{{ old('sponsored_by', $record?->sponsored_by ?? $record?->sponsor_name) }}" class="workspace-input px-4 py-3">
            </div>

            <div>
                <label class="workspace-field-label">Sponsorship Type</label>
                <input type="text" name="sponsorship_type" value="{{ old('sponsorship_type', $record?->sponsorship_type) }}" class="workspace-input px-4 py-3">
            </div>

            <div>
                <label class="workspace-field-label">Sponsorship Status</label>
                <select name="sponsorship_status" class="workspace-select px-4 py-3">
                    <option value="">Select Status</option>
                    @foreach(['Active', 'Inactive', 'Pending'] as $status)
                        <option value="{{ $status }}" @selected(old('sponsorship_status', $record?->sponsorship_status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="workspace-field-label">Sponsorship Start Date</label>
                <input type="date" name="sponsorship_start_date" value="{{ old('sponsorship_start_date', $record?->sponsorship_start_date?->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
            </div>

            <div class="md:col-span-2">
                <label class="workspace-field-label">Sponsor Physical Address</label>
                <textarea name="sponsor_physical_address" rows="3" class="workspace-textarea px-4 py-3">{{ old('sponsor_physical_address', $record?->sponsor_physical_address) }}</textarea>
            </div>

            <div>
                <label class="workspace-field-label">Sponsor Contact</label>
                <input type="text" name="sponsor_contact" value="{{ old('sponsor_contact', $record?->sponsor_contact) }}" class="workspace-input px-4 py-3">
            </div>

            <div>
                <label class="workspace-field-label">Sponsorship Category</label>
                <input type="text" name="sponsorship_category" value="{{ old('sponsorship_category', $record?->sponsorship_category) }}" placeholder="e.g. Full Support, Partial Support" class="workspace-input px-4 py-3">
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 pt-2">
        <button type="submit" class="btn-primary">{{ $isEdit ? 'Update Sponsorship' : 'Save Sponsorship' }}</button>
        <a href="{{ route('sponsorships.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
