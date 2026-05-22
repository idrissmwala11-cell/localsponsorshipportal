@php
    $isEdit = isset($participant);
    $record = $participant ?? null;
    $latestSponsorship = $record?->latestSponsorship;
    $action = $isEdit ? route('participants.update', $record->id) : route('participants.store');
    $chronicIllnessOptions = ['HIV', 'Asthma', 'Diabetes', 'Epilepsy', 'Sickle Cell', 'Heart Disease', 'Cancer', 'Ulcer', 'Kidney Disease', 'Other'];
    $selectedChronicIllnesses = collect(old('chronic_illnesses', $record?->chronic_illnesses ? array_map('trim', explode(',', $record->chronic_illnesses)) : []))
        ->filter()
        ->values()
        ->all();
    $hasOtherChronicIllness = collect($selectedChronicIllnesses)->contains(function ($value) use ($chronicIllnessOptions) {
        return !in_array($value, array_diff($chronicIllnessOptions, ['Other']), true) && $value !== 'Other';
    }) || in_array('Other', $selectedChronicIllnesses, true);
    $selectedKnownChronicIllnesses = collect($selectedChronicIllnesses)
        ->filter(fn ($value) => in_array($value, $chronicIllnessOptions, true))
        ->values()
        ->all();
    $manualOtherChronicIllness = old('chronic_illness_other', collect($selectedChronicIllnesses)
        ->reject(fn ($value) => in_array($value, array_diff($chronicIllnessOptions, ['Other']), true))
        ->reject(fn ($value) => $value === 'Other')
        ->implode(', '));
    $sponsorEntries = old('sponsor_entries', isset($record)
        ? (($record->relationLoaded('sponsorships') ? $record->sponsorships : collect())->map(function ($sponsorship) {
            return [
                'sponsor_name' => $sponsorship->sponsor_name ?? $sponsorship->sponsored_by,
                'sponsored_by' => $sponsorship->sponsored_by,
                'sponsorship_type' => $sponsorship->sponsorship_type,
                'sponsorship_status' => $sponsorship->sponsorship_status,
                'sponsorship_start_date' => optional($sponsorship->sponsorship_start_date)->format('Y-m-d'),
                'sponsor_physical_address' => $sponsorship->sponsor_physical_address,
                'sponsor_contact' => $sponsorship->sponsor_contact,
                'sponsorship_category' => $sponsorship->sponsorship_category,
            ];
        })->values()->all())
        : []);
    if (empty($sponsorEntries)) {
        $sponsorEntries = [[
            'sponsor_name' => old('sponsor_name', $latestSponsorship?->sponsor_name ?? $latestSponsorship?->sponsored_by ?? $record?->sponsored_by),
            'sponsored_by' => old('sponsored_by', $latestSponsorship?->sponsored_by ?? $record?->sponsored_by),
            'sponsorship_type' => old('sponsorship_type', $latestSponsorship?->sponsorship_type ?? $record?->sponsorship_type),
            'sponsorship_status' => old('sponsorship_record_status', $latestSponsorship?->sponsorship_status ?? $record?->sponsorship_status),
            'sponsorship_start_date' => old('sponsorship_start_date', optional($latestSponsorship?->sponsorship_start_date)->format('Y-m-d') ?? optional($record?->sponsorship_start_date)->format('Y-m-d')),
            'sponsor_physical_address' => old('sponsor_physical_address', $latestSponsorship?->sponsor_physical_address ?? $record?->sponsor_physical_address),
            'sponsor_contact' => old('sponsor_contact', $latestSponsorship?->sponsor_contact ?? $record?->sponsor_contact),
            'sponsorship_category' => old('sponsorship_category', $latestSponsorship?->sponsorship_category ?? $record?->sponsorship_category),
        ]];
    }
@endphp

@once
    <style>
        .participant-profile-form .workspace-subpanel.compact-section {
            padding: 0.85rem 0.9rem;
            border-radius: 1.05rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 250, 252, 0.97));
            box-shadow: 0 18px 34px -30px rgba(15, 23, 42, 0.14);
        }
        .participant-profile-form .section-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        .participant-profile-form .compact-fields {
            gap: 0.8rem;
        }
        .participant-photo-inline {
            display: grid;
            grid-template-columns: 104px minmax(0, 1fr);
            gap: 0.85rem;
            align-items: center;
            padding: 0.75rem;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: linear-gradient(180deg, #f8fbff, #ffffff);
        }
        .participant-photo-frame {
            width: 104px;
            height: 104px;
            border-radius: 0.9rem;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .participant-profile-form .workspace-subpanel h2 {
            margin-bottom: 0;
            font-size: 1rem;
            font-weight: 800;
        }
        .participant-profile-form .workspace-field-label {
            display: inline-block;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            color: #0f172a;
            margin-bottom: 0.18rem;
        }
        .participant-profile-form .workspace-input,
        .participant-profile-form .workspace-select,
        .participant-profile-form .workspace-textarea {
            margin-top: 0.28rem;
            padding-top: 0.72rem;
            padding-bottom: 0.72rem;
            font-size: 0.94rem;
            font-weight: 600;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.38);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
        }
        .participant-profile-form .workspace-input::placeholder,
        .participant-profile-form .workspace-textarea::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        .participant-profile-form .workspace-input:focus,
        .participant-profile-form .workspace-select:focus,
        .participant-profile-form .workspace-textarea:focus {
            border-color: rgba(59, 130, 246, 0.38);
            box-shadow: 0 0 0 4px rgba(219, 234, 254, 0.85);
        }
        .participant-profile-form .workspace-input[readonly] {
            color: #334155;
            font-weight: 700;
            background: #f8fafc;
        }
        .participant-section-heading {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
        }
        .participant-section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 0.75rem;
            color: #2563eb;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid rgba(147, 197, 253, 0.5);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
            flex-shrink: 0;
        }
        .participant-section-copy {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 500;
            margin-top: 0.08rem;
            line-height: 1.35;
        }
        .sponsor-entry-card {
            border: 1px solid rgba(191, 219, 254, 0.9);
            border-radius: 1.15rem;
            padding: 0.95rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(239,246,255,0.6));
        }
        .participant-map-preview {
            border: 1px solid rgba(191, 219, 254, 0.9);
            border-radius: 1.1rem;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
            box-shadow: 0 18px 34px -30px rgba(15, 23, 42, 0.14);
        }
        .participant-map-preview iframe {
            display: block;
            width: 100%;
            height: 260px;
            border: 0;
            background: #e2e8f0;
        }
        .participant-map-preview iframe.hidden,
        .participant-map-preview-empty.hidden {
            display: none;
        }
        .participant-map-preview-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            padding: 1rem;
            text-align: center;
            color: #64748b;
            background:
                radial-gradient(circle at top, rgba(219, 234, 254, 0.9), rgba(255, 255, 255, 0.98));
        }
        .participant-map-preview-empty p {
            margin: 0;
        }
        .participant-map-preview-empty .map-preview-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }
        .participant-map-preview-empty .map-preview-copy {
            margin-top: 0.35rem;
            max-width: 30rem;
            font-size: 0.7rem;
            line-height: 1.45;
            color: #64748b;
        }
        @media (min-width: 1100px) {
            .participant-profile-form .section-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .participant-photo-inline {
                grid-template-columns: 1fr;
            }
            .participant-photo-frame {
                width: 100%;
                max-width: 130px;
                height: 130px;
                margin: 0 auto;
            }
        }
    </style>
