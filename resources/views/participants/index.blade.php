<x-app-layout>
<x-slot name="header">Participants</x-slot>
<div class="workspace-page">
    <div class="workspace-container space-y-6">

        @php
            $photoDueCount = $participants->filter(function ($participant) {
                return method_exists($participant, 'isPhotoDueForUpdate') && $participant->isPhotoDueForUpdate();
            })->count();
        @endphp

        @once
            <style>
                .participants-hero {
                    padding: 1.35rem 1.45rem;
                }
                .participants-hero-title {
                    font-size: clamp(1.9rem, 2.8vw, 2.7rem);
                    line-height: 1;
                    color: #0f172a;
                }
                .participants-stat {
                    padding: 0.95rem 1rem;
                    min-height: 7.9rem;
                    box-shadow: 0 14px 30px -30px rgba(15, 23, 42, 0.14);
                }
                .participants-stat h3 {
                    font-size: clamp(1.55rem, 2vw, 2rem);
                    line-height: 1;
                    margin-top: 0.65rem;
                }
                .participants-table-wrap {
                    overflow-x: auto;
                }
                .participants-table {
                    width: max-content;
                    min-width: 100%;
                    table-layout: auto;
                }
                .participants-table th {
                    white-space: nowrap;
                    background: rgba(248, 250, 252, 0.96);
                    font-size: 0.58rem;
                    padding: 0.42rem 0.36rem;
                    letter-spacing: 0.05em;
                }
                .participants-table td {
                    vertical-align: top;
                    font-size: 0.69rem;
                    padding: 0.42rem 0.36rem;
                    line-height: 1.15;
                    white-space: nowrap;
                }
                .participants-table .table-primary {
                    color: #0f172a;
                    font-weight: 700;
                    font-size: 0.7rem;
                }
                .participants-table .table-secondary {
                    color: #475569;
                    font-size: 0.67rem;
                }
                .participants-table .status-badge {
                    padding: 0.14rem 0.32rem;
                    font-size: 0.52rem;
                    gap: 0.18rem;
                }
                .participants-table .btn-action,
                .participants-table .btn-ghost {
                    padding: 0.28rem 0.38rem;
                    font-size: 0.58rem;
                    border-radius: 0.55rem;
                    white-space: nowrap;
                }
                .participants-table .participant-photo-thumb {
                    width: 1.8rem;
                    height: 1.8rem;
                }
                .participants-table .participant-id-badge {
                    padding: 0.22rem 0.34rem;
                    font-size: 0.54rem;
                    border-radius: 0.5rem;
                }
                .participants-table .compact-address {
                    min-width: 118px;
                    max-width: 118px;
                }
                .participants-table .compact-actions {
                    min-width: 148px;
                }
                .participants-table .compact-actions > div {
                    display: flex;
                    flex-wrap: nowrap;
                    gap: 0.28rem;
                }
                .participants-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 0.65rem;
                }
                .participants-summary-card {
                    border-radius: 1.15rem;
                    border: 1px solid rgba(191, 219, 254, 0.95);
                    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                    box-shadow: 0 18px 36px -28px rgba(15, 23, 42, 0.12);
                    padding: 0.72rem 0.8rem;
                }
                .participants-summary-card:hover {
                    background: rgba(239, 246, 255, 0.55);
                }
                .participants-summary-metric {
                    color: #0f172a;
                    font-weight: 800;
                    font-size: 0.82rem;
                }
                .participants-summary-copy {
                    color: #64748b;
                    font-size: 0.68rem;
                    margin-top: 0.1rem;
                    line-height: 1.3;
                }
                .participants-summary-value {
                    color: #0f172a;
                    font-size: 1.05rem;
                    font-weight: 900;
                    line-height: 1;
                }
                .participants-summary-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.38rem;
                    padding: 0.24rem 0.46rem;
                    border-radius: 999px;
                    border: 1px solid rgba(191, 219, 254, 0.95);
                    background: rgba(239, 246, 255, 0.92);
                    color: #2563eb;
                    font-size: 0.56rem;
                    font-weight: 800;
                    letter-spacing: 0.08em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }
                .participants-empty-icon {
                    width: 4.25rem;
                    height: 4.25rem;
                    border-radius: 999px;
                    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
                    color: #2563eb;
                    font-size: 0;
                }
                .participants-empty-icon::before {
                    content: '\f4cf';
                    font-family: 'bootstrap-icons';
                    font-size: 1.4rem;
                }
                @media (max-width: 768px) {
                    .participants-hero {
                        padding: 1.25rem;
                    }
                    .participants-stat {
                        min-height: auto;
                    }
                    .participants-summary-grid {
                        grid-template-columns: 1fr;
                    }
                }
                @media (max-width: 1280px) {
                    .participants-summary-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }
            </style>
        @endonce

        {{-- Header --}}
        <div class="workspace-hero participants-hero">
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="workspace-label">Participants Workspace</p>
                <h1 class="participants-hero-title font-black mt-3">Participants</h1>
                <p class="text-slate-600 text-sm mt-3 max-w-2xl leading-7">
                            Manage project records, photos, sponsorship links, exits, and status updates in one place.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <form method="GET" action="{{ route('participants.index') }}" class="flex flex-col sm:flex-row gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, ID, center, or status..."
                        class="workspace-input w-full sm:w-80 px-4 py-2.5 text-sm shadow-sm"
                    >

                    <button
                        type="submit"
                        class="btn-primary justify-center">
                        Search
                    </button>

                    @if(request('search'))
                        <a href="{{ route('participants.index') }}"
                           class="btn-ghost text-center">
                            Reset
                        </a>
                    @endif
                </form>

                <a href="{{ route('participants.create') }}"
                   class="btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Participant
                </a>
            </div>
            </div>
        </div>

        {{-- Summary Table --}}
        <div class="participants-summary-grid">
            <div class="participants-summary-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="participants-summary-metric">Total Records</div>
                        <div class="participants-summary-copy">All participants in current result set.</div>
                    </div>
                    <span class="participants-summary-value">{{ $participants->total() }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="participants-summary-pill"><i class="bi bi-collection"></i> Directory</span>
                    <span class="text-slate-600 text-xs text-right">Complete participant records available in this search scope.</span>
                </div>
            </div>

            <div class="participants-summary-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="participants-summary-metric">Current Page</div>
                        <div class="participants-summary-copy">Browsing paginated participant records.</div>
                    </div>
                    <span class="participants-summary-value">{{ $participants->currentPage() }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="participants-summary-pill"><i class="bi bi-file-earmark"></i> Page View</span>
                    <span class="text-slate-600 text-xs text-right">Shows the page number currently being viewed.</span>
                </div>
            </div>

            <div class="participants-summary-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="participants-summary-metric">Showing</div>
                        <div class="participants-summary-copy">Participants visible on this page.</div>
                    </div>
                    <span class="participants-summary-value">{{ $participants->count() }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="participants-summary-pill"><i class="bi bi-eye"></i> Visible</span>
                    <span class="text-slate-600 text-xs text-right">Records currently visible before moving to the next page.</span>
                </div>
            </div>

            <div class="participants-summary-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="participants-summary-metric">Photo Updates Due</div>
                        <div class="participants-summary-copy">Participants needing photo update.</div>
                    </div>
                    <span class="participants-summary-value">{{ $photoDueCount }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <span class="participants-summary-pill"><i class="bi bi-camera"></i> Follow Up</span>
                    <span class="text-slate-600 text-xs text-right">Records approaching or past the 18-month photo refresh period.</span>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="workspace-flash-success p-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="workspace-flash-error p-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="workspace-panel overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200/80">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="workspace-label">Directory</p>
                        <h2 class="text-xl font-bold text-slate-900 mt-2">Participants List</h2>
                        <p class="text-sm text-slate-600 mt-1">
                            View form-related participant details across basic info, contacts, address, FCP, sponsorship, and education.
                        </p>
                    </div>

                    @if(request('search'))
                        <div class="text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 font-medium">
                            Search result for: <span class="font-semibold">{{ request('search') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="participants-table-wrap">
                <table class="w-full modern-table participants-table">
                    <thead>
                        <tr>
                            <th class="text-left">Photo</th>
                            <th class="text-left">Participant ID</th>
                            <th class="text-left">Project Name</th>
                            <th class="text-left">Full Name</th>
                            <th class="text-left">Age</th>
                            <th class="text-left">Gender</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Center</th>
                            <th class="text-left">Address</th>
                            <th class="text-left">Parent / Guardian</th>
                            <th class="text-left">Phone</th>
                            <th class="text-left">House Hold Name</th>
                            <th class="text-left">House Hold Phone</th>
                            <th class="text-left">House Hold Relationship</th>
                            <th class="text-left">Cluster</th>
                            <th class="text-left">FCP Name</th>
                            <th class="text-left">PF</th>
                            <th class="text-left">National Office Community</th>
                            <th class="text-left">School Name</th>
                            <th class="text-left">Current Class</th>
                            <th class="text-left">Education Stage</th>
                            <th class="text-left">Photo Status</th>
                            <th class="text-left">Next Update Due</th>
                            <th class="text-left">Latest Sponsor</th>
                            <th class="text-left">Sponsor Contact</th>
                            <th class="text-left">Sponsorship Status</th>
                            <th class="text-left">Sponsorship Type</th>
                            <th class="text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                {{-- Photo --}}
                                <td>
                                    @if($participant->photo_url)
                                        <img src="{{ $participant->photo_url }}"
                                             alt="{{ $participant->project_name }}"
                                             class="participant-photo-thumb rounded-lg object-cover border border-slate-200 shadow-sm">
                                    @else
                                        <div class="participant-photo-thumb bg-slate-100 rounded-lg flex items-center justify-center text-[9px] text-slate-500 border border-slate-200">
                                            No Photo
                                        </div>
                                    @endif
                                </td>

                                {{-- Participant ID --}}
                                <td><span class="participant-id-badge inline-flex bg-slate-100 text-slate-700 font-mono border border-slate-200">{{ $participant->local_participant_id ?? '-' }}</span></td>

                                {{-- Name --}}
                                <td>
                                    <div class="table-primary whitespace-nowrap">
                                        {{ $participant->project_name ?? '-' }}
                                    </div>
                                </td>

                                {{-- Full Name --}}
                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->preferred_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->age ?? '-' }}
                                </td>

                                {{-- Gender --}}
                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->gender ?? '-' }}
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap">
                                    @php
                                        $status = $participant->participant_status;
                                    @endphp

                                    @if($status === 'Active')
                                        <span class="status-badge badge-active"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Active</span>
                                    @elseif($status === 'Exited')
                                        <span class="status-badge badge-exited"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Exited</span>
                                    @elseif($status === 'Planned Exit')
                                        <span class="status-badge badge-planned"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Planned Exit</span>
                                    @else
                                        <span class="status-badge badge-default"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $status ?? 'N/A' }}</span>
                                    @endif
                                </td>

                                {{-- Center --}}
                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->center_id ?? '-' }}
                                </td>

                                <td class="table-secondary compact-address">
                                    <div>{{ $participant->physical_address ?: '-' }}</div>
                                    @if($participant->house_number || $participant->region_city_street)
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $participant->house_number ?: '' }}{{ $participant->house_number && $participant->region_city_street ? ', ' : '' }}{{ $participant->region_city_street ?: '' }}
                                        </div>
                                    @endif
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->parent_guardian_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->parent_guardian_phone ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->household_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->household_phone ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->household_relationship ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->cluster ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->fcp_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->partnership_facilitator ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->national_office_community_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->school_name ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->current_class ?: '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->education_stage ?: '-' }}
                                </td>

                                {{-- Photo Status --}}
                                <td class="whitespace-nowrap">
                                    @if(method_exists($participant, 'isPhotoDueForUpdate') && $participant->isPhotoDueForUpdate())
                                        <span class="status-badge badge-exited"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Due for Update</span>
                                    @elseif($participant->photo)
                                        <span class="status-badge badge-active"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Updated</span>
                                    @else
                                        <span class="status-badge badge-default"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>No Photo</span>
                                    @endif
                                </td>

                                {{-- Next Photo Update Due --}}
                                <td class="table-secondary whitespace-nowrap">
                                    @if(!empty($participant->next_photo_update_due_at))
                                        {{ \Carbon\Carbon::parse($participant->next_photo_update_due_at)->format('Y-m-d') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Latest Sponsor --}}
                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->current_sponsored_by ?? '-' }}
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->latestSponsorship?->sponsor_contact ?? $participant->sponsor_contact ?? '-' }}
                                </td>

                                {{-- Sponsorship --}}
                                <td class="whitespace-nowrap">
                                    @php
                                        $sponsorshipStatus = $participant->current_sponsorship_status;
                                    @endphp

                                    <div class="space-y-1">
                                        @if($sponsorshipStatus)
                                            <span class="status-badge badge-active"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $sponsorshipStatus }}</span>
                                        @else
                                            <span class="table-secondary">-</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="table-secondary whitespace-nowrap">
                                    {{ $participant->latestSponsorship?->sponsorship_type ?? $participant->sponsorship_type ?? '-' }}
                                </td>

                                {{-- Actions --}}
                                <td class="compact-actions">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('participants.show', $participant->id) }}" class="btn-action">View</a>

                                        <a href="{{ route('participants.edit', $participant->id) }}" class="btn-action">Edit</a>

                                        <a href="{{ route('sponsorships.index', ['search' => $participant->local_participant_id]) }}" class="btn-action btn-action-green">Sponsorships</a>

                                        <a href="{{ route('sponsorships.create', ['participant_id' => $participant->id]) }}" class="btn-ghost text-xs">Add Sponsorship</a>

                                        <form action="{{ route('participants.destroy', $participant->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this participant?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-ghost text-xs bg-rose-50 text-rose-600 border-rose-200">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="28" class="text-center py-14">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="participants-empty-icon flex items-center justify-center text-2xl mb-4">
                                            👥
                                        </div>
                                        <h3 class="text-lg font-semibold text-slate-900">No participants found</h3>
                                        <p class="text-sm text-slate-600 mt-1">
                                            Start by adding a new participant record.
                                        </p>
                                        <a href="{{ route('participants.create') }}" class="mt-4 btn-primary"><i class="bi bi-plus-lg"></i> Add Participant</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 sm:p-5 border-t border-slate-200/80">
                {{ $participants->links() }}
            </div>
        </div>
    </div>
</div>
</x-app-layout>
