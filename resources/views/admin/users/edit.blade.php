<x-app-layout>
    <x-slot name="header">Edit User</x-slot>

    <div class="workspace-page">
        <div class="workspace-narrow">
            <div class="workspace-panel overflow-hidden">
                <div class="workspace-hero px-6 md:px-8 py-6">
                    <p class="workspace-label">Admin Action</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mt-2">Edit User</h1>
                    <p class="text-slate-500 mt-2 text-sm md:text-base">Update user details and role while keeping them inside your current center.</p>
                </div>

                <div class="p-6 md:p-8">
                    @if ($errors->any())
                        <div class="workspace-flash-error mb-6 p-4">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="workspace-field-label">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $managedUser->name) }}" required class="workspace-input px-4 py-3">
                            </div>
                            <div>
                                <label class="workspace-field-label">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $managedUser->email) }}" required class="workspace-input px-4 py-3">
                            </div>
                            <div>
                                <label class="workspace-field-label">Role</label>
                                <select name="role" class="workspace-select px-4 py-3" required>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" @selected(old('role', $managedUser->role) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="workspace-field-label">Project Name</label>
                                <input type="text" name="project_name" value="{{ old('project_name', $managedUser->project_name) }}" required class="workspace-input px-4 py-3" placeholder="Example: Moravian, FPCT, TAG, EAGT">
                                <p class="mt-2 text-xs text-slate-500">Enter the project name manually. Matching logos are detected automatically in the portal.</p>
                            </div>
                            <div>
                                <label class="workspace-field-label">Primary Center</label>
                                @if($isOfficialAdmin)
                                    <select name="center_id" class="workspace-select px-4 py-3">
                                        <option value="">Select center</option>
                                        @foreach($centers as $center)
                                            <option value="{{ $center->center_id }}" @selected(old('center_id', $managedUser->center_id) === $center->center_id)>{{ $center->center_id }} - {{ $center->center_name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" value="{{ $managedUser->center_id }}" readonly class="workspace-input px-4 py-3 bg-slate-100">
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <label class="workspace-field-label">Managed Centers For Admin</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                                    @php
                                        $selectedCenters = old('managed_center_ids', $managedCenterIds);
                                    @endphp
                                    @foreach($centers as $center)
                                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <input type="checkbox" name="managed_center_ids[]" value="{{ $center->center_id }}" @checked(in_array($center->center_id, $selectedCenters, true))>
                                            <span class="text-sm text-slate-700">{{ $center->center_id }} - {{ $center->center_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-slate-500">This is used only when the selected role is `Admin`.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="btn-primary">Update User</button>
                            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
