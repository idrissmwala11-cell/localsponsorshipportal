<x-app-layout>
    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            @once
                <style>
                    .sponsorships-hero {
                        padding: 1.45rem 1.6rem;
                    }
                    .sponsorships-hero-title {
                        font-size: clamp(2rem, 3vw, 3rem);
                        line-height: 1;
                        color: #0f172a;
                    }
                    .sponsorship-stat {
                        min-height: 8.6rem;
                    }
                    .sponsorship-stat h3 {
                        font-size: clamp(1.7rem, 2.3vw, 2.15rem);
                        line-height: 1;
                        margin-top: 0.7rem;
                    }
                    .sponsorship-table .table-primary {
                        color: #0f172a;
                        font-weight: 700;
                    }
                    .sponsorship-table .table-secondary {
                        color: #475569;
                    }
                    .sponsorship-summary-grid {
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                        gap: 0.65rem;
                    }
                    .sponsorship-summary-card {
                        border-radius: 1rem;
                        border: 1px solid rgba(191, 219, 254, 0.95);
                        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                        box-shadow: 0 18px 36px -28px rgba(15, 23, 42, 0.12);
                        padding: 0.72rem 0.8rem;
                    }
                    .sponsorship-summary-card:hover {
                        background: rgba(239, 246, 255, 0.55);
                    }
                    .sponsorship-summary-metric {
                        color: #0f172a;
                        font-weight: 800;
                        font-size: 0.82rem;
                    }
                    .sponsorship-summary-copy {
                        color: #64748b;
                        font-size: 0.68rem;
                        margin-top: 0.1rem;
                        line-height: 1.3;
                    }
                    .sponsorship-summary-value {
                        color: #0f172a;
                        font-size: 1.05rem;
                        font-weight: 900;
                        line-height: 1;
                    }
                    .sponsorship-summary-pill {
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
                    .sponsorship-empty-icon {
                        width: 4rem;
                        height: 4rem;
                        border-radius: 999px;
                        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
                        color: #2563eb;
                        font-size: 0;
                    }
                    .sponsorship-empty-icon::before {
                        content: '\f1ca';
                        font-family: 'bootstrap-icons';
                        font-size: 1.35rem;
                    }
                    @media (max-width: 768px) {
                        .sponsorships-hero {
                            padding: 1.2rem;
                        }
                        .sponsorship-stat {
                            min-height: auto;
                        }
                        .sponsorship-summary-grid {
                            grid-template-columns: 1fr;
                        }
                    }
                    @media (max-width: 1280px) {
                        .sponsorship-summary-grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    }
                </style>
            @endonce

            <div class="workspace-hero sponsorships-hero">
                <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="workspace-label">Funding Workspace</p>
                    <h1 class="sponsorships-hero-title font-black mt-3">Sponsorship Information</h1>
                    <p class="text-slate-600 text-sm mt-3 max-w-2xl leading-7">
                        Manage all sponsorship records independently from participant profiles.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="GET" action="{{ route('sponsorships.index') }}" class="flex flex-col sm:flex-row gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search funding, sponsor, participant..."
                            class="workspace-input w-full sm:w-80 px-4 py-2.5 text-sm"
                        >

                        <button
                            type="submit"
                            class="btn-primary justify-center">
                            Search
                        </button>

                        @if(request('search'))
                            <a href="{{ route('sponsorships.index') }}"
                               class="btn-ghost text-center">
                                Reset
                            </a>
                        @endif
                    </form>

                    <a href="{{ route('sponsorships.create') }}"
                       class="btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Sponsorship
                </a>
            </div>
                </div>
            </div>

            <div class="sponsorship-summary-grid">
                <div class="sponsorship-summary-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="sponsorship-summary-metric">Total Records</div>
                            <div class="sponsorship-summary-copy">All sponsorship records found.</div>
                        </div>
                        <span class="sponsorship-summary-value">{{ $sponsorships->total() }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="sponsorship-summary-pill"><i class="bi bi-wallet2"></i> Funding</span>
                        <span class="text-slate-600 text-xs text-right">Complete sponsorship records in the current search scope.</span>
                    </div>
                </div>

                <div class="sponsorship-summary-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="sponsorship-summary-metric">Current Page</div>
                            <div class="sponsorship-summary-copy">Page currently being viewed.</div>
                        </div>
                        <span class="sponsorship-summary-value">{{ $sponsorships->currentPage() }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="sponsorship-summary-pill"><i class="bi bi-file-earmark"></i> Page View</span>
                        <span class="text-slate-600 text-xs text-right">Shows the paginated sponsorship page currently open.</span>
                    </div>
                </div>

                <div class="sponsorship-summary-card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="sponsorship-summary-metric">Showing</div>
                            <div class="sponsorship-summary-copy">Records visible on this page.</div>
                        </div>
                        <span class="sponsorship-summary-value">{{ $sponsorships->count() }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="sponsorship-summary-pill"><i class="bi bi-eye"></i> Visible</span>
                        <span class="text-slate-600 text-xs text-right">Records displayed before moving to the next page.</span>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="workspace-flash-success p-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="workspace-panel overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200/80">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="workspace-label">Records</p>
                            <h2 class="text-lg font-bold text-slate-900 mt-2">Sponsorship Records</h2>
                            <p class="text-sm text-slate-600 mt-1">
                                Sponsorship status, sponsor, dates, and category.
                            </p>
                        </div>

                        @if(request('search'))
                            <div class="text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-xl px-4 py-2 font-medium">
                                Search result for: <span class="font-semibold">{{ request('search') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full modern-table sponsorship-table">
                        <thead>
                            <tr>
                                <th class="text-left">Project Name</th>
                                <th class="text-left">Participant ID</th>
                                <th class="text-left">Sponsor Name</th>
                                <th class="text-left">Sponsorship Type</th>
                                <th class="text-left">Sponsorship Status</th>
                                <th class="text-left">Sponsor Contact</th>
                                <th class="text-left">Start Date</th>
                                <th class="text-left">Category</th>
                                <th class="text-left">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($sponsorships as $sponsorship)
                                <tr>
                                    <td class="table-primary">
                                        {{ optional($sponsorship->participant)->project_name ?? optional($sponsorship->participant)->account_name ?? '-' }}
                                    </td>

                                    <td><span class="inline-flex px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs border border-slate-200">{{ optional($sponsorship->participant)->local_participant_id ?? '-' }}</span></td>

                                    <td class="table-secondary">
                                        {{ $sponsorship->sponsor_name ?? $sponsorship->sponsored_by ?? '-' }}
                                    </td>

                                    <td class="table-secondary">
                                        {{ $sponsorship->sponsorship_type ?? '-' }}
                                    </td>

                                    <td>
                                        @php
                                            $status = $sponsorship->sponsorship_status;
                                        @endphp

                                        @if($status === 'Active')
                                            <span class="status-badge badge-active"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Active</span>
                                        @elseif($status === 'Inactive')
                                            <span class="status-badge badge-inactive"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Inactive</span>
                                        @elseif($status === 'Pending')
                                            <span class="status-badge badge-pending"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>Pending</span>
                                        @else
                                            <span class="status-badge badge-default"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $status ?? 'N/A' }}</span>
                                        @endif
                                    </td>

                                    <td class="table-secondary">
                                        {{ $sponsorship->sponsor_contact ?? '-' }}
                                    </td>

                                    <td class="table-secondary whitespace-nowrap">
                                        {{ $sponsorship->sponsorship_start_date ? $sponsorship->sponsorship_start_date->format('Y-m-d') : '-' }}
                                    </td>

                                    <td class="table-secondary">
                                        {{ $sponsorship->sponsorship_category ?? '-' }}
                                    </td>

                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            @if($sponsorship->participant)
                                                <a href="{{ route('participants.show', $sponsorship->participant->id) }}" class="btn-action">Participant</a>
                                            @endif

                                            <a href="{{ route('sponsorships.edit', $sponsorship->id) }}" class="btn-action btn-action-green">Edit</a>

                                            <form action="{{ route('sponsorships.destroy', $sponsorship->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this sponsorship record?')">
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
                                    <td colspan="9" class="text-center py-14">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center text-3xl mb-4">
                                                💳
                                            </div>
                                            <h3 class="text-lg font-semibold text-slate-900">No sponsorship records found</h3>
                                            <p class="text-sm text-slate-600 mt-1">
                                                Start by adding a new sponsorship record.
                                            </p>
                                            <a href="{{ route('sponsorships.create') }}" class="mt-4 btn-primary"><i class="bi bi-plus-lg"></i> Add Sponsorship</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 sm:p-5 border-t border-slate-200/80">
                    {{ $sponsorships->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
