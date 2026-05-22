<x-app-layout>
    <x-slot name="header">Admin Setup</x-slot>

    <div class="workspace-page">
        <div class="workspace-container max-w-5xl space-y-6">
            <div class="workspace-hero p-6 lg:p-8">
                <p class="workspace-label">First Login Setup</p>
                <h1 class="text-3xl lg:text-5xl font-black text-slate-900 mt-3">Choose Your Managed Cluster</h1>
                <p class="text-slate-500 text-sm mt-3 max-w-3xl">
                    Select the cluster or clusters under your supervision. Every user registered in those clusters will automatically appear under your management dashboard.
                </p>
            </div>

            @if(session('error'))
                <div class="workspace-flash-error p-4 text-sm">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.setup.store') }}" class="workspace-panel p-6 space-y-6">
                @csrf

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <label class="workspace-field-label">Clusters Under Your Supervision</label>
                            <p class="mt-1 text-xs text-slate-500">Select at least one cluster. All users in its centers will be managed automatically.</p>
                        </div>
                        <div class="text-xs font-semibold text-slate-500">
                            {{ $clusterSummaries->count() }} available
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse($clusterSummaries as $cluster)
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-blue-200 hover:bg-blue-50/40">
                                <input
                                    type="checkbox"
                                    name="managed_cluster_names[]"
                                    value="{{ $cluster['cluster_name'] }}"
                                    class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    @checked(collect(old('managed_cluster_names', $selectedClusters))->contains($cluster['cluster_name']))>
                                <span class="block">
                                    <strong class="block text-sm text-slate-900">{{ $cluster['cluster_name'] }}</strong>
                                    <small class="mt-1 block text-xs leading-6 text-slate-500">
                                        {{ $cluster['centers_count'] }} {{ $cluster['centers_count'] === 1 ? 'center' : 'centers' }}<br>
                                        {{ $cluster['users_count'] }} {{ $cluster['users_count'] === 1 ? 'user' : 'users' }} currently registered
                                    </small>
                                </span>
                            </label>
                        @empty
                            <div class="md:col-span-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                No clusters are available yet. Ask users to register with their cluster names first.
                            </div>
                        @endforelse
                    </div>

                    <x-input-error :messages="$errors->get('managed_cluster_names')" class="mt-2" />
                    <x-input-error :messages="$errors->get('managed_cluster_names.*')" class="mt-2" />
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">Save Admin Setup</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
