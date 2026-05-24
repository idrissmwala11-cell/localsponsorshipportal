<x-app-layout>
    <x-slot name="header">{{ $dashboardTitle ?? 'Dashboard' }}</x-slot>

    @php
        $participantTotal = max((int) ($totalParticipants ?? 0), 0);
        $activeTotal = max((int) ($activeParticipants ?? 0), 0);
        $sponsorshipTotal = max((int) ($totalSponsorships ?? 0), 0);
        $activeSponsorshipTotal = max((int) ($activeSponsorships ?? 0), 0);
        $overdueTotal = max((int) ($overdue ?? 0), 0);
        $plannedExitTotal = max((int) ($plannedExits ?? 0), 0);
        $comingDueTotal = max((int) ($comingDue ?? 0), 0);
        $exitTotal = max((int) ($exits ?? 0), 0);

        $participantActiveRate = $participantTotal > 0 ? min(100, round(($activeTotal / $participantTotal) * 100)) : 0;
        $sponsorshipActiveRate = $sponsorshipTotal > 0 ? min(100, round(($activeSponsorshipTotal / $sponsorshipTotal) * 100)) : 0;
        $photoRiskRate = $participantTotal > 0 ? min(100, round(($overdueTotal / $participantTotal) * 100)) : 0;
        $plannedExitRate = $participantTotal > 0 ? min(100, round(($plannedExitTotal / $participantTotal) * 100)) : 0;
        $exitRate = $participantTotal > 0 ? min(100, round(($exitTotal / $participantTotal) * 100)) : 0;
        $comingDueRate = $participantTotal > 0 ? min(100, round(($comingDueTotal / $participantTotal) * 100)) : 0;
        $seriousConditionsTotal = max((int) ($seriousConditions ?? 0), 0);
        $seriousConditionsRate = $participantTotal > 0 ? min(100, round(($seriousConditionsTotal / $participantTotal) * 100)) : 0;
    @endphp

    @once
        <style>
            .dashboard-tag {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.34rem 0.68rem;
                border-radius: 999px;
                background: linear-gradient(135deg, #eff6ff, #dbeafe);
                border: 1px solid #bfdbfe;
                color: #1d4ed8;
                font-size: 0.63rem;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                box-shadow: 0 10px 24px -20px rgba(59, 130, 246, 0.28);
            }
            .dashboard-tag::before {
                content: '';
                width: 0.42rem;
                height: 0.42rem;
                border-radius: 999px;
                background: #3b82f6;
            }
            .dashboard-shell { position: relative; }
            .dashboard-shell::before {
                content: '';
                position: absolute;
                inset: 0;
                pointer-events: none;
                background:
                    radial-gradient(circle at top left, rgba(96, 165, 250, 0.12), transparent 28rem),
                    radial-gradient(circle at top right, rgba(191, 219, 254, 0.3), transparent 22rem),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.65), transparent 22rem);
            }
            .dashboard-hero {
                position: relative;
                overflow: hidden;
                background:
                    linear-gradient(135deg, #ffffff, #f4f8ff 52%, #eef5ff),
                    radial-gradient(circle at top left, rgba(96, 165, 250, 0.14), transparent 20rem);
                border: 1px solid rgba(191, 219, 254, 0.9);
                box-shadow: 0 20px 44px -34px rgba(59, 130, 246, 0.16);
            }
            .dashboard-hero::after {
                content: '';
                position: absolute;
                inset: auto -4rem -7rem auto;
                width: 18rem;
                height: 18rem;
                border-radius: 999px;
                background: radial-gradient(circle, rgba(147, 197, 253, 0.42), transparent 65%);
                filter: blur(20px);
                pointer-events: none;
            }
            .hero-grid-pattern {
                position: absolute;
                inset: 0;
                opacity: 0.3;
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
                background-size: 36px 36px;
                mask-image: linear-gradient(180deg, rgba(255, 255, 255, 0.7), transparent 88%);
                pointer-events: none;
            }
            .metric-card {
                position: relative;
                overflow: hidden;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(246, 250, 255, 0.98)),
                    radial-gradient(circle at top right, rgba(191, 219, 254, 0.18), transparent 40%);
                border: 1px solid rgba(148, 163, 184, 0.18);
                box-shadow: 0 14px 28px -26px rgba(15, 23, 42, 0.14);
                transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.3s ease;
            }
            .metric-card::before { content: ''; position: absolute; inset: 0 0 auto; height: 3px; opacity: 0.95; }
            .metric-card::after {
                content: '';
                position: absolute;
                inset: auto -2.5rem -3rem auto;
                width: 9rem;
                height: 9rem;
                border-radius: 999px;
                background: radial-gradient(circle, rgba(96, 165, 250, 0.14), transparent 68%);
                filter: blur(10px);
                pointer-events: none;
            }
            .metric-card:hover {
                transform: translateY(-6px);
                border-color: rgba(96, 165, 250, 0.22);
                box-shadow: 0 28px 40px -28px rgba(37, 99, 235, 0.22);
            }
            .metric-cyan::before { background: linear-gradient(90deg, #e0f2fe, #60a5fa); }
            .metric-emerald::before { background: linear-gradient(90deg, #bfdbfe, #3b82f6); }
            .metric-indigo::before { background: linear-gradient(90deg, #93c5fd, #2563eb); }
            .metric-rose::before { background: linear-gradient(90deg, #dbeafe, #60a5fa); }
            .metric-amber::before { background: linear-gradient(90deg, #eff6ff, #3b82f6); }
            .progress-track { height: 0.45rem; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
            .progress-fill { height: 100%; border-radius: inherit; }
            .section-panel { background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96)); border: 1px solid rgba(148, 163, 184, 0.12); box-shadow: 0 24px 60px -40px rgba(15, 23, 42, 0.12); }
            .metric-primary {
                min-height: 7.15rem;
                display: flex;
                flex-direction: column;
            }
            .metric-compact {
                min-height: 5.8rem;
                display: flex;
                flex-direction: column;
            }
            .metric-copy {
                margin-top: auto;
            }
            .metric-icon {
                color: #2563eb;
                background: linear-gradient(135deg, rgba(219, 234, 254, 0.95), rgba(239, 246, 255, 0.98));
                border: 1px solid rgba(147, 197, 253, 0.45);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
            }
            .metric-kicker {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                width: fit-content;
                margin-top: 0.65rem;
                padding: 0.28rem 0.55rem;
                border-radius: 999px;
                background: rgba(239, 246, 255, 0.92);
                border: 1px solid rgba(191, 219, 254, 0.9);
                color: #2563eb;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
            .metric-label {
                color: #64748b;
                font-size: 0.64rem;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
            }
            .metric-value {
                color: #0f172a;
                font-size: clamp(1.25rem, 1.6vw, 1.65rem);
                font-weight: 900;
                line-height: 1;
                margin-top: 0.45rem;
            }
            .metric-title {
                color: #0f172a;
                font-size: 0.94rem;
                font-weight: 900;
                line-height: 1.1;
                margin-top: 0.45rem;
            }
            .metric-description {
                color: #64748b;
                font-size: 0.72rem;
                line-height: 1.32;
                margin-top: 0.22rem;
            }
            .signal-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .action-tile {
                position: relative;
                display: flex;
                align-items: center;
                gap: 0.85rem;
                width: 100%;
                min-width: 0;
                padding: 0.82rem 0.92rem;
                border-radius: 1.15rem;
                border: 1px solid rgba(148, 163, 184, 0.16);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                box-shadow: 0 16px 34px -28px rgba(15, 23, 42, 0.16);
                transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.25s ease;
            }
            .action-tile:hover {
                transform: translateY(-4px);
                border-color: rgba(96, 165, 250, 0.22);
                box-shadow: 0 26px 42px -30px rgba(37, 99, 235, 0.22);
            }
            .action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.55rem;
                height: 2.55rem;
                border-radius: 0.9rem;
                background: linear-gradient(135deg, #eff6ff, #dbeafe);
                color: #2563eb;
                border: 1px solid rgba(147, 197, 253, 0.45);
                flex-shrink: 0;
            }
            .action-title {
                color: #0f172a;
                font-weight: 800;
                font-size: 0.88rem;
                line-height: 1.2;
            }
            .action-copy {
                color: #64748b;
                font-size: 0.72rem;
                margin-top: 0.18rem;
            }
            .alert-card {
                position: relative;
                overflow: hidden;
                border-radius: 1.5rem;
                border: 1px solid rgba(148, 163, 184, 0.14);
                background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.98));
                box-shadow: 0 16px 34px -30px rgba(15, 23, 42, 0.14);
                transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            }
            .alert-card:hover {
                transform: translateY(-4px);
                border-color: rgba(96, 165, 250, 0.24);
                box-shadow: 0 24px 38px -28px rgba(37, 99, 235, 0.2);
            }
            .dashboard-table-wrap {
                overflow: hidden;
                border-radius: 1.15rem;
                border: 1px solid rgba(191, 219, 254, 0.95);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
                box-shadow: 0 18px 36px -28px rgba(15, 23, 42, 0.12);
            }
            .dashboard-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
            }
            .dashboard-table thead th {
                padding: 0.9rem 1rem;
                background: linear-gradient(180deg, #eff6ff, #f8fbff);
                color: #475569;
                font-size: 0.7rem;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                text-align: left;
                border-bottom: 1px solid rgba(191, 219, 254, 0.9);
            }
            .dashboard-table tbody td {
                padding: 0.95rem 1rem;
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
                vertical-align: middle;
            }
            .dashboard-table tbody tr:last-child td {
                border-bottom: none;
            }
            .dashboard-table tbody tr {
                transition: background 0.2s ease;
            }
            .dashboard-table tbody tr:hover {
                background: rgba(239, 246, 255, 0.55);
            }
            .table-metric {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                min-width: 0;
            }
            .table-metric-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.95rem;
                color: #2563eb;
                background: linear-gradient(135deg, #eff6ff, #dbeafe);
                border: 1px solid rgba(147, 197, 253, 0.45);
                flex-shrink: 0;
            }
            .table-metric-name {
                color: #0f172a;
                font-size: 0.92rem;
                font-weight: 800;
                line-height: 1.2;
            }
            .table-metric-copy {
                color: #64748b;
                font-size: 0.75rem;
                margin-top: 0.15rem;
                line-height: 1.35;
            }
            .table-value {
                color: #0f172a;
                font-size: 1.35rem;
                font-weight: 900;
                line-height: 1;
            }
            .table-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.38rem;
                padding: 0.34rem 0.62rem;
                border-radius: 999px;
                border: 1px solid rgba(191, 219, 254, 0.95);
                background: rgba(239, 246, 255, 0.92);
                color: #2563eb;
                font-size: 0.64rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                white-space: nowrap;
            }
            .table-progress {
                min-width: 165px;
            }
            .table-progress-label {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                color: #64748b;
                font-size: 0.72rem;
                margin-bottom: 0.45rem;
            }
            .table-note {
                color: #0f172a;
                font-size: 0.82rem;
                font-weight: 700;
            }
            .table-empty {
                color: #94a3b8;
                font-size: 0.74rem;
                font-weight: 700;
            }
            .dashboard-split-grid {
                display: grid;
                gap: 0.95rem;
                grid-template-columns: minmax(0, 1.06fr) minmax(0, 0.94fr);
                align-items: start;
            }
            .dashboard-mini-panel {
                border-radius: 1.2rem;
                padding: 0.82rem 0.86rem 0.92rem;
            }
            .dashboard-mini-panel .dashboard-table thead th {
                padding: 0.62rem 0.7rem;
                font-size: 0.58rem;
            }
            .dashboard-mini-panel .dashboard-table tbody td {
                padding: 0.62rem 0.7rem;
            }
            .dashboard-mini-panel .table-metric {
                gap: 0.56rem;
            }
            .dashboard-mini-panel .table-metric-icon {
                width: 1.8rem;
                height: 1.8rem;
                border-radius: 0.66rem;
            }
            .dashboard-mini-panel .table-metric-name {
                font-size: 0.77rem;
            }
            .dashboard-mini-panel .table-metric-copy {
                font-size: 0.62rem;
            }
            .dashboard-mini-panel .table-value {
                font-size: 0.98rem;
            }
            .dashboard-mini-panel .table-pill {
                padding: 0.24rem 0.46rem;
                font-size: 0.53rem;
            }
            .dashboard-mini-panel .table-progress {
                min-width: 120px;
            }
            .dashboard-mini-panel .table-progress-label {
                font-size: 0.61rem;
                margin-bottom: 0.28rem;
            }
            .dashboard-mini-panel .table-note,
            .dashboard-mini-panel .table-empty {
                font-size: 0.72rem;
            }
            .dashboard-mini-panel .progress-track {
                height: 0.36rem;
            }
            .dashboard-mini-panel .dashboard-table-wrap {
                overflow-x: auto;
            }
            .developer-credit {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 0.42rem;
                justify-content: center;
                min-height: 2rem;
                padding: 0.38rem 0.72rem;
                border-radius: 999px;
                border: 1px solid rgba(125, 211, 252, 0.28);
                background:
                    linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.82)),
                    radial-gradient(circle at 12% 50%, rgba(14, 165, 233, 0.12), transparent 42%),
                    radial-gradient(circle at 88% 50%, rgba(59, 130, 246, 0.1), transparent 42%);
                box-shadow:
                    0 12px 20px -22px rgba(37, 99, 235, 0.24),
                    0 0 0 1px rgba(255, 255, 255, 0.72) inset;
                overflow: hidden;
                isolation: isolate;
            }
            .developer-credit-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.28rem;
                height: 1.28rem;
                border-radius: 999px;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(14, 165, 233, 0.18));
                color: #2563eb;
                font-size: 0.68rem;
                flex-shrink: 0;
                box-shadow: 0 0 14px rgba(125, 211, 252, 0.18);
            }
            .developer-credit::before {
                content: '';
                position: absolute;
                inset: -35% -18%;
                background: linear-gradient(90deg, transparent, rgba(125, 211, 252, 0.2), transparent);
                transform: translateX(-38%);
                animation: developer-credit-sheen 6s linear infinite;
                z-index: -1;
            }
            .developer-credit-copy {
                display: inline-flex;
                align-items: center;
                gap: 0.32rem;
                min-width: 0;
            }
            .developer-credit-label {
                font-size: 0.56rem;
                font-weight: 900;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: #1e40af;
                text-shadow: 0 0 16px rgba(125, 211, 252, 0.18);
                animation: developer-credit-fade 5.8s ease-in-out infinite;
                white-space: nowrap;
            }
            .developer-credit-name {
                position: relative;
                display: inline-flex;
                align-items: center;
                overflow: hidden;
                white-space: nowrap;
                font-size: 0.58rem;
                font-weight: 900;
                letter-spacing: 0.08em;
                color: #0f3fa8;
                text-shadow: 0 0 14px rgba(125, 211, 252, 0.16);
                padding-right: 0.1rem;
                animation: developer-credit-fade 5.8s ease-in-out infinite;
            }
            .developer-credit-name::after {
                content: '';
                position: absolute;
                inset: 0 auto 0 0;
                width: 100%;
                background: linear-gradient(90deg, rgba(255, 255, 255, 0.94) 0%, rgba(255, 255, 255, 0.18) 86%, transparent 100%);
                transform: translateX(-112%);
                animation: developer-credit-reveal 5.8s cubic-bezier(0.22, 1, 0.36, 1) infinite;
            }
            @keyframes developer-credit-fade {
                0%, 16% {
                    opacity: 0;
                    transform: translateY(1px);
                }
                24%, 72% {
                    opacity: 1;
                    transform: translateY(0);
                }
                84%, 100% {
                    opacity: 0;
                    transform: translateY(-1px);
                }
            }
            @keyframes developer-credit-reveal {
                0%, 20% {
                    transform: translateX(-112%);
                }
                30%, 68% {
                    transform: translateX(104%);
                }
                84%, 100% {
                    transform: translateX(112%);
                }
            }
            @keyframes developer-credit-sheen {
                from {
                    transform: translateX(-46%);
                }
                to {
                    transform: translateX(46%);
                }
            }
            .quick-access-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
            }
            .church-gallery-shell {
                border-radius: 1.35rem;
                border: 1px solid rgba(191, 219, 254, 0.9);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(241, 245, 249, 0.94));
                box-shadow: 0 24px 40px -32px rgba(15, 23, 42, 0.18);
                padding: 1rem;
                overflow: hidden;
            }
            .church-gallery-stage {
                overflow: hidden;
            }
            .church-gallery-track {
                display: flex;
                gap: 1rem;
                transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
                will-change: transform;
            }
            .church-gallery-tile {
                position: relative;
                overflow: hidden;
                border-radius: 1.1rem;
                border: 1px solid rgba(191, 219, 254, 0.85);
                background: #ffffff;
                min-height: 22rem;
                box-shadow: 0 18px 32px -28px rgba(15, 23, 42, 0.16);
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 calc(50% - 0.5rem);
            }
            .church-gallery-tile img {
                display: block;
                width: 100%;
                height: 22rem;
                object-fit: contain;
                background: #ffffff;
            }
            .church-gallery-overlay {
                position: absolute;
                inset: auto 0 0 0;
                padding: 1rem 1.1rem 0.95rem;
                background: linear-gradient(180deg, transparent, rgba(15, 23, 42, 0.72));
                color: white;
            }
            .church-gallery-counter {
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
            @media (max-width: 900px) {
                .metric-primary,
                .metric-compact {
                    min-height: auto;
                }
                .signal-grid {
                    grid-template-columns: 1fr;
                }
                .action-tile {
                    min-width: 100%;
                }
                .dashboard-split-grid {
                    grid-template-columns: 1fr;
                }
                .quick-access-grid {
                    grid-template-columns: 1fr;
                }
                .church-gallery-tile,
                .church-gallery-tile img {
                    height: 18rem;
                    min-height: 18rem;
                }
                .church-gallery-tile {
                    flex-basis: 100%;
                }
            }
            @media (max-width: 860px) {
                .dashboard-table-wrap {
                    overflow-x: auto;
                }
                .dashboard-table {
                    min-width: 760px;
                }
            }
        </style>
    @endonce

    <div class="dashboard-shell px-3 lg:px-4 py-4 space-y-4">
        <div class="dashboard-split-grid fade-up stagger-1">
                <div class="section-panel glass-card dashboard-mini-panel">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-3">
                        <div>
                            <span class="dashboard-tag">Overview</span>
                            <h2 class="text-slate-900 text-base lg:text-lg font-black mt-1">{{ $dashboardTitle }}</h2>
                            <p class="text-slate-600 text-[11px] mt-1.5 max-w-3xl">{{ $dashboardSubtitle }}</p>
                            <p class="text-blue-700 text-[11px] font-semibold mt-1.5">Portal user accounts are counted under `Users` only. Participant and sponsorship metrics below show center records, not the logged-in user account.</p>
                        </div>
                    </div>

                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-people-fill"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Participants</span>
                                                <span class="block table-metric-copy">Only participant form records in your center</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $participantTotal }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-activity"></i> Live Snapshot</span></td>
                                    <td class="table-progress">
                                        <div class="table-progress-label"><span>Coverage</span><span class="table-note">100%</span></div>
                                        <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-blue-400" style="width: 100%"></div></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-check-circle-fill"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Active Participants</span>
                                                <span class="block table-metric-copy">{{ $participantActiveRate }}% of all participant records</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $activeTotal }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-stars"></i> Healthy Pace</span></td>
                                    <td class="table-progress">
                                        <div class="table-progress-label"><span>Activity Rate</span><span class="table-note">{{ $participantActiveRate }}%</span></div>
                                        <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-blue-500" style="width: {{ $participantActiveRate }}%"></div></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-cash-coin"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Sponsorship Records</span>
                                                <span class="block table-metric-copy">{{ $activeSponsorshipTotal }} active sponsorship records</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $sponsorshipTotal }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-graph-up-arrow"></i> Funding Pulse</span></td>
                                    <td class="table-progress">
                                        <div class="table-progress-label"><span>Active Records</span><span class="table-note">{{ $sponsorshipActiveRate }}%</span></div>
                                        <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-300 to-blue-600" style="width: {{ max($sponsorshipActiveRate, 8) }}%"></div></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-camera-fill"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Photos</span>
                                                <span class="block table-metric-copy">Overdue follow-up items</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $overdueTotal }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-camera2"></i> Needs Action</span></td>
                                    <td class="table-progress">
                                        <div class="table-progress-label"><span>Photo Risk</span><span class="table-note">{{ $photoRiskRate }}%</span></div>
                                        <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-sky-500" style="width: {{ max($photoRiskRate, $overdueTotal > 0 ? 12 : 0) }}%"></div></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-door-open-fill"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Exit Plan</span>
                                                <span class="block table-metric-copy">Participants nearing transition</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $plannedExitTotal }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-arrow-up-right-circle"></i> Transition Watch</span></td>
                                    <td class="table-progress">
                                        <div class="table-progress-label"><span>Planned Exit Rate</span><span class="table-note">{{ $plannedExitRate }}%</span></div>
                                        <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-blue-500" style="width: {{ max($plannedExitRate, $plannedExitTotal > 0 ? 12 : 0) }}%"></div></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-person-badge-fill"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Users</span>
                                                <span class="block table-metric-copy">{{ $usersMetricCopy ?? (($adminUsersCount ?? 0) . ' admin users in this center') }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-value">{{ $usersCount ?? 0 }}</span></td>
                                    <td><span class="table-pill"><i class="bi bi-people"></i> Team Access</span></td>
                                    <td><span class="table-empty">{{ $usersMetricNote ?? 'Center-scoped access only' }}. User accounts are separate from participant and sponsorship records.</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="table-metric">
                                            <span class="table-metric-icon"><i class="bi bi-shield-check"></i></span>
                                            <span>
                                                <span class="block table-metric-name">Security</span>
                                                <span class="block table-metric-copy">No cross-center access is allowed</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="table-note">Center Locked</span></td>
                                    <td><span class="table-pill"><i class="bi bi-shield-lock"></i> Protected</span></td>
                                    <td><span class="table-empty">Access remains limited to assigned center data</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="section-panel glass-card dashboard-mini-panel">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-3">
                                <div>
                                    <span class="dashboard-tag">Status Highlights</span>
                                    <h3 class="text-slate-900 text-base lg:text-lg font-black mt-1">Operational Signals</h3>
                                    <p class="text-slate-600 text-[11px] mt-1.5 max-w-3xl">A quick operational summary of participant and sponsorship records only.</p>
                                </div>
                                <div class="developer-credit self-start sm:self-center">
                                    <span class="developer-credit-icon"><i class="bi bi-code-slash"></i></span>
                                    <span class="developer-credit-copy">
                                        <span class="developer-credit-label">Developer</span>
                                        <span class="developer-credit-name">idriss Ict Services</span>
                                    </span>
                                </div>
                        </div>

                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="table-metric">
                                                <span class="table-metric-icon"><i class="bi bi-x-circle-fill"></i></span>
                                                <span>
                                                    <span class="block table-metric-name">Exited</span>
                                                    <span class="block table-metric-copy">Participants who have already exited</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="table-value">{{ $exitTotal }}</span></td>
                                        <td><span class="table-pill"><i class="bi bi-x-circle"></i> Exit Watch</span></td>
                                        <td class="table-progress">
                                            <div class="table-progress-label"><span>Exit Rate</span><span class="table-note">{{ $exitRate }}%</span></div>
                                            <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-sky-500" style="width: {{ max($exitRate, $exitTotal > 0 ? 12 : 0) }}%"></div></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-metric">
                                                <span class="table-metric-icon"><i class="bi bi-hourglass-split"></i></span>
                                                <span>
                                                    <span class="block table-metric-name">Coming Due</span>
                                                    <span class="block table-metric-copy">Needs near-term follow-up</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="table-value">{{ $comingDueTotal }}</span></td>
                                        <td><span class="table-pill"><i class="bi bi-hourglass-split"></i> Attention Queue</span></td>
                                        <td class="table-progress">
                                            <div class="table-progress-label"><span>Due Rate</span><span class="table-note">{{ $comingDueRate }}%</span></div>
                                            <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-blue-500" style="width: {{ max($comingDueRate, $comingDueTotal > 0 ? 12 : 0) }}%"></div></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-metric">
                                                <span class="table-metric-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                                                <span>
                                                    <span class="block table-metric-name">Serious Conditions</span>
                                                    <span class="block table-metric-copy">Participants with chronic diseases selected in the participant form</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="table-value">{{ $seriousConditionsTotal }}</span></td>
                                        <td><span class="table-pill"><i class="bi bi-heart-pulse"></i> Health Watch</span></td>
                                        <td class="table-progress">
                                            <div class="table-progress-label"><span>Health Risk Rate</span><span class="table-note">{{ $seriousConditionsRate }}%</span></div>
                                            <div class="progress-track"><div class="progress-fill bg-gradient-to-r from-slate-100 via-blue-200 to-rose-500" style="width: {{ max($seriousConditionsRate, $seriousConditionsTotal > 0 ? 12 : 0) }}%"></div></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @unless(auth()->user()->isOfficialAdmin())
                            <div class="mt-4 border-t border-slate-200/80 pt-4">
                                <div class="flex flex-col items-center gap-1.5 mb-3">
                                    <span class="dashboard-tag">Quick Access</span>
                                    <p class="text-slate-500 text-xs text-center">Choose a section to open for more details.</p>
                                </div>

                                <div class="quick-access-grid">
                                    @if(Route::has('participants.index'))
                                        <a href="{{ route('participants.index') }}" class="action-tile">
                                            <span class="action-icon"><i class="bi bi-people-fill"></i></span>
                                            <span class="flex-1 text-left">
                                                <span class="action-title">Participants</span>
                                                <span class="block action-copy">Open participant records stored in the participant form.</span>
                                            </span>
                                            <i class="bi bi-arrow-up-right text-slate-400"></i>
                                        </a>
                                    @endif
                                    @if(Route::has('sponsorships.index'))
                                        <a href="{{ route('sponsorships.index') }}" class="action-tile">
                                            <span class="action-icon"><i class="bi bi-cash-coin"></i></span>
                                            <span class="flex-1 text-left">
                                                <span class="action-title">Sponsorships</span>
                                                <span class="block action-copy">Track sponsorship records linked to participants.</span>
                                            </span>
                                            <i class="bi bi-arrow-up-right text-slate-400"></i>
                                        </a>
                                    @endif
                                    @if(Route::has('notifications.index'))
                                        <a href="{{ route('notifications.index') }}" class="action-tile">
                                            <span class="action-icon"><i class="bi bi-bell-fill"></i></span>
                                            <span class="flex-1 text-left">
                                                <span class="action-title">Notifications</span>
                                                <span class="block action-copy">Review admin messages sent to your center.</span>
                                            </span>
                                            <i class="bi bi-arrow-up-right text-slate-400"></i>
                                        </a>
                                    @endif
                                    @if(Route::has('treatments.index'))
                                        <a href="{{ route('treatments.index') }}" class="action-tile">
                                            <span class="action-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                                            <span class="flex-1 text-left">
                                                <span class="action-title">Treatment</span>
                                                <span class="block action-copy">Record and review participant treatment history separately.</span>
                                            </span>
                                            <i class="bi bi-arrow-up-right text-slate-400"></i>
                                        </a>
                                    @endif
                                    @if(($isAdmin ?? false) && Route::has('admin.users.index'))
                                        <a href="{{ route('admin.users.index') }}" class="action-tile">
                                            <span class="action-icon"><i class="bi bi-person-badge-fill"></i></span>
                                            <span class="flex-1 text-left">
                                                <span class="action-title">Manage Users</span>
                                                <span class="block action-copy">Update permissions and center assignments.</span>
                                            </span>
                                            <i class="bi bi-arrow-up-right text-slate-400"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endunless
                  </div>
        </div>

        @unless($isAdmin ?? false)
            <div class="section-panel glass-card dashboard-mini-panel fade-up stagger-2"
                 x-data="{
                    churchFormOpen: {{ $errors->any() ? 'true' : 'false' }},
                    churchPhotoFormOpen: false,
                    churchDetailsOpen: false
                 }">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                <div>
                    <span class="dashboard-tag">Church Profile</span>
                    <h3 class="text-slate-900 text-[1rem] font-bold mt-1">Historical Background</h3>
                    <p class="text-slate-600 text-[12px] mt-1">Save a short history of your church and keep a gallery of church photos for this center.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="text-[11px] text-slate-500 font-semibold">
                        Center: {{ $dashboardCenterId ?? 'N/A' }}
                    </div>
                    <button type="button" class="btn-primary" @click="churchFormOpen = !churchFormOpen">
                        <span x-text="churchFormOpen ? 'Close Form' : 'Add Church Historical Background'"></span>
                    </button>
                    <button type="button" class="btn-ghost" @click="churchPhotoFormOpen = !churchPhotoFormOpen">
                        <span x-text="churchPhotoFormOpen ? 'Close Photo Upload' : 'Upload Church Photos'"></span>
                    </button>
                    @if($churchProfile)
                        <button type="button" class="btn-ghost" @click="churchDetailsOpen = true">
                            View Historical Background
                        </button>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white/80 p-4 mb-4">
                @if($churchProfile)
                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                        <div class="space-y-3">
                            <div>
                                <p class="workspace-field-label">Church Name</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $churchProfile->church_name ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="workspace-field-label">Historical Background</p>
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-3 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->historical_background ?: 'No church historical background saved yet.' }}</div>
                            </div>
                            <div>
                                <p class="workspace-field-label">Mission</p>
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-3 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->mission ?: 'No mission saved yet.' }}</div>
                            </div>
                            <div>
                                <p class="workspace-field-label">Vision</p>
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-3 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->vision ?: 'No vision saved yet.' }}</div>
                            </div>
                        </div>

                        <div>
                            <p class="workspace-field-label mb-3">Church Photo Gallery</p>
                            @if (!empty($churchProfile->photo_urls))
                                <div class="church-gallery-shell"
                                     x-data="{
                                        photos: {{ \Illuminate\Support\Js::from($churchProfile->photo_urls) }},
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
                                    <div class="church-gallery-stage">
                                        <div class="church-gallery-track"
                                             :style="`transform: translateX(-${current * (slideWidth())}%);`">
                                            @foreach ($churchProfile->photo_urls as $index => $photoUrl)
                                                <div class="church-gallery-tile">
                                                    <img src="{{ $photoUrl }}" alt="Church photo {{ $index + 1 }}">
                                                    <div class="church-gallery-overlay">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div>
                                                                <p class="text-sm font-semibold">{{ $churchProfile->church_name ?: 'Church Photo Gallery' }}</p>
                                                                <p class="text-xs text-white/80">Saved church gallery for this center.</p>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="church-gallery-counter">
                                                                    {{ $index + 1 }} / {{ count($churchProfile->photo_urls) }}
                                                                </span>
                                                                <form method="POST" action="{{ route('dashboard.church-profile.photos.delete', $index) }}" onsubmit="return confirm('Delete this church photo?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="inline-flex items-center rounded-full bg-rose-500/90 px-3 py-1 text-[11px] font-semibold text-white shadow-sm transition hover:bg-rose-600">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/90 px-4 py-8 text-center text-sm text-slate-500">
                                    No church photos uploaded yet.
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/90 px-4 py-8 text-center text-sm text-slate-500">
                        No church historical background saved yet. Click <span class="font-semibold text-slate-700">Add Church Historical Background</span> to create it.
                    </div>
                @endif
            </div>

            <form x-cloak x-show="churchFormOpen" x-transition
                  method="POST"
                  action="{{ route('dashboard.church-profile.update') }}"
                  class="space-y-4 rounded-2xl border border-slate-200/80 bg-white/85 p-4">
                @csrf

                <div>
                    <label class="workspace-field-label">Church Name</label>
                    <input
                        type="text"
                        name="church_name"
                        value="{{ old('church_name', $churchProfile->church_name ?? '') }}"
                        class="workspace-input px-4 py-3"
                        placeholder="Enter your church name">
                </div>

                <div>
                    <label class="workspace-field-label">Historical Background</label>
                    <textarea
                        name="historical_background"
                        rows="6"
                        class="workspace-textarea px-4 py-3"
                        placeholder="Write the historical background of your church here...">{{ old('historical_background', $churchProfile->historical_background ?? '') }}</textarea>
                </div>

                <div>
                    <label class="workspace-field-label">Mission</label>
                    <textarea
                        name="mission"
                        rows="4"
                        class="workspace-textarea px-4 py-3"
                        placeholder="Write the mission of your church...">{{ old('mission', $churchProfile->mission ?? '') }}</textarea>
                </div>

                <div>
                    <label class="workspace-field-label">Vision</label>
                    <textarea
                        name="vision"
                        rows="4"
                        class="workspace-textarea px-4 py-3"
                        placeholder="Write the vision of your church...">{{ old('vision', $churchProfile->vision ?? '') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">Save Church Profile</button>
                    <button type="button" class="btn-ghost" @click="churchFormOpen = false">Cancel</button>
                </div>
            </form>

            <form x-cloak x-show="churchPhotoFormOpen" x-transition
                  method="POST"
                  action="{{ route('dashboard.church-profile.update') }}"
                  enctype="multipart/form-data"
                  class="mt-4 space-y-4 rounded-2xl border border-slate-200/80 bg-white/85 p-4">
                @csrf

                <div>
                    <label class="workspace-field-label">Church Photos</label>
                    <input
                        type="file"
                        name="church_photos[]"
                        multiple
                        accept=".jpg,.jpeg,.png,.webp"
                        class="workspace-input px-4 py-3">
                    <p class="text-[11px] text-slate-500 mt-2">You can upload multiple church photos. New photos will be added to the gallery below.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">Save Church Photos</button>
                    <button type="button" class="btn-ghost" @click="churchPhotoFormOpen = false">Cancel</button>
                </div>
            </form>

            @if($churchProfile)
                <div
                    x-cloak
                    x-show="churchDetailsOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/55 p-4"
                    @keydown.escape.window="churchDetailsOpen = false">
                    <div
                        @click.outside="churchDetailsOpen = false"
                        class="w-full max-w-4xl rounded-[1.75rem] border border-slate-200/80 bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="dashboard-tag">Church Profile</span>
                                <h3 class="mt-3 text-xl font-bold text-slate-900">{{ $churchProfile->church_name ?: 'Church Profile' }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Read the saved mission, vision, and historical background for this center.</p>
                            </div>
                            <button type="button" class="btn-ghost" @click="churchDetailsOpen = false">Close</button>
                        </div>

                        <div class="mt-6 grid gap-4">
                            <div>
                                <p class="workspace-field-label">Historical Background</p>
                                <div class="mt-2 rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-4 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->historical_background ?: 'No church historical background saved yet.' }}</div>
                            </div>
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <p class="workspace-field-label">Mission</p>
                                    <div class="mt-2 rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-4 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->mission ?: 'No mission saved yet.' }}</div>
                                </div>
                                <div>
                                    <p class="workspace-field-label">Vision</p>
                                    <div class="mt-2 rounded-2xl border border-slate-200/80 bg-slate-50/90 px-4 py-4 text-sm leading-7 text-slate-700 whitespace-pre-line">{{ $churchProfile->vision ?: 'No vision saved yet.' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </div>
        @endunless

    </div>
</x-app-layout>