@endonce

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6 participant-profile-form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-5">
            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-person-vcard-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Basic Information</h2>
                        <p class="participant-section-copy">Core participant identity, profile photo, and status summary.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 compact-fields">
                    <div class="md:col-span-2 xl:col-span-3">
                        <div class="participant-photo-inline">
                            <div class="participant-photo-frame">
                                @if($record?->photo_url)
                                    <img id="photoPreview" src="{{ $record->photo_url }}" alt="Participant Photo" class="w-full h-full object-cover">
                                    <div id="photoPlaceholder" class="hidden text-center px-4">
                                        <p class="text-sm font-medium text-slate-500">No photo selected</p>
                                    </div>
                                @else
                                    <img id="photoPreview" src="" alt="Preview" class="hidden w-full h-full object-cover">
                                    <div id="photoPlaceholder" class="text-center px-4">
                                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-lg text-slate-400">
                                            <i class="bi bi-camera"></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-500">No photo selected</p>
                                        <p class="mt-1 text-xs text-slate-400">Upload clear participant photo.</p>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="workspace-field-label">Participant Photo</label>
                                <input type="file" name="photo" id="photo" accept="image/*" class="workspace-input block w-full px-4 py-3">
                                <div class="mt-3 rounded-2xl bg-slate-50 border border-slate-200 p-3 text-sm text-slate-600">
                                    <p><span class="font-semibold">Last photo update:</span> {{ $record?->photo_updated_at?->format('Y-m-d') ?? 'N/A' }}</p>
                                    <p class="mt-1.5"><span class="font-semibold">Next update due:</span> {{ $record?->next_photo_update_due_at?->format('Y-m-d') ?? 'After upload' }}</p>
                                    <p class="mt-1.5 text-xs text-slate-500">Photo updates are now due after every 18 months.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="workspace-field-label">Local Participant Number</label>
                        <input type="text" name="local_participant_number" value="{{ old('local_participant_number', $record?->local_participant_number) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Local Participant ID</label>
                        <input type="text" value="{{ $record?->local_participant_id ?? 'Auto-generated by system' }}" class="workspace-input px-4 py-3 bg-slate-50" readonly>
                    </div>
                    <div>
                        <label class="workspace-field-label">Project Name *</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $record?->account_name) }}" required class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Full Name</label>
                        <input type="text" name="preferred_name" value="{{ old('preferred_name', $record?->preferred_name) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Gender *</label>
                        <select name="gender" required class="workspace-select px-4 py-3">
                            <option value="">Select Gender</option>
                            <option value="Male" @selected(old('gender', $record?->gender) === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', $record?->gender) === 'Female')>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="workspace-field-label">Birthdate</label>
                        <input type="date" name="birthdate" value="{{ old('birthdate', $record?->birthdate?->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Age</label>
                        <input type="text" value="{{ old('age', $record?->age ?? '') }}" class="workspace-input px-4 py-3 bg-slate-50" readonly>
                    </div>
                    <div>
                        <label class="workspace-field-label">Current Status Summary</label>
                        <input type="text" value="{{ old('participant_status', $record?->participant_status ?? 'Active') }}" class="workspace-input px-4 py-3 bg-slate-50" readonly>
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-geo-alt-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Address Information</h2>
                        <p class="participant-section-copy">Residential location, household details, and GPS reference.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div class="md:col-span-2">
                        <label class="workspace-field-label">Physical Address</label>
                        <textarea id="physical_address" name="physical_address" rows="3" class="workspace-textarea px-4 py-3">{{ old('physical_address', $record?->physical_address ?? $record?->address) }}</textarea>
                    </div>
                    <div>
                        <label class="workspace-field-label">House Number</label>
                        <input id="house_number" type="text" name="house_number" value="{{ old('house_number', $record?->house_number) }}" placeholder="Enter house number manually" class="workspace-input px-4 py-3">
                        <p class="mt-2 text-xs text-slate-500">This field is entered manually by the user and is not generated by the map.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="workspace-field-label">GPS Location</label>
                        <input id="gps_location" type="text" name="gps_location" value="{{ old('gps_location', $record?->gps_location) }}" class="workspace-input px-4 py-3 bg-slate-50" readonly>
                        <p class="mt-2 text-xs text-slate-500">This location is now auto-detected from the address and shown on the map below.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="workspace-field-label">Map View</label>
                        <div class="participant-map-preview">
                            <iframe
                                id="participantMapFrame"
                                src=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="hidden"
                                title="Participant address map preview"></iframe>
                            <div id="participantMapPlaceholder" class="participant-map-preview-empty">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border border-sky-100 bg-white text-2xl text-sky-500 shadow-sm">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <p class="map-preview-title">Map preview will appear automatically</p>
                                <p class="map-preview-copy">Fill in the physical address and the house number manually, and the system will display the map view here.</p>
                            </div>
                        </div>
                        <div class="mt-3 rounded-2xl border border-slate-200 bg-white/90 px-4 py-3 text-sm text-slate-600">
                            <p class="font-semibold text-slate-700">Detected Area / Street</p>
                            <p id="participantDetectedLocation" class="mt-1 text-xs leading-5 text-slate-500">
                                The detected place name will appear here automatically after the address is recognized.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-telephone-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Contacts</h2>
                        <p class="participant-section-copy">Parent, guardian, phone, and household contact details.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div>
                        <label class="workspace-field-label">Parent / Guardian Name</label>
                        <input type="text" name="parent_guardian_name" value="{{ old('parent_guardian_name', $record?->parent_guardian_name) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Occupation</label>
                        <input type="text" name="parent_guardian_occupation" value="{{ old('parent_guardian_occupation', $record?->parent_guardian_occupation) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Parent / Guardian Phone</label>
                        <input type="text" name="parent_guardian_phone" value="{{ old('parent_guardian_phone', $record?->parent_guardian_phone) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Caregiver</label>
                        <input type="text" name="caregiver_name" value="{{ old('caregiver_name', $record?->caregiver_name) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Father Status</label>
                        <select name="father_status" class="workspace-select px-4 py-3">
                            <option value="">Select Status</option>
                            <option value="Alive" @selected(in_array(old('father_status', $record?->father_status), ['Alive', 'Hai'], true))>Alive</option>
                            <option value="Deceased" @selected(in_array(old('father_status', $record?->father_status), ['Deceased', 'Wamekufa', 'Amekufa'], true))>Deceased</option>
                        </select>
                    </div>
                    <div>
                        <label class="workspace-field-label">Mother Status</label>
                        <select name="mother_status" class="workspace-select px-4 py-3">
                            <option value="">Select Status</option>
                            <option value="Alive" @selected(in_array(old('mother_status', $record?->mother_status), ['Alive', 'Hai'], true))>Alive</option>
                            <option value="Deceased" @selected(in_array(old('mother_status', $record?->mother_status), ['Deceased', 'Wamekufa', 'Amekufa'], true))>Deceased</option>
                        </select>
                    </div>
                    <div>
                        <label class="workspace-field-label">House Hold Name</label>
                        <input type="text" name="household_name" value="{{ old('household_name', $record?->household_name ?? $record?->household) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">House Hold Phone No</label>
                        <input type="text" name="household_phone" value="{{ old('household_phone', $record?->household_phone) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">House Hold Relationship</label>
                        <input type="text" name="household_relationship" value="{{ old('household_relationship', $record?->household_relationship) }}" class="workspace-input px-4 py-3">
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-diagram-3-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">FCP Association</h2>
                        <p class="participant-section-copy">Cluster, FCP, PF, and national office community assignment.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div><label class="workspace-field-label">Cluster</label><input type="text" name="cluster" value="{{ old('cluster', $record?->cluster) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">FCP Name</label><input type="text" name="fcp_name" value="{{ old('fcp_name', $record?->fcp_name) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">PF</label><input type="text" name="partnership_facilitator" value="{{ old('partnership_facilitator', $record?->partnership_facilitator) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">National Office Community Name</label><input type="text" name="national_office_community_name" value="{{ old('national_office_community_name', $record?->national_office_community_name) }}" class="workspace-input px-4 py-3"></div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Sponsorship Information</h2>
                        <p class="participant-section-copy">Sponsor details, status, contact, and support category. You can add more than one sponsor.</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div id="sponsorEntriesContainer" class="space-y-4">
                        @foreach($sponsorEntries as $index => $sponsorEntry)
                            <div class="sponsor-entry-card">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <h3 class="text-sm font-bold text-slate-900">Sponsor {{ $index + 1 }}</h3>
                                    <button type="button" class="btn-ghost remove-sponsor-entry px-3 py-2 text-xs">Remove</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                                    <div><label class="workspace-field-label">Sponsor Name</label><input type="text" name="sponsor_entries[{{ $index }}][sponsor_name]" value="{{ $sponsorEntry['sponsor_name'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                    <div><label class="workspace-field-label">Sponsored By</label><input type="text" name="sponsor_entries[{{ $index }}][sponsored_by]" value="{{ $sponsorEntry['sponsored_by'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                    <div><label class="workspace-field-label">Sponsorship Type</label><input type="text" name="sponsor_entries[{{ $index }}][sponsorship_type]" value="{{ $sponsorEntry['sponsorship_type'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                    <div>
                                        <label class="workspace-field-label">Sponsorship Status</label>
                                        <select name="sponsor_entries[{{ $index }}][sponsorship_status]" class="workspace-select px-4 py-3">
                                            <option value="">Select Status</option>
                                            @foreach(['Active', 'Inactive', 'Pending'] as $status)
                                                <option value="{{ $status }}" @selected(($sponsorEntry['sponsorship_status'] ?? '') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div><label class="workspace-field-label">Sponsorship Start Date</label><input type="date" name="sponsor_entries[{{ $index }}][sponsorship_start_date]" value="{{ $sponsorEntry['sponsorship_start_date'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                    <div><label class="workspace-field-label">Sponsor Contact</label><input type="text" name="sponsor_entries[{{ $index }}][sponsor_contact]" value="{{ $sponsorEntry['sponsor_contact'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                    <div class="md:col-span-2"><label class="workspace-field-label">Sponsor Physical Address</label><textarea name="sponsor_entries[{{ $index }}][sponsor_physical_address]" rows="3" class="workspace-textarea px-4 py-3">{{ $sponsorEntry['sponsor_physical_address'] ?? '' }}</textarea></div>
                                    <div><label class="workspace-field-label">Sponsorship Category</label><input type="text" name="sponsor_entries[{{ $index }}][sponsorship_category]" value="{{ $sponsorEntry['sponsorship_category'] ?? '' }}" class="workspace-input px-4 py-3"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="addSponsorEntry" class="btn-primary w-full sm:w-auto">
                        <i class="bi bi-plus-lg"></i> Add Another Sponsor
                    </button>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-mortarboard-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Education Background</h2>
                        <p class="participant-section-copy">School placement, class level, and education results where applicable.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div><label class="workspace-field-label">Country</label><input type="text" name="country" value="{{ old('country', $record?->country ?? 'TANZANIA') }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">School Name</label><input type="text" name="school_name" value="{{ old('school_name', $record?->school_name) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Current Class</label><input type="text" name="current_class" value="{{ old('current_class', $record?->current_class) }}" class="workspace-input px-4 py-3"></div>
                    <div>
                        <label class="workspace-field-label">Education Stage</label>
                        <select name="education_stage" id="education_stage" class="workspace-select px-4 py-3">
                            <option value="">Select Stage</option>
                            @foreach(['Primary', 'Secondary', 'University', 'College'] as $stage)
                                <option value="{{ $stage }}" @selected(old('education_stage', $record?->education_stage) === $stage)>{{ $stage }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input id="is_in_school" type="checkbox" name="is_in_school" value="1" @checked(old('is_in_school', $record?->is_in_school ?? true))>
                        <label for="is_in_school" class="workspace-field-label !mb-0">Participant is currently in school</label>
                    </div>
                    <div class="md:col-span-2"><label class="workspace-field-label">Reason Not In School</label><textarea name="not_in_school_reason" rows="3" class="workspace-textarea px-4 py-3">{{ old('not_in_school_reason', $record?->not_in_school_reason) }}</textarea></div>
                </div>

                <div id="primary_results_panel" class="mt-6 rounded-3xl border border-blue-100 bg-blue-50/60 p-5 hidden">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Primary Results</h3>
                        <p class="mt-1 text-sm text-slate-600">Jaza matokeo ya masomo ya primary. Average itahesabiwa moja kwa moja.</p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 compact-fields">
                        <div><label class="workspace-field-label">Kiswahili (%)</label><input id="primary_kiswahili_score" type="number" step="0.01" min="0" max="100" name="primary_kiswahili_score" value="{{ old('primary_kiswahili_score', $record?->primary_kiswahili_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">English (%)</label><input id="primary_english_score" type="number" step="0.01" min="0" max="100" name="primary_english_score" value="{{ old('primary_english_score', $record?->primary_english_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Mathematics (%)</label><input id="primary_mathematics_score" type="number" step="0.01" min="0" max="100" name="primary_mathematics_score" value="{{ old('primary_mathematics_score', $record?->primary_mathematics_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Science (%)</label><input id="primary_science_score" type="number" step="0.01" min="0" max="100" name="primary_science_score" value="{{ old('primary_science_score', $record?->primary_science_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Social Studies (%)</label><input id="primary_social_studies_score" type="number" step="0.01" min="0" max="100" name="primary_social_studies_score" value="{{ old('primary_social_studies_score', $record?->primary_social_studies_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Average (%)</label><input id="primary_score" type="number" step="0.01" min="0" max="100" name="primary_score" value="{{ old('primary_score', $record?->primary_score) }}" class="workspace-input px-4 py-3 bg-slate-50" readonly></div>
                    </div>
                </div>

                <div id="secondary_results_panel" class="mt-6 rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5 hidden">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Secondary Results</h3>
                        <p class="mt-1 text-sm text-slate-600">Jaza matokeo ya masomo ya secondary. Average itahesabiwa moja kwa moja.</p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 compact-fields">
                        <div><label class="workspace-field-label">English (%)</label><input id="secondary_english_score" type="number" step="0.01" min="0" max="100" name="secondary_english_score" value="{{ old('secondary_english_score', $record?->secondary_english_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Mathematics (%)</label><input id="secondary_mathematics_score" type="number" step="0.01" min="0" max="100" name="secondary_mathematics_score" value="{{ old('secondary_mathematics_score', $record?->secondary_mathematics_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Biology (%)</label><input id="secondary_biology_score" type="number" step="0.01" min="0" max="100" name="secondary_biology_score" value="{{ old('secondary_biology_score', $record?->secondary_biology_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Chemistry (%)</label><input id="secondary_chemistry_score" type="number" step="0.01" min="0" max="100" name="secondary_chemistry_score" value="{{ old('secondary_chemistry_score', $record?->secondary_chemistry_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Physics (%)</label><input id="secondary_physics_score" type="number" step="0.01" min="0" max="100" name="secondary_physics_score" value="{{ old('secondary_physics_score', $record?->secondary_physics_score) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Average (%)</label><input id="secondary_average_score" type="number" step="0.01" min="0" max="100" name="secondary_average_score" value="{{ old('secondary_average_score', $record?->secondary_average_score) }}" class="workspace-input px-4 py-3 bg-slate-50" readonly></div>
                    </div>
                </div>

                <div id="university_results_panel" class="mt-6 rounded-3xl border border-violet-100 bg-violet-50/60 p-5 hidden">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">University GPA</h3>
                        <p class="mt-1 text-sm text-slate-600">Jaza GPA za semesters na mfumo utahesabu GPA ya mwisho.</p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 compact-fields">
                        <div><label class="workspace-field-label">Semester 1 GPA</label><input id="university_semester_one_gpa" type="number" step="0.01" min="0" max="5" name="university_semester_one_gpa" value="{{ old('university_semester_one_gpa', $record?->university_semester_one_gpa) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Semester 2 GPA</label><input id="university_semester_two_gpa" type="number" step="0.01" min="0" max="5" name="university_semester_two_gpa" value="{{ old('university_semester_two_gpa', $record?->university_semester_two_gpa) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Semester 3 GPA</label><input id="university_semester_three_gpa" type="number" step="0.01" min="0" max="5" name="university_semester_three_gpa" value="{{ old('university_semester_three_gpa', $record?->university_semester_three_gpa) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Semester 4 GPA</label><input id="university_semester_four_gpa" type="number" step="0.01" min="0" max="5" name="university_semester_four_gpa" value="{{ old('university_semester_four_gpa', $record?->university_semester_four_gpa) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Calculated GPA</label><input id="university_gpa" type="number" step="0.01" min="0" max="5" name="university_gpa" value="{{ old('university_gpa', $record?->university_gpa) }}" class="workspace-input px-4 py-3 bg-slate-50" readonly></div>
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-stars"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Participant Interests And Vision</h2>
                        <p class="participant-section-copy">Interests, strengths, favorite subjects, hobbies, and future vision.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div><label class="workspace-field-label">Things I Like</label><textarea name="things_i_like" rows="3" class="workspace-textarea px-4 py-3">{{ old('things_i_like', $record?->things_i_like) }}</textarea></div>
                    <div><label class="workspace-field-label">Favorite Activities</label><textarea name="favorite_activities" rows="3" class="workspace-textarea px-4 py-3">{{ old('favorite_activities', $record?->favorite_activities) }}</textarea></div>
                    <div><label class="workspace-field-label">Household Duties</label><textarea name="household_duties" rows="3" class="workspace-textarea px-4 py-3">{{ old('household_duties', $record?->household_duties) }}</textarea></div>
                    <div><label class="workspace-field-label">Favorite Subjects</label><textarea name="favorite_subjects" rows="3" class="workspace-textarea px-4 py-3">{{ old('favorite_subjects', $record?->favorite_subjects) }}</textarea></div>
                    <div><label class="workspace-field-label">Hobbies</label><textarea name="hobbies" rows="3" class="workspace-textarea px-4 py-3">{{ old('hobbies', $record?->hobbies) }}</textarea></div>
                    <div><label class="workspace-field-label">Participant Needs</label><textarea name="participant_needs" rows="3" class="workspace-textarea px-4 py-3">{{ old('participant_needs', $record?->participant_needs) }}</textarea></div>
                    <div><label class="workspace-field-label">My Vision For Tomorrow</label><textarea name="vision_for_tomorrow" rows="3" class="workspace-textarea px-4 py-3">{{ old('vision_for_tomorrow', $record?->vision_for_tomorrow) }}</textarea></div>
                </div>
            </div>

            <div class="section-grid">
                <div class="workspace-subpanel compact-section">
                    <div class="participant-section-heading">
                        <span class="participant-section-icon"><i class="bi bi-book-half"></i></span>
                        <div>
                            <h2 class="text-lg font-bold">Spiritual Information</h2>
                            <p class="participant-section-copy">Faith milestones, baptism status, and Christian activities.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 compact-fields">
                        <div><label class="workspace-field-label">Religious Affiliation</label><input type="text" name="religious_affiliation" value="{{ old('religious_affiliation', $record?->religious_affiliation) }}" class="workspace-input px-4 py-3"></div>
                        <div>
                            <label class="workspace-field-label">Baptism Status</label>
                            <select name="baptism_status" class="workspace-select px-4 py-3">
                                <option value="">Select</option>
                                <option value="Baptized" @selected(old('baptism_status', $record?->baptism_status) === 'Baptized')>Baptized</option>
                                <option value="Not Baptized" @selected(old('baptism_status', $record?->baptism_status) === 'Not Baptized')>Not Baptized</option>
                            </select>
                        </div>
                        <div><label class="workspace-field-label">Bible Distributed Date</label><input type="date" name="bible_distributed_date" value="{{ old('bible_distributed_date', $record?->bible_distributed_date?->format('Y-m-d')) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Faith Confession Date</label><input type="date" name="faith_confession_date" value="{{ old('faith_confession_date', $record?->faith_confession_date?->format('Y-m-d')) }}" class="workspace-input px-4 py-3"></div>
                        <div><label class="workspace-field-label">Christian Activities</label><textarea name="christian_activities" rows="3" class="workspace-textarea px-4 py-3">{{ old('christian_activities', $record?->christian_activities) }}</textarea></div>
                    </div>
                </div>

                <div class="workspace-subpanel compact-section">
                    <div class="participant-section-heading">
                        <span class="participant-section-icon"><i class="bi bi-clipboard2-pulse-fill"></i></span>
                        <div>
                            <h2 class="text-lg font-bold">General Assessment</h2>
                            <p class="participant-section-copy">Overall social, physical, emotional, and spiritual assessment.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 compact-fields">
                        <div><label class="workspace-field-label">Social</label><textarea name="general_assessment_social" rows="2" class="workspace-textarea px-4 py-3">{{ old('general_assessment_social', $record?->general_assessment_social) }}</textarea></div>
                        <div><label class="workspace-field-label">Physical</label><textarea name="general_assessment_physical" rows="2" class="workspace-textarea px-4 py-3">{{ old('general_assessment_physical', $record?->general_assessment_physical) }}</textarea></div>
                        <div><label class="workspace-field-label">Emotional</label><textarea name="general_assessment_emotional" rows="2" class="workspace-textarea px-4 py-3">{{ old('general_assessment_emotional', $record?->general_assessment_emotional) }}</textarea></div>
                        <div><label class="workspace-field-label">Spiritual</label><textarea name="general_assessment_spiritual" rows="2" class="workspace-textarea px-4 py-3">{{ old('general_assessment_spiritual', $record?->general_assessment_spiritual) }}</textarea></div>
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-box-arrow-right"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Planned Exit</h2>
                        <p class="participant-section-copy">Transition planning, exit reasons, and lessons from unplanned exit.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div>
                        <label class="workspace-field-label">Participant Status *</label>
                        <select name="participant_status" required class="workspace-select px-4 py-3">
                            @foreach(['Active', 'Planned Exit', 'Exited'] as $status)
                                <option value="{{ $status }}" @selected(old('participant_status', $record?->participant_status ?? 'Active') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="workspace-field-label">Planned Exit Type</label>
                        <select name="planned_exit_type" class="workspace-select px-4 py-3">
                            <option value="">Select Type</option>
                            @foreach(['By Age', 'Manual'] as $type)
                                <option value="{{ $type }}" @selected(old('planned_exit_type', $record?->planned_exit_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Participant reaching 22 years will be marked as planned exit automatically.</p>
                    </div>
                    <div>
                        <label class="workspace-field-label">Planned Completion Date</label>
                        <input type="date" name="planned_completion_date" value="{{ old('planned_completion_date', $record?->planned_completion_date?->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div>
                        <label class="workspace-field-label">Transition Date</label>
                        <input type="date" name="transition_date" value="{{ old('transition_date', $record?->transition_date?->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="workspace-field-label">Reason For Planned Exit</label>
                        <textarea name="planned_exit_reason" rows="3" class="workspace-textarea px-4 py-3">{{ old('planned_exit_reason', $record?->planned_exit_reason) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="workspace-field-label">Lesson For Unplanned Exit</label>
                        <textarea name="unplanned_exit_lessons" rows="3" class="workspace-textarea px-4 py-3">{{ old('unplanned_exit_lessons', $record?->unplanned_exit_lessons) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="workspace-subpanel compact-section">
                <div class="participant-section-heading">
                    <span class="participant-section-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Medical Information</h2>
                        <p class="participant-section-copy">Health profile, chronic illnesses, and general medical notes.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div><label class="workspace-field-label">Weight</label><input type="text" name="weight" value="{{ old('weight', $record?->weight) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Height</label><input type="text" name="height" value="{{ old('height', $record?->height) }}" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Disabilities</label><textarea name="disabilities" rows="3" class="workspace-textarea px-4 py-3">{{ old('disabilities', $record?->disabilities) }}</textarea></div>
                    <div>
                        <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 mt-7">
                            <input
                                type="checkbox"
                                id="has_chronic_illnesses"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                @checked(!empty($selectedChronicIllnesses))
                            >
                            <span class="text-sm font-semibold text-slate-800">Participant has chronic illness</span>
                        </label>
                    </div>
                    <div id="chronic_illnesses_panel" class="{{ empty($selectedChronicIllnesses) ? 'hidden' : '' }}">
                        <label class="workspace-field-label">Chronic Illnesses</label>
                        <div id="chronic_illnesses" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-2xl border border-slate-200 bg-white p-3">
                            @foreach($chronicIllnessOptions as $illnessOption)
                                <label class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm font-medium text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="chronic_illnesses[]"
                                        value="{{ $illnessOption }}"
                                        data-chronic-option="{{ $illnessOption }}"
                                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        @checked(in_array($illnessOption, $selectedKnownChronicIllnesses, true) || ($illnessOption === 'Other' && $hasOtherChronicIllness))
                                    >
                                    <span>{{ $illnessOption }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div id="chronic_illness_other_panel" class="mt-3 {{ $hasOtherChronicIllness ? '' : 'hidden' }}">
                            <label class="workspace-field-label">Other Chronic Illness</label>
                            <input
                                type="text"
                                id="chronic_illness_other"
                                name="chronic_illness_other"
                                value="{{ $manualOtherChronicIllness }}"
                                class="workspace-input px-4 py-3"
                                placeholder="Write chronic illness manually">
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Choose one or more chronic diseases.</p>
                    </div>
                </div>
            </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 pt-2">
        <button type="submit" class="btn-primary">{{ $isEdit ? 'Update Participant' : 'Save Participant' }}</button>
        @if($isEdit)
            <a href="{{ route('participants.show', $record->id) }}" class="btn-ghost">View Profile</a>
        @endif
        <a href="{{ route('participants.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>

<script>
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const educationStage = document.getElementById('education_stage');
    const physicalAddress = document.getElementById('physical_address');
    const gpsLocation = document.getElementById('gps_location');
    const primaryResultsPanel = document.getElementById('primary_results_panel');
    const secondaryResultsPanel = document.getElementById('secondary_results_panel');
    const universityResultsPanel = document.getElementById('university_results_panel');
    const hasChronicIllnesses = document.getElementById('has_chronic_illnesses');
    const chronicIllnessesPanel = document.getElementById('chronic_illnesses_panel');
    const chronicIllnessOtherPanel = document.getElementById('chronic_illness_other_panel');
    const chronicIllnessOtherInput = document.getElementById('chronic_illness_other');
    const chronicIllnessesPanelInputs = () => chronicIllnessesPanel ? chronicIllnessesPanel.querySelectorAll('input[name="chronic_illnesses[]"]') : [];
    const sponsorEntriesContainer = document.getElementById('sponsorEntriesContainer');
    const addSponsorEntryButton = document.getElementById('addSponsorEntry');
    let gpsManuallyEdited = false;

    if (photoInput) {
        photoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (!file) {
                photoPreview.src = '';
                photoPreview.classList.add('hidden');
                photoPlaceholder.classList.remove('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.src = e.target.result;
                photoPreview.classList.remove('hidden');
                photoPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    function calculateAverage(inputIds, outputId, maxValue = 100) {
        const values = inputIds
            .map((id) => document.getElementById(id)?.value)
            .filter((value) => value !== undefined && value !== null && value !== '')
            .map((value) => Number(value))
            .filter((value) => !Number.isNaN(value));

        const output = document.getElementById(outputId);

        if (!output) {
            return;
        }

        if (!values.length) {
            output.value = '';
            return;
        }

        const average = Math.min(maxValue, values.reduce((sum, value) => sum + value, 0) / values.length);
        output.value = average.toFixed(2);
    }

    function syncEducationStagePanels() {
        if (!educationStage) {
            return;
        }

        const stage = educationStage.value;
        primaryResultsPanel?.classList.toggle('hidden', stage !== 'Primary');
        secondaryResultsPanel?.classList.toggle('hidden', stage !== 'Secondary');
        universityResultsPanel?.classList.toggle('hidden', stage !== 'University');
    }

    [
        'primary_kiswahili_score',
        'primary_english_score',
        'primary_mathematics_score',
        'primary_science_score',
        'primary_social_studies_score',
    ].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', () => calculateAverage([
            'primary_kiswahili_score',
            'primary_english_score',
            'primary_mathematics_score',
            'primary_science_score',
            'primary_social_studies_score',
        ], 'primary_score'));
    });

    [
        'secondary_english_score',
        'secondary_mathematics_score',
        'secondary_biology_score',
        'secondary_chemistry_score',
        'secondary_physics_score',
    ].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', () => calculateAverage([
            'secondary_english_score',
            'secondary_mathematics_score',
            'secondary_biology_score',
            'secondary_chemistry_score',
            'secondary_physics_score',
        ], 'secondary_average_score'));
    });

    [
        'university_semester_one_gpa',
        'university_semester_two_gpa',
        'university_semester_three_gpa',
        'university_semester_four_gpa',
    ].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', () => calculateAverage([
            'university_semester_one_gpa',
            'university_semester_two_gpa',
            'university_semester_three_gpa',
            'university_semester_four_gpa',
        ], 'university_gpa', 5));
    });

    educationStage?.addEventListener('change', syncEducationStagePanels);
    syncEducationStagePanels();

    function syncChronicIllnessPanel() {
        if (!hasChronicIllnesses || !chronicIllnessesPanel) {
            return;
        }

        const isChecked = hasChronicIllnesses.checked;
        chronicIllnessesPanel.classList.toggle('hidden', !isChecked);
        chronicIllnessesPanelInputs().forEach((input) => {
            input.disabled = !isChecked;
        });

        if (!isChecked) {
            chronicIllnessesPanelInputs().forEach((input) => {
                input.checked = false;
            });
            if (chronicIllnessOtherInput) {
                chronicIllnessOtherInput.value = '';
            }
        }

        syncChronicIllnessOtherPanel();
    }

    function syncChronicIllnessOtherPanel() {
        const otherInput = chronicIllnessesPanel?.querySelector('input[data-chronic-option="Other"]');
        const showOther = !!(hasChronicIllnesses?.checked && otherInput?.checked);

        if (chronicIllnessOtherPanel) {
            chronicIllnessOtherPanel.classList.toggle('hidden', !showOther);
        }

        if (chronicIllnessOtherInput) {
            chronicIllnessOtherInput.disabled = !showOther;

            if (!showOther) {
                chronicIllnessOtherInput.value = '';
            }
        }
    }

    hasChronicIllnesses?.addEventListener('change', syncChronicIllnessPanel);
    chronicIllnessesPanelInputs().forEach((input) => {
        input.addEventListener('change', syncChronicIllnessOtherPanel);
    });
    syncChronicIllnessPanel();
    syncChronicIllnessOtherPanel();

    calculateAverage([
        'primary_kiswahili_score',
        'primary_english_score',
        'primary_mathematics_score',
        'primary_science_score',
        'primary_social_studies_score',
    ], 'primary_score');
    calculateAverage([
        'secondary_english_score',
        'secondary_mathematics_score',
        'secondary_biology_score',
        'secondary_chemistry_score',
        'secondary_physics_score',
    ], 'secondary_average_score');
    calculateAverage([
        'university_semester_one_gpa',
        'university_semester_two_gpa',
        'university_semester_three_gpa',
        'university_semester_four_gpa',
    ], 'university_gpa', 5);

    const houseNumberInput = document.getElementById('house_number');
    const participantMapFrame = document.getElementById('participantMapFrame');
    const participantMapPlaceholder = document.getElementById('participantMapPlaceholder');
    const participantDetectedLocation = document.getElementById('participantDetectedLocation');
    let gpsLookupTimer = null;

    function buildParticipantAddressParts() {
        return {
            physicalAddress: physicalAddress?.value?.trim() ?? '',
            houseNumber: houseNumberInput?.value?.trim() ?? '',
            country: 'Tanzania',
        };
    }

    function buildParticipantAddressQuery(parts = buildParticipantAddressParts()) {
        return [
            parts.physicalAddress,
            parts.houseNumber,
            parts.country,
        ].filter(Boolean).join(', ');
    }

    function buildParticipantAddressQueryVariants() {
        const parts = buildParticipantAddressParts();

        return [
            buildParticipantAddressQuery(parts),
            [parts.physicalAddress, parts.country].filter(Boolean).join(', '),
        ].filter((value, index, array) => value && array.indexOf(value) === index);
    }

    function updateParticipantMap(query, coordinates = null) {
        if (!participantMapFrame || !participantMapPlaceholder) {
            return;
        }

        if (!query || query.length < 5) {
            participantMapFrame.src = '';
            participantMapFrame.classList.add('hidden');
            participantMapPlaceholder.classList.remove('hidden');
            return;
        }

        const mapQuery = coordinates?.lat && coordinates?.lon
            ? `${coordinates.lat},${coordinates.lon}`
            : query;

        participantMapFrame.src = `https://www.google.com/maps?q=${encodeURIComponent(mapQuery)}&t=k&z=18&output=embed`;
        participantMapFrame.classList.remove('hidden');
        participantMapPlaceholder.classList.add('hidden');
    }

    function updateDetectedLocationLabel(label = '') {
        if (!participantDetectedLocation) {
            return;
        }

        participantDetectedLocation.textContent = label && label.trim() !== ''
            ? label
            : 'The detected place name will appear here automatically after the address is recognized.';
    }

    async function lookupAddressCoordinates(queries) {
        if (!gpsLocation || !Array.isArray(queries) || queries.length === 0) {
            return null;
        }

        for (const query of queries) {
            if (!query || query.length < 5) {
                continue;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    continue;
                }

                const results = await response.json();
                const firstMatch = Array.isArray(results) ? results[0] : null;

                if (firstMatch?.lat && firstMatch?.lon) {
                    gpsLocation.value = `${firstMatch.lat}, ${firstMatch.lon}`;
                    return {
                        lat: firstMatch.lat,
                        lon: firstMatch.lon,
                        displayName: firstMatch.display_name ?? query,
                        matchedQuery: query,
                    };
                }
            } catch (error) {
                // Try the next broader query.
            }
        }

        return null;
    }

    function syncGpsLocationFromAddress() {
        const query = buildParticipantAddressQuery();
        const queryVariants = buildParticipantAddressQueryVariants();

        updateParticipantMap(query);

        if (!gpsLocation) {
            return;
        }

        gpsLocation.value = query;

        if (gpsLookupTimer) {
            window.clearTimeout(gpsLookupTimer);
        }

        if (!query) {
            updateDetectedLocationLabel('');
            return;
        }

        gpsLookupTimer = window.setTimeout(() => {
            lookupAddressCoordinates(queryVariants).then((match) => {
                if (match) {
                    updateParticipantMap(match.displayName || query, match);
                    updateDetectedLocationLabel(match.displayName || match.matchedQuery || query);
                } else {
                    updateDetectedLocationLabel(query);
                }
            });
        }, 650);
    }

    [physicalAddress, houseNumberInput].forEach((input) => {
        input?.addEventListener('input', syncGpsLocationFromAddress);
    });
    syncGpsLocationFromAddress();

    function sponsorEntryTemplate(index) {
        return `
            <div class="sponsor-entry-card">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Sponsor ${index + 1}</h3>
                    <button type="button" class="btn-ghost remove-sponsor-entry px-3 py-2 text-xs">Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 compact-fields">
                    <div><label class="workspace-field-label">Sponsor Name</label><input type="text" name="sponsor_entries[${index}][sponsor_name]" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Sponsored By</label><input type="text" name="sponsor_entries[${index}][sponsored_by]" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Sponsorship Type</label><input type="text" name="sponsor_entries[${index}][sponsorship_type]" class="workspace-input px-4 py-3"></div>
                    <div>
                        <label class="workspace-field-label">Sponsorship Status</label>
                        <select name="sponsor_entries[${index}][sponsorship_status]" class="workspace-select px-4 py-3">
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div><label class="workspace-field-label">Sponsorship Start Date</label><input type="date" name="sponsor_entries[${index}][sponsorship_start_date]" class="workspace-input px-4 py-3"></div>
                    <div><label class="workspace-field-label">Sponsor Contact</label><input type="text" name="sponsor_entries[${index}][sponsor_contact]" class="workspace-input px-4 py-3"></div>
                    <div class="md:col-span-2"><label class="workspace-field-label">Sponsor Physical Address</label><textarea name="sponsor_entries[${index}][sponsor_physical_address]" rows="3" class="workspace-textarea px-4 py-3"></textarea></div>
                    <div><label class="workspace-field-label">Sponsorship Category</label><input type="text" name="sponsor_entries[${index}][sponsorship_category]" class="workspace-input px-4 py-3"></div>
                </div>
            </div>
        `;
    }

    function bindSponsorEntryRemoval() {
        sponsorEntriesContainer?.querySelectorAll('.remove-sponsor-entry').forEach((button) => {
            button.onclick = function () {
                const cards = sponsorEntriesContainer.querySelectorAll('.sponsor-entry-card');

                if (cards.length <= 1) {
                    const inputs = cards[0].querySelectorAll('input, textarea, select');
                    inputs.forEach((input) => {
                        if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        } else {
                            input.value = '';
                        }
                    });
                    return;
                }

                this.closest('.sponsor-entry-card')?.remove();
            };
        });
    }

    addSponsorEntryButton?.addEventListener('click', function () {
        if (!sponsorEntriesContainer) {
            return;
        }

        const index = sponsorEntriesContainer.querySelectorAll('.sponsor-entry-card').length;
        sponsorEntriesContainer.insertAdjacentHTML('beforeend', sponsorEntryTemplate(index));
        bindSponsorEntryRemoval();
    });

    bindSponsorEntryRemoval();
</script>
