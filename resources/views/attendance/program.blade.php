<x-app-layout>
    <x-slot name="header">{{ $pageTitle ?? 'Program Attendance' }}</x-slot>

    @php
        $participantTotal = $participants->count();
    @endphp

    @once
        <style>
            .attendance-shell {
                max-width: 86rem;
                margin: 0 auto;
            }
            .attendance-card {
                border-radius: 1.8rem;
                border: 1px solid rgba(191, 219, 254, 0.9);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                box-shadow: 0 28px 70px -46px rgba(15, 23, 42, 0.2);
                overflow: hidden;
            }
            .attendance-hero {
                padding: 1.6rem 1.7rem 1.3rem;
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
                background: radial-gradient(circle at top left, rgba(96, 165, 250, 0.14), transparent 18rem), linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(243, 248, 255, 0.95));
            }
            .attendance-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                margin-top: 1rem;
            }
            .attendance-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.58rem 0.92rem;
                border-radius: 999px;
                border: 1px solid rgba(191, 219, 254, 0.9);
                background: linear-gradient(180deg, #ffffff, #f8fbff);
                color: #334155;
                font-size: 0.8rem;
                font-weight: 700;
            }
            .attendance-body {
                padding: 1.5rem;
            }
            .attendance-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
                gap: 1rem;
            }
            .attendance-panel {
                border-radius: 1.3rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: rgba(255, 255, 255, 0.88);
                padding: 1rem;
            }
            .attendance-summary {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
            }
            .attendance-summary-card {
                border-radius: 1rem;
                border: 1px solid rgba(191, 219, 254, 0.85);
                background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(239,246,255,0.9));
                padding: 0.9rem;
            }
            .attendance-summary-label {
                color: #64748b;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .attendance-summary-value {
                color: #0f172a;
                font-size: 1.5rem;
                font-weight: 900;
                margin-top: 0.35rem;
            }
            .attendance-table-wrap {
                overflow-x: auto;
                border-radius: 1rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: white;
            }
            .attendance-check {
                width: 1.15rem;
                height: 1.15rem;
                accent-color: #2563eb;
            }
            .attendance-status-present {
                color: #15803d;
                font-weight: 700;
            }
            .attendance-status-absent {
                color: #b91c1c;
                font-weight: 700;
            }
            .attendance-gallery {
                display: grid;
                gap: 1rem;
            }
            .attendance-gallery-card {
                overflow: hidden;
                border-radius: 1.35rem;
                border: 1px solid rgba(191, 219, 254, 0.88);
                background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(248,250,252,0.96));
                box-shadow: 0 22px 38px -30px rgba(15, 23, 42, 0.18);
                padding: 1rem;
            }
            .attendance-gallery-meta {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 0.85rem;
            }
            .attendance-gallery-shell {
                border-radius: 1.2rem;
                border: 1px solid rgba(191, 219, 254, 0.9);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(241, 245, 249, 0.94));
                box-shadow: 0 20px 34px -28px rgba(15, 23, 42, 0.16);
                padding: 0.85rem;
                overflow: hidden;
            }
            .attendance-gallery-stage {
                overflow: hidden;
            }
            .attendance-gallery-track {
                display: flex;
                gap: 1rem;
                transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
                will-change: transform;
            }
            .attendance-gallery-tile {
                position: relative;
                overflow: hidden;
                border-radius: 1rem;
                border: 1px solid rgba(191, 219, 254, 0.85);
                background: #ffffff;
                min-height: 20rem;
                box-shadow: 0 18px 32px -28px rgba(15, 23, 42, 0.16);
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 calc(50% - 0.5rem);
            }
            .attendance-gallery-tile img {
                display: block;
                width: 100%;
                height: 20rem;
                object-fit: contain;
                background: #ffffff;
            }
            .attendance-gallery-overlay {
                position: absolute;
                inset: auto 0 0 0;
                padding: 1rem 1.1rem 0.95rem;
                background: linear-gradient(180deg, transparent, rgba(15, 23, 42, 0.76));
                color: #ffffff;
            }
            .attendance-gallery-counter {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.28rem 0.62rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.16);
                border: 1px solid rgba(255, 255, 255, 0.2);
                font-size: 0.68rem;
                font-weight: 700;
            }
            .attendance-photo-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
                gap: 0.85rem;
                align-items: end;
            }
            @media (max-width: 1024px) {
                .attendance-grid {
                    grid-template-columns: 1fr;
                }
            }
            @media (max-width: 768px) {
                .attendance-body, .attendance-hero {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                .attendance-summary {
                    grid-template-columns: 1fr;
                }
                .attendance-photo-row {
                    grid-template-columns: 1fr;
                }
                .attendance-gallery-meta {
                    flex-direction: column;
                }
                .attendance-gallery-tile,
                .attendance-gallery-tile img {
                    height: 16rem;
                    min-height: 16rem;
                }
                .attendance-gallery-tile {
                    flex-basis: 100%;
                }
            }
        </style>
    @endonce

    <div class="workspace-page">
        <div class="workspace-container">
            <div class="attendance-shell">
                @if(session('success'))
                    <div class="workspace-flash-success p-4 text-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="workspace-flash-error p-4 text-sm mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="workspace-flash-error p-4 text-sm mb-4">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>&bull; {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="attendance-card"
                     x-data="{
                        presentCount: 0,
                        total: {{ $participantTotal }},
                        updateCounts() {
                            this.presentCount = this.$root.querySelectorAll('input[name=\'present_participants[]\']:checked').length;
                        }
                     }"
                     x-init="$nextTick(() => updateCounts())">
                    <div class="attendance-hero">
                        <p class="workspace-label">Attendance Workspace</p>
                        <h1 class="text-3xl font-black text-slate-900 mt-3">{{ $pageTitle ?? 'Program Attendance' }}</h1>
                        <p class="mt-3 text-sm text-slate-600 max-w-3xl">
                            {{ $pageDescription ?? 'Mark each registered participant as present or absent, then save the program attendance together with the instructor, topic, and comment.' }}
                        </p>

                        <div class="attendance-badges">
                            <span class="attendance-pill">Registered Participants: <strong>{{ $participantTotal }}</strong></span>
                            <span class="attendance-pill">Attendance Date: <strong>{{ now()->format('d M Y') }}</strong></span>
                            <span class="attendance-pill">Type: <strong>{{ $attendanceTypeLabel ?? 'Program' }}</strong></span>
                            <span class="attendance-pill">Columns: <strong>5</strong></span>
                        </div>
                    </div>

                    <div class="attendance-body">
                        <form method="POST" action="{{ route($submitRoute ?? 'attendance.program.store') }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            <div class="attendance-grid">
                                <div class="attendance-panel space-y-4">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="workspace-field-label">Attendance Date</label>
                                            <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->format('Y-m-d')) }}" class="workspace-input px-4 py-3">
                                        </div>
                                        <div>
                                            <label class="workspace-field-label">Instructor Name</label>
                                            <input type="text" name="instructor_name" value="{{ old('instructor_name') }}" class="workspace-input px-4 py-3" placeholder="Enter instructor name">
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="workspace-field-label">Topic</label>
                                            <input type="text" name="topic" value="{{ old('topic') }}" class="workspace-input px-4 py-3" placeholder="Enter topic taught">
                                        </div>
                                        <div>
                                            <label class="workspace-field-label">Comment</label>
                                            <input type="text" name="comment" value="{{ old('comment') }}" class="workspace-input px-4 py-3" placeholder="Enter comment">
                                        </div>
                                    </div>

                                    @if(($attendanceType ?? 'program') === 'activity')
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="workspace-field-label">Aina Ya Activity</label>
                                                <input type="text" name="activity_name" value="{{ old('activity_name') }}" class="workspace-input px-4 py-3" placeholder="Enter activity type" required>
                                            </div>
                                            <div class="text-sm text-slate-500 flex items-end">
                                                Upload more than one photo and write a caption for each image.
                                            </div>
                                        </div>

                                        @php
                                            $oldActivityCaptions = old('activity_photo_captions', ['']);
                                            if (empty($oldActivityCaptions)) {
                                                $oldActivityCaptions = [''];
                                            }
                                        @endphp

                                        <div>
                                            <div class="flex items-center justify-between gap-3 mb-3">
                                                <label class="workspace-field-label !mb-0">Activity Photos</label>
                                                <button type="button" id="addActivityPhotoRow" class="btn-ghost px-3 py-2 text-xs">Add Another Photo</button>
                                            </div>
                                            <div id="activityPhotoRows" class="space-y-3">
                                                @foreach($oldActivityCaptions as $index => $caption)
                                                    <div class="attendance-photo-row">
                                                        <div>
                                                            <label class="workspace-field-label">Photo {{ $index + 1 }}</label>
                                                            <input type="file" name="activity_photos[]" accept=".jpg,.jpeg,.png,.webp,image/*" class="workspace-input px-4 py-3">
                                                        </div>
                                                        <div>
                                                            <label class="workspace-field-label">Caption</label>
                                                            <input type="text" name="activity_photo_captions[]" value="{{ $caption }}" class="workspace-input px-4 py-3" placeholder="Write caption for this image">
                                                        </div>
                                                        <button type="button" class="btn-ghost remove-activity-photo-row px-3 py-3 text-xs">Remove</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="attendance-panel">
                                    <div class="attendance-summary">
                                        <div class="attendance-summary-card">
                                            <div class="attendance-summary-label">Total Participants</div>
                                            <div class="attendance-summary-value" x-text="total"></div>
                                        </div>
                                        <div class="attendance-summary-card">
                                            <div class="attendance-summary-label">Present</div>
                                            <div class="attendance-summary-value text-emerald-600" x-text="presentCount"></div>
                                        </div>
                                        <div class="attendance-summary-card">
                                            <div class="attendance-summary-label">Absent</div>
                                            <div class="attendance-summary-value text-rose-600" x-text="Math.max(total - presentCount, 0)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="attendance-table-wrap">
                                <table class="w-full modern-table">
                                    <thead>
                                        <tr>
                                            <th class="text-left">#</th>
                                            <th class="text-left">Participant ID</th>
                                            <th class="text-left">Project Name</th>
                                            <th class="text-left">Full Name</th>
                                            <th class="text-left">Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($participants as $index => $participant)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="font-mono text-xs">{{ $participant->local_participant_id ?? '-' }}</td>
                                                <td class="table-primary">{{ $participant->project_name ?? '-' }}</td>
                                                <td>{{ $participant->preferred_name ?? '-' }}</td>
                                                <td>
                                                    <label class="inline-flex items-center gap-3">
                                                        <input
                                                            type="checkbox"
                                                            class="attendance-check"
                                                            name="present_participants[]"
                                                            value="{{ $participant->id }}"
                                                            @checked(collect(old('present_participants', []))->contains((string) $participant->id) || collect(old('present_participants', []))->contains($participant->id))
                                                            @change="updateCounts()">
                                                        <span :class="$el.previousElementSibling.checked ? 'attendance-status-present' : 'attendance-status-absent'">
                                                            <span x-text="$el.previousElementSibling.checked ? 'Present' : 'Absent'"></span>
                                                        </span>
                                                    </label>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-12 text-sm text-slate-500">No participants found for your current scope.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="btn-primary">{{ $submitLabel ?? 'Save Program Attendance' }}</button>
                                <a href="{{ route('dashboard') }}" class="btn-ghost">Back to Dashboard</a>
                            </div>
                        </form>

                        @if($recentSessions->isNotEmpty())
                            <div class="attendance-panel mt-5">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div>
                                        <p class="workspace-label">Recent Records</p>
                                        <h2 class="text-lg font-bold text-slate-900 mt-1">{{ $recentTitle ?? 'Saved Program Attendance' }}</h2>
                                    </div>
                                </div>

                                <div class="attendance-table-wrap">
                                    <table class="w-full modern-table">
                                        <thead>
                                            <tr>
                                                @if(($attendanceType ?? 'program') === 'activity')
                                                    <th class="text-left">Activity</th>
                                                @endif
                                                <th class="text-left">Type</th>
                                                <th class="text-left">Date</th>
                                                <th class="text-left">Instructor</th>
                                                <th class="text-left">Topic</th>
                                                <th class="text-left">Present</th>
                                                <th class="text-left">Absent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentSessions as $session)
                                                <tr>
                                                    @if(($attendanceType ?? 'program') === 'activity')
                                                        <td>{{ $session->activity_name ?: '-' }}</td>
                                                    @endif
                                                    <td>{{ ucfirst($session->attendance_type ?? ($attendanceType ?? 'program')) }}</td>
                                                    <td>{{ $session->attendance_date?->format('d M Y') ?? '-' }}</td>
                                                    <td>{{ $session->instructor_name ?: '-' }}</td>
                                                    <td>{{ $session->topic ?: '-' }}</td>
                                                    <td class="text-emerald-600 font-semibold">{{ $session->present_count }}</td>
                                                    <td class="text-rose-600 font-semibold">{{ $session->absent_count }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(($attendanceType ?? 'program') === 'activity')
                            @php
                                $activityPhotoSessions = $recentSessions->filter(fn ($session) => !empty($session->activity_photo_gallery));
                            @endphp

                            @if($activityPhotoSessions->isNotEmpty())
                                <div class="attendance-panel mt-5">
                                    <div class="mb-4">
                                        <p class="workspace-label">Activity Photos</p>
                                        <h2 class="text-lg font-bold text-slate-900 mt-1">Uploaded Activity Images</h2>
                                    </div>

                                    <div class="attendance-gallery">
                                        @foreach($activityPhotoSessions as $session)
                                            @php
                                                $activityPhotos = $session->activity_photo_gallery;
                                            @endphp
                                            <div class="attendance-gallery-card">
                                                <div class="attendance-gallery-meta">
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-900">{{ $session->activity_name ?: 'Activity' }}</p>
                                                        <p class="mt-1 text-xs text-slate-500">{{ $session->attendance_date?->format('d M Y') ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-xs font-semibold text-slate-500">
                                                        {{ count($activityPhotos) }} {{ count($activityPhotos) === 1 ? 'photo' : 'photos' }}
                                                    </div>
                                                </div>

                                                <div class="attendance-gallery-shell"
                                                     x-data="{
                                                        photos: {{ \Illuminate\Support\Js::from($activityPhotos) }},
                                                        activityName: @js($session->activity_name ?: 'Activity'),
                                                        current: 0,
                                                        visibleCount: window.innerWidth <= 900 ? 1 : 2,
                                                        timer: null,
                                                        totalSlides() {
                                                            return Math.max(this.photos.length - this.visibleCount + 1, 1);
                                                        },
                                                        slideWidth() {
                                                            return this.visibleCount === 1 ? 100 : 50;
                                                        },
                                                        updateViewport() {
                                                            this.visibleCount = window.innerWidth <= 900 ? 1 : 2;
                                                            if (this.current >= this.totalSlides()) {
                                                                this.current = 0;
                                                            }
                                                        },
                                                        start() {
                                                            if (this.photos.length <= this.visibleCount) return;
                                                            this.stop();
                                                            this.timer = setInterval(() => this.next(), 5000);
                                                        },
                                                        stop() {
                                                            if (this.timer) clearInterval(this.timer);
                                                        },
                                                        next() {
                                                            this.current = (this.current + 1) % this.totalSlides();
                                                        }
                                                     }"
                                                     x-init="updateViewport(); start(); window.addEventListener('resize', () => updateViewport())"
                                                     @mouseenter="stop()"
                                                     @mouseleave="start()">
                                                    <div class="attendance-gallery-stage">
                                                        <div class="attendance-gallery-track"
                                                             :style="`transform: translateX(-${current * (slideWidth())}%);`">
                                                            @foreach($activityPhotos as $index => $photo)
                                                                <div class="attendance-gallery-tile">
                                                                    <img src="{{ $photo['url'] }}" alt="{{ $session->activity_name ?: 'Activity photo' }} {{ $index + 1 }}">
                                                                    <div class="attendance-gallery-overlay">
                                                                        <div class="flex items-center justify-between gap-3">
                                                                            <div>
                                                                                <p class="text-sm font-semibold">{{ $session->activity_name ?: 'Activity Photo Gallery' }}</p>
                                                                                <p class="text-xs text-white/80">{{ $photo['caption'] ?: 'No caption provided.' }}</p>
                                                                            </div>
                                                                            <span class="attendance-gallery-counter">
                                                                                {{ $index + 1 }} / {{ count($activityPhotos) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($attendanceType ?? 'program') === 'activity')
        <script>
            const activityPhotoRows = document.getElementById('activityPhotoRows');
            const addActivityPhotoRow = document.getElementById('addActivityPhotoRow');

            function bindActivityPhotoRowActions() {
                activityPhotoRows?.querySelectorAll('.remove-activity-photo-row').forEach((button) => {
                    button.onclick = function () {
                        const rows = activityPhotoRows.querySelectorAll('.attendance-photo-row');

                        if (rows.length <= 1) {
                            rows[0].querySelectorAll('input').forEach((input) => input.value = '');
                            return;
                        }

                        this.closest('.attendance-photo-row')?.remove();
                    };
                });
            }

            addActivityPhotoRow?.addEventListener('click', function () {
                if (!activityPhotoRows) {
                    return;
                }

                const rowCount = activityPhotoRows.querySelectorAll('.attendance-photo-row').length;

                activityPhotoRows.insertAdjacentHTML('beforeend', `
                    <div class="attendance-photo-row">
                        <div>
                            <label class="workspace-field-label">Photo ${rowCount + 1}</label>
                            <input type="file" name="activity_photos[]" accept=".jpg,.jpeg,.png,.webp,image/*" class="workspace-input px-4 py-3">
                        </div>
                        <div>
                            <label class="workspace-field-label">Caption</label>
                            <input type="text" name="activity_photo_captions[]" class="workspace-input px-4 py-3" placeholder="Write caption for this image">
                        </div>
                        <button type="button" class="btn-ghost remove-activity-photo-row px-3 py-3 text-xs">Remove</button>
                    </div>
                `);

                bindActivityPhotoRowActions();
            });

            bindActivityPhotoRowActions();
        </script>
    @endif

</x-app-layout>
