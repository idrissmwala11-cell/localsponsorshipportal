<x-app-layout>
    <x-slot name="header">{{ ($isAdminMessenger ?? false) ? 'Chat With User' : 'Chat With Admin' }}</x-slot>

    <div class="workspace-page">
        <div class="workspace-container space-y-6">
            @once
                <style>
                    .notifications-hero {
                        padding: 1.35rem 1.5rem;
                    }
                    .notifications-title {
                        font-size: clamp(2rem, 3vw, 3rem);
                        line-height: 1;
                    }
                    .notification-card {
                        padding: 1rem;
                        border-radius: 1.05rem;
                    }
                    .notification-meta-box {
                        border-radius: 1rem;
                        padding: 0.8rem;
                    }
                </style>
            @endonce

            <div class="workspace-hero notifications-hero">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="workspace-label">Chat Workflow</p>
                        <h1 class="notifications-title font-black text-slate-900 mt-3">
                            {{ ($isAdminMessenger ?? false) ? 'Chat With User' : 'Chat With Admin' }}
                        </h1>
                        <p class="text-slate-600 text-sm mt-3 max-w-2xl leading-7">
                            @if($isAdminMessenger ?? false)
                                Send messages to one user or all users you supervise, and review replies sent back to you.
                            @else
                                Messages from your admins appear here, and you can reply directly to your admins only.
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn-primary"><i class="bi bi-check2-all"></i> Mark All Read</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="workspace-flash-success p-4 text-sm">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
            @endif

            @if($canSendNotifications ?? false)
                <div class="workspace-panel p-6">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-5">
                        <div>
                            <p class="workspace-label">{{ ($isAdminMessenger ?? false) ? 'Admin Chat' : 'User Chat' }}</p>
                            <h2 class="text-lg font-bold text-slate-900 mt-2">
                                {{ ($isAdminMessenger ?? false) ? 'Send Message to User or All Users' : 'Send Message to Admin' }}
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">
                                @if($isAdminMessenger ?? false)
                                    Send a message to one user or broadcast to all users you supervise.
                                @else
                                    Send a message directly to your supervising admin.
                                @endif
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('notifications.send') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4"
                          x-data="{ targetMode: '{{ old('target_mode', 'single_user') }}' }">
                        @csrf

                        @if($isAdminMessenger ?? false)
                            <div>
                                <label class="workspace-field-label">Send To</label>
                                <select name="target_mode" class="workspace-select px-4 py-3" x-model="targetMode" required>
                                    <option value="single_user">Single User</option>
                                    <option value="all_managed_users">All Managed Users</option>
                                </select>
                            </div>

                            <div x-show="targetMode === 'single_user'">
                                <label class="workspace-field-label">Select User</label>
                                <select name="target_user_id" class="workspace-select px-4 py-3" :required="targetMode === 'single_user'">
                                    <option value="">Select User</option>
                                    @foreach(($managedUsers ?? []) as $managedUser)
                                        <option value="{{ $managedUser->id }}" @selected((string) old('target_user_id') === (string) $managedUser->id)>
                                            {{ $managedUser->name }} | {{ $managedUser->center_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="workspace-field-label">Send To Admin / System Administrator</label>
                                <select name="target_user_id" class="workspace-select px-4 py-3" required>
                                    <option value="">Select Admin or System Administrator</option>
                                    @foreach(($adminRecipients ?? []) as $adminRecipient)
                                        <option value="{{ $adminRecipient->id }}" @selected((string) old('target_user_id') === (string) $adminRecipient->id)>
                                            {{ $adminRecipient->name }} | {{ $adminRecipient->display_title }}{{ $adminRecipient->center_id ? ' | ' . $adminRecipient->center_id : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(($adminRecipients ?? collect())->isEmpty())
                                    <p class="mt-2 text-xs text-amber-600">
                                        No admin recipients are linked to your account yet. Please contact the system administrator.
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="workspace-field-label">Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="workspace-input px-4 py-3" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="workspace-field-label">Message</label>
                            <textarea name="message" rows="4" class="workspace-textarea px-4 py-3" required>{{ old('message') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @forelse($notifications as $notification)
                    @php
                        $isRead = $notification->reads->isNotEmpty();
                    @endphp
                    <div class="workspace-panel notification-card">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] {{ $isRead ? 'bg-slate-100 text-slate-500' : 'bg-blue-50 text-blue-600' }}">
                                {{ str_replace('_', ' ', $notification->type) }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                        </div>

                        <h2 class="mt-3 text-base font-bold text-slate-900">{{ $notification->title }}</h2>
                        <p class="mt-1.5 text-sm text-slate-600 leading-6">{{ $notification->message }}</p>

                        <div class="mt-3 grid grid-cols-2 gap-2.5 text-sm">
                            <div class="notification-meta-box bg-slate-50">
                                <p class="workspace-label">Sent By</p>
                                <p class="mt-1.5 text-slate-700">{{ $notification->sender?->name ?? ($notification->meta['sender_name'] ?? 'Admin') }}</p>
                            </div>
                            <div class="notification-meta-box bg-slate-50">
                                <p class="workspace-label">Recipient</p>
                                <p class="mt-1.5 text-slate-700">
                                    @if($notification->sent_to_all_users)
                                        All Managed Users
                                    @else
                                        {{ $notification->recipient?->name ?? ($notification->meta['recipient_name'] ?? ($notification->center_id ?? 'N/A')) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="mt-2 text-xs text-slate-500">
                            Center: {{ $notification->center_id ?? 'N/A' }}
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="text-xs {{ $isRead ? 'text-slate-400' : 'text-blue-600 font-semibold' }}">
                                {{ $isRead ? 'Read' : 'Unread' }}
                            </span>
                            @unless($isRead)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn-ghost">Mark Read</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="workspace-panel p-6 xl:col-span-3 text-center text-slate-500">
                        No chat messages available for your center yet.
                    </div>
                @endforelse
            </div>

            <div class="workspace-panel p-4">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
