<x-app-layout>
    <x-slot name="header">Treatment Records</x-slot>

    <div class="workspace-page">
        <div class="workspace-container">
            <div class="max-w-6xl mx-auto space-y-5">
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
                @if($errors->any())
                    <div class="workspace-flash-error p-4 text-sm">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>&bull; {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="workspace-panel p-5 lg:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                        <div>
                            <p class="workspace-label">Medical Workspace</p>
                            <h1 class="mt-2 text-3xl font-black text-slate-900">Treatment Records</h1>
                            <p class="mt-2 text-sm text-slate-600 max-w-3xl">
                                Record treatment details separately from the participant profile and keep a clean history of medical support given to each participant.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 min-w-[220px]">
                            <div class="workspace-stat">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Participants</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $participants->count() }}</p>
                            </div>
                            <div class="workspace-stat">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Treatment Records</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $treatments->total() }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('treatments.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf

                        <div>
                            <label class="workspace-field-label">Participant</label>
                            <select name="participant_id" class="workspace-select px-4 py-3" required>
                                <option value="">Select Participant</option>
                                @foreach($participants as $participant)
                                    <option value="{{ $participant->id }}" @selected(old('participant_id') == $participant->id)>
                                        {{ $participant->account_name }}{{ $participant->preferred_name ? ' - ' . $participant->preferred_name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="workspace-field-label">Treatment Date</label>
                            <input type="date" name="treatment_date" value="{{ old('treatment_date', now()->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
                        </div>

                        <div>
                            <label class="workspace-field-label">Illness Type</label>
                            <select name="illness_type" class="workspace-select px-4 py-3">
                                <option value="">Select Type</option>
                                <option value="Illness" @selected(old('illness_type') === 'Illness')>Illness</option>
                                <option value="Injured" @selected(old('illness_type') === 'Injured')>Injured</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="workspace-field-label">Treatment</label>
                            <textarea name="treatment" rows="3" class="workspace-textarea px-4 py-3">{{ old('treatment') }}</textarea>
                        </div>

                        <div>
                            <label class="workspace-field-label">Diseases Tested</label>
                            <textarea name="tested_diseases" rows="3" class="workspace-textarea px-4 py-3">{{ old('tested_diseases') }}</textarea>
                        </div>

                        <div>
                            <label class="workspace-field-label">Treatment Location</label>
                            <input type="text" name="treatment_location" value="{{ old('treatment_location') }}" class="workspace-input px-4 py-3">
                        </div>

                        <div>
                            <label class="workspace-field-label">Treatment Cost</label>
                            <input type="number" step="0.01" min="0" name="treatment_cost" value="{{ old('treatment_cost') }}" class="workspace-input px-4 py-3">
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <label class="workspace-field-label">Health Comments</label>
                            <textarea name="health_comments" rows="3" class="workspace-textarea px-4 py-3">{{ old('health_comments') }}</textarea>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3 pt-1">
                            <button type="submit" class="btn-primary">Save Treatment Record</button>
                            <a href="{{ route('dashboard') }}" class="btn-ghost">Back to Dashboard</a>
                        </div>
                    </form>
                </div>

                <div class="workspace-panel p-5 lg:p-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <p class="workspace-label">Medical History</p>
                            <h2 class="mt-2 text-2xl font-black text-slate-900">Saved Treatment Records</h2>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                        <table class="w-full modern-table">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th class="text-left">Participant</th>
                                    <th class="text-left">Date</th>
                                    <th class="text-left">Illness / Injury</th>
                                    <th class="text-left">Treatment</th>
                                    <th class="text-left">Location</th>
                                    <th class="text-left">Cost</th>
                                    <th class="text-left">Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($treatments as $index => $treatment)
                                    <tr>
                                        <td>{{ $treatments->firstItem() + $index }}</td>
                                        <td>
                                            <div class="font-semibold text-slate-900">{{ $treatment->participant?->account_name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-500">{{ $treatment->participant?->preferred_name ?: ($treatment->participant?->local_participant_id ?? '') }}</div>
                                        </td>
                                        <td>{{ $treatment->treatment_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>{{ $treatment->illness_type ?: 'N/A' }}</td>
                                        <td>{{ $treatment->treatment ?: 'N/A' }}</td>
                                        <td>{{ $treatment->treatment_location ?: 'N/A' }}</td>
                                        <td>{{ $treatment->treatment_cost !== null ? number_format((float) $treatment->treatment_cost, 2) : 'N/A' }}</td>
                                        <td>{{ $treatment->health_comments ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-12 text-sm text-slate-500">No treatment records saved yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $treatments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
