<x-app-layout>
    @once
        <style>
            .participant-profile-page .workspace-hero {
                padding: 1.25rem 1.45rem;
            }
            .participant-profile-page .workspace-subpanel,
            .participant-profile-page .workspace-panel {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                border: 1px solid rgba(148, 163, 184, 0.12);
                box-shadow: 0 16px 34px -32px rgba(15, 23, 42, 0.12);
            }
            .participant-profile-page .workspace-subpanel h2,
            .participant-profile-page .workspace-panel h1,
            .participant-profile-page .workspace-panel h2 {
                color: #0f172a;
            }
            .participant-profile-page .workspace-subpanel p,
            .participant-profile-page .workspace-panel p,
            .participant-profile-page .workspace-subpanel div,
            .participant-profile-page .workspace-panel div {
                color: #475569;
            }
            .participant-profile-page .workspace-label {
                color: #2563eb;
            }
            .participant-profile-page .profile-chip {
                background: #eff6ff;
                color: #1d4ed8;
                border: 1px solid #bfdbfe;
            }
            .participant-profile-page .participant-map-card {
                overflow: hidden;
                border-radius: 1.2rem;
                border: 1px solid rgba(191, 219, 254, 0.88);
                background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(248,250,252,0.97));
            }
            .participant-profile-page .participant-map-card iframe {
                display: block;
                width: 100%;
                height: 280px;
                border: 0;
                background: #e2e8f0;
            }
        </style>
    @endonce
    <div class="workspace-page">
        <div class="workspace-container participant-profile-page">
            <div class="space-y-6">

                {{-- Hero Header --}}
                <div class="workspace-panel overflow-hidden">
                    <div class="workspace-hero px-6 md:px-8 py-6">
                        @php
                            $participantDisplayName = trim((string) ($participant->preferred_name ?? ''));
                            $participantDisplayName = $participantDisplayName !== ''
                                ? $participantDisplayName
                                : ($participant->account_name ?: ($participant->project_name ?: 'Participant'));
                        @endphp
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                                <div class="w-28 h-28 rounded-3xl bg-white/10 border border-white/20 overflow-hidden flex items-center justify-center shadow">
                                    @if($participant->photo_url)
                                        <img src="{{ $participant->photo_url }}"
                                             alt="{{ $participantDisplayName }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="text-slate-300 text-sm text-center px-2">No Photo</div>
                                    @endif
                                </div>

                                <div>
                                    <p class="workspace-label">Participant Profile</p>
                                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                                        {{ $participantDisplayName }}
                                    </h1>

                                    <p class="text-slate-600 mt-1">
                                        Participant ID: {{ $participant->local_participant_id ?? 'N/A' }}
                                    </p>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="profile-chip px-3 py-1 rounded-full text-xs font-semibold">
                                            {{ $participant->participant_status ?? 'No Status' }}
                                        </span>

                                        <span class="profile-chip px-3 py-1 rounded-full text-xs font-semibold">
                                            Center: {{ $participant->center_id ?? 'N/A' }}
                                        </span>

                                        @if(method_exists($participant, 'isPhotoDueForUpdate') && $participant->isPhotoDueForUpdate())
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-100 border border-red-300/30">
                                                Photo Update Due
                                            </span>
                                        @elseif($participant->photo)
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-100 border border-emerald-300/30">
                                                Photo Updated
                                            </span>
                                        @else
                                            <span class="profile-chip px-3 py-1 rounded-full text-xs font-semibold">
                                                No Photo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('participants.edit', $participant->id) }}"
                                   class="btn-primary">
                                    Edit Participant
                                </a>

                                <a href="{{ route('sponsorships.index', ['search' => $participant->local_participant_id]) }}"
                                   class="btn-action btn-action-green">
                                    Sponsorship Info
                                </a>

                                <a href="{{ route('sponsorships.create', ['participant_id' => $participant->id]) }}"
                                   class="btn-action btn-action-green">
                                    Add Sponsorship
                                </a>

                                <a href="{{ route('participants.index') }}"
                                   class="btn-ghost">
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Top Info Area --}}
                    <div class="p-6 md:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                            {{-- Photo Information --}}
                            <div class="workspace-subpanel p-6">
                                <h2 class="text-lg font-bold text-white mb-4">Photo Information</h2>

                                <div class="space-y-3 text-sm text-slate-300">
                                    <p>
                                        <span class="font-semibold">Last Photo Update:</span>
                                        {{ $participant->photo_updated_at ? $participant->photo_updated_at->format('Y-m-d') : 'N/A' }}
                                    </p>

                                    <p>
                                        <span class="font-semibold">Next Update Due:</span>
                                        {{ $participant->next_photo_update_due_at ? $participant->next_photo_update_due_at->format('Y-m-d') : 'N/A' }}
                                    </p>

                                    <p>
                                        <span class="font-semibold">Photo Status:</span>
                                        @if(method_exists($participant, 'isPhotoDueForUpdate') && $participant->isPhotoDueForUpdate())
                                            <span class="text-rose-300 font-semibold">Due for Update</span>
                                        @elseif($participant->photo)
                                            <span class="text-emerald-300 font-semibold">Updated</span>
                                        @else
                                            <span class="text-slate-400 font-semibold">No Photo</span>
                                        @endif
                                    </p>
                                </div>

                                @if(method_exists($participant, 'isPhotoDueForUpdate') && $participant->isPhotoDueForUpdate())
                                    <div class="workspace-flash-error mt-4 p-4 text-sm">
                                        This participant photo needs to be updated.
                                    </div>
                                @endif
                            </div>

                            {{-- Basic Information --}}
                            <div class="lg:col-span-2 workspace-subpanel p-6">
                                <h2 class="text-lg font-bold text-white mb-5">Basic Information</h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-300">
                                    <p><span class="font-semibold">Local Participant Number:</span> {{ $participant->local_participant_number ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Local Participant ID:</span> {{ $participant->local_participant_id ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Project Name:</span> {{ $participant->project_name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Full Name:</span> {{ $participant->preferred_name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Gender:</span> {{ $participant->gender ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Birthdate:</span> {{ $participant->birthdate ? $participant->birthdate->format('Y-m-d') : 'N/A' }}</p>
                                    <p><span class="font-semibold">Age:</span> {{ $participant->age ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Participant Status:</span> {{ $participant->participant_status ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">Sponsorship Status:</span> {{ $participant->current_sponsorship_status ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Latest Sponsorship Summary --}}
                <div class="workspace-subpanel p-6 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-white">Latest Sponsorship Information</h2>
                            <p class="text-sm text-slate-400 mt-1">
                                Most recent sponsorship details linked to this participant.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('sponsorships.index', ['search' => $participant->local_participant_id]) }}"
                               class="btn-action btn-action-green">
                                View Sponsorships
                            </a>

                            <a href="{{ route('sponsorships.create', ['participant_id' => $participant->id]) }}"
                               class="btn-primary">
                                + Add Sponsorship
                            </a>
                        </div>
                    </div>

                    @if($participant->sponsorships->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 text-sm text-slate-300">

                            <div class="workspace-subpanel p-4">
                                <p class="text-slate-400 text-xs uppercase tracking-wide">Sponsorship Status</p>
                                <p class="mt-2 font-semibold text-white">
                                    {{ $participant->current_sponsorship_status ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="workspace-subpanel p-4">
                                <p class="text-slate-400 text-xs uppercase tracking-wide">Sponsored By</p>
                                <p class="mt-2 font-semibold text-white">
                                    {{ $participant->current_sponsored_by ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="workspace-subpanel p-4">
                                <p class="text-slate-400 text-xs uppercase tracking-wide">Start Date</p>
                                <p class="mt-2 font-semibold text-white">
                                    {{ $participant->current_sponsorship_start_date ? \Illuminate\Support\Carbon::parse($participant->current_sponsorship_start_date)->format('Y-m-d') : 'N/A' }}
                                </p>
                            </div>

                            <div class="workspace-subpanel p-4">
                                <p class="text-slate-400 text-xs uppercase tracking-wide">Category</p>
                                <p class="mt-2 font-semibold text-white">
                                    {{ $participant->current_sponsorship_category ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                            @foreach($participant->sponsorships as $sponsorship)
                                <div class="workspace-subpanel p-4">
                                    <p><span class="font-semibold">Sponsor Name:</span> {{ $sponsorship->sponsor_name ?: $sponsorship->sponsored_by ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Sponsored By:</span> {{ $sponsorship->sponsored_by ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Sponsorship Type:</span> {{ $sponsorship->sponsorship_type ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Status:</span> {{ $sponsorship->sponsorship_status ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Start Date:</span> {{ $sponsorship->sponsorship_start_date?->format('Y-m-d') ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Contact:</span> {{ $sponsorship->sponsor_contact ?: 'N/A' }}</p>
                                    <p class="mt-2"><span class="font-semibold">Category:</span> {{ $sponsorship->sponsorship_category ?: 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="workspace-subpanel p-5 text-sm text-slate-400">
                            No sponsorship information added yet for this participant.
                        </div>
                    @endif
                </div>

                {{-- Remaining Sections --}}
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">C. FCP Association</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-300">
                            <p><span class="font-semibold">Cluster:</span> {{ $participant->cluster ?? 'N/A' }}</p>
                            <p><span class="font-semibold">FCP Name:</span> {{ $participant->fcp_name ?? 'N/A' }}</p>
                            <p><span class="font-semibold">PF:</span> {{ $participant->partnership_facilitator ?? 'N/A' }}</p>
                            <p><span class="font-semibold">National Office Community Name:</span> {{ $participant->national_office_community_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Planned Exit</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-300">
                            <p><span class="font-semibold">Planned Exit Type:</span> {{ $participant->planned_exit_type ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Planned Completion Date:</span> {{ $participant->planned_completion_date ? $participant->planned_completion_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><span class="font-semibold">Transition Date:</span> {{ $participant->transition_date ? $participant->transition_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><span class="font-semibold">Planned Exit Reason:</span> {{ $participant->planned_exit_reason ?? 'N/A' }}</p>
                            <p class="md:col-span-2"><span class="font-semibold">Lesson For Unplanned Exit:</span> {{ $participant->unplanned_exit_lessons ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Address Information</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <p><span class="font-semibold">Physical Address:</span> {{ $participant->physical_address ?? $participant->address ?? 'N/A' }}</p>
                            <p><span class="font-semibold">House Number:</span> {{ $participant->house_number ?? 'N/A' }}</p>
                            <p><span class="font-semibold">GPS Location:</span> {{ $participant->gps_location ?? 'N/A' }}</p>
                        </div>

                        @if($participant->map_embed_url)
                            <div class="mt-5 participant-map-card">
                                <iframe
                                    src="{{ $participant->map_embed_url }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Participant saved map view"></iframe>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-3">
                                <a href="https://www.google.com/maps?q={{ urlencode($participant->map_query) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn-ghost">
                                    Open in Maps
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Participant Interests And Vision</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <p><span class="font-semibold">Things I Like:</span> {{ $participant->things_i_like ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Favorite Activities:</span> {{ $participant->favorite_activities ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Household Duties:</span> {{ $participant->household_duties ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Favorite Subjects:</span> {{ $participant->favorite_subjects ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Hobbies:</span> {{ $participant->hobbies ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Participant Needs:</span> {{ $participant->participant_needs ?? 'N/A' }}</p>
                            <p><span class="font-semibold">My Vision For Tomorrow:</span> {{ $participant->vision_for_tomorrow ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Education Background</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-300">
                            <p><span class="font-semibold">Country:</span> {{ $participant->country ?? 'N/A' }}</p>
                            <p><span class="font-semibold">School Name:</span> {{ $participant->school_name ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Current Class:</span> {{ $participant->current_class ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Education Stage:</span> {{ $participant->education_stage ?? 'N/A' }}</p>
                            @if($participant->education_stage === 'Primary')
                                <p><span class="font-semibold">Primary Average:</span> {{ $participant->primary_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Calculated Grade:</span> {{ $participant->education_grade ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Primary Kiswahili:</span> {{ $participant->primary_kiswahili_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Primary English:</span> {{ $participant->primary_english_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Primary Mathematics:</span> {{ $participant->primary_mathematics_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Primary Science:</span> {{ $participant->primary_science_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Primary Social Studies:</span> {{ $participant->primary_social_studies_score ?? 'N/A' }}</p>
                            @elseif($participant->education_stage === 'Secondary')
                                <p><span class="font-semibold">Secondary Average:</span> {{ $participant->secondary_average_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Calculated Grade:</span> {{ $participant->education_grade ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Secondary English:</span> {{ $participant->secondary_english_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Secondary Mathematics:</span> {{ $participant->secondary_mathematics_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Secondary Biology:</span> {{ $participant->secondary_biology_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Secondary Chemistry:</span> {{ $participant->secondary_chemistry_score ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Secondary Physics:</span> {{ $participant->secondary_physics_score ?? 'N/A' }}</p>
                            @elseif($participant->education_stage === 'University')
                                <p><span class="font-semibold">Semester 1 GPA:</span> {{ $participant->university_semester_one_gpa ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Semester 2 GPA:</span> {{ $participant->university_semester_two_gpa ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Semester 3 GPA:</span> {{ $participant->university_semester_three_gpa ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Semester 4 GPA:</span> {{ $participant->university_semester_four_gpa ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Calculated GPA:</span> {{ $participant->university_gpa ?? 'N/A' }}</p>
                                <p><span class="font-semibold">Calculated Grade:</span> {{ $participant->education_grade ?? 'N/A' }}</p>
                            @endif
                            <p><span class="font-semibold">In School:</span> {{ $participant->is_in_school ? 'Yes' : 'No' }}</p>
                            <p class="md:col-span-2"><span class="font-semibold">Reason Not In School:</span> {{ $participant->not_in_school_reason ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Spiritual Information</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <p><span class="font-semibold">Religious Affiliation:</span> {{ $participant->religious_affiliation ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Baptism Status:</span> {{ $participant->baptism_status ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Bible Distributed Date:</span> {{ $participant->bible_distributed_date ? $participant->bible_distributed_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><span class="font-semibold">Faith Confession Date:</span> {{ $participant->faith_confession_date ? $participant->faith_confession_date->format('Y-m-d') : 'N/A' }}</p>
                            <p><span class="font-semibold">Christian Activities:</span> {{ $participant->christian_activities ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">Contacts</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <p><span class="font-semibold">Parent / Guardian Name:</span> {{ $participant->parent_guardian_name ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Occupation:</span> {{ $participant->parent_guardian_occupation ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Phone:</span> {{ $participant->parent_guardian_phone ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Caregiver:</span> {{ $participant->caregiver_name ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Father Status:</span> {{ match(mb_strtolower((string) ($participant->father_status ?? ''))) { 'hai', 'alive' => 'Alive', 'wamekufa', 'amekufa', 'dead', 'deceased' => 'Deceased', '' => 'N/A', default => $participant->father_status } }}</p>
                            <p><span class="font-semibold">Mother Status:</span> {{ match(mb_strtolower((string) ($participant->mother_status ?? ''))) { 'hai', 'alive' => 'Alive', 'wamekufa', 'amekufa', 'dead', 'deceased' => 'Deceased', '' => 'N/A', default => $participant->mother_status } }}</p>
                            <p><span class="font-semibold">House Hold Name:</span> {{ $participant->household_name ?? $participant->household ?? 'N/A' }}</p>
                            <p><span class="font-semibold">House Hold Phone No:</span> {{ $participant->household_phone ?? 'N/A' }}</p>
                            <p><span class="font-semibold">House Hold Relationship:</span> {{ $participant->household_relationship ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6">
                        <h2 class="text-lg font-bold text-white mb-5">General Assessment</h2>
                        <div class="space-y-3 text-sm text-slate-300">
                            <p><span class="font-semibold">Social:</span> {{ $participant->general_assessment_social ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Physical:</span> {{ $participant->general_assessment_physical ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Emotional:</span> {{ $participant->general_assessment_emotional ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Spiritual:</span> {{ $participant->general_assessment_spiritual ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="workspace-subpanel p-6 xl:col-span-2">
                        <h2 class="text-lg font-bold text-white mb-5">Medical Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-300">
                            <p><span class="font-semibold">Weight:</span> {{ $participant->weight ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Height:</span> {{ $participant->height ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Disabilities:</span> {{ $participant->disabilities ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Chronic Illnesses:</span> {{ $participant->chronic_illnesses ?? 'N/A' }}</p>
                            <p class="md:col-span-2"><span class="font-semibold">Treatment Records:</span> Managed in the standalone Treatment module.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
