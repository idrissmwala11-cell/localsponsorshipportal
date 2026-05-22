<x-app-layout>
    @once
        <style>
            .participant-form-page .workspace-hero {
                padding: 1.2rem 1.4rem;
            }
            .participant-form-page .workspace-panel,
            .participant-form-page .workspace-subpanel {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 250, 252, 0.96));
                border: 1px solid rgba(148, 163, 184, 0.12);
                box-shadow: 0 16px 32px -32px rgba(15, 23, 42, 0.12);
            }
            .participant-form-page .workspace-subpanel h2,
            .participant-form-page .workspace-panel h1 {
                color: #0f172a;
            }
            .participant-form-page .workspace-panel p,
            .participant-form-page .workspace-subpanel p {
                color: #475569;
            }
            .participant-form-page .compact-section {
                padding: 1.15rem 1.2rem;
            }
        </style>
    @endonce
    <div class="workspace-page">
        <div class="workspace-container participant-form-page">
            <div class="workspace-panel overflow-hidden">

                <div class="workspace-hero px-6 md:px-8 py-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="workspace-label">Update Record</p>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mt-2">Edit Participant</h1>
                            <p class="text-slate-600 mt-2 text-sm md:text-base">
                                Update participant profile information and photo.
                            </p>
                        </div>

                        <a href="{{ route('participants.index') }}"
                           class="btn-ghost">
                            Back to Participants
                        </a>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if ($errors->any())
                        <div class="workspace-flash-error mb-6 p-4">
                            <h3 class="text-sm font-bold">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>&bull; {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('participants.partials.form', ['participant' => $participant])
                </div>
            </div>
        </div>
    </div>

</x-app-layout>


