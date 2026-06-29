<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Strata Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f5f0;
            --panel: #ffffff;
            --panel-muted: #f1f5f4;
            --ink: #18211f;
            --muted: #65716d;
            --line: #d9dfdc;
            --accent: #0f766e;
            --accent-dark: #124f4a;
            --warning: #a16207;
            --danger: #b42318;
            --shadow: 0 16px 40px rgba(24, 33, 31, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }

        a {
            color: inherit;
        }

        .shell {
            min-height: 100vh;
        }

        .topbar {
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.86);
        }

        .topbar-inner,
        .workspace {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 76px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            display: grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border-radius: 8px;
            background: var(--ink);
            color: #ffffff;
            font-weight: 800;
        }

        .brand h1,
        .brand p,
        .metric p,
        .metric strong,
        .filter-state,
        .timeline-item p,
        .detail-list dd,
        .empty-state p {
            margin: 0;
        }

        .brand h1 {
            font-size: 1rem;
            line-height: 1.2;
        }

        .brand p,
        .status-note,
        .metric p,
        .filter-state,
        .timeline-item p,
        .detail-list dt,
        .empty-state p {
            color: var(--muted);
            font-size: 0.875rem;
        }

        .status-note {
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--panel-muted);
            white-space: nowrap;
        }

        .workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 24px;
            padding: 32px 0;
        }

        .main-column,
        .side-column {
            display: grid;
            gap: 20px;
            align-content: start;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .metric,
        .filters,
        .timeline,
        .empty-state,
        .detail-panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .metric {
            padding: 16px;
        }

        .metric strong {
            display: block;
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .filters {
            display: grid;
            grid-template-columns: 1.2fr repeat(2, minmax(120px, 0.6fr)) auto;
            align-items: end;
            gap: 12px;
            padding: 16px;
        }

        label {
            display: grid;
            gap: 6px;
            color: var(--muted);
            font-size: 0.8125rem;
            font-weight: 700;
        }

        input,
        select,
        .clear-link {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #ffffff;
            color: var(--ink);
            font: inherit;
            padding: 8px 10px;
        }

        .clear-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 800;
        }

        .timeline {
            overflow: hidden;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
        }

        .section-heading h2 {
            margin: 0;
            font-size: 1rem;
        }

        .section-heading span {
            color: var(--muted);
            font-size: 0.8125rem;
        }

        .timeline-list {
            display: grid;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .timeline-item {
            display: grid;
            grid-template-columns: 76px 1fr auto;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
        }

        .timeline-item:last-child {
            border-bottom: 0;
        }

        .timeline-item time {
            color: var(--muted);
            font-size: 0.8125rem;
            font-variant-numeric: tabular-nums;
        }

        .event-title {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            font-weight: 800;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 3px 8px;
            border-radius: 999px;
            background: var(--panel-muted);
            color: var(--accent-dark);
            font-size: 0.75rem;
            font-weight: 800;
        }

        .badge.warning {
            color: var(--warning);
        }

        .badge.danger {
            color: var(--danger);
        }

        .timeline-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--panel-muted);
            color: var(--ink);
            font: inherit;
            font-size: 0.875rem;
            font-weight: 800;
            padding: 8px 10px;
            text-decoration: none;
        }

        .timeline-button.active {
            border-color: var(--accent);
            background: var(--accent);
            color: #ffffff;
        }

        .empty-state {
            padding: 22px;
        }

        .empty-state h2,
        .detail-panel h2 {
            margin: 0 0 8px;
            font-size: 1rem;
        }

        .detail-panel {
            position: sticky;
            top: 24px;
            overflow: hidden;
        }

        .detail-body {
            padding: 18px;
        }

        .detail-list {
            display: grid;
            gap: 12px;
            margin: 18px 0 0;
        }

        .detail-list div {
            display: grid;
            gap: 4px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line);
        }

        .detail-list div:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .detail-list dd {
            overflow-wrap: anywhere;
            font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
            font-size: 0.875rem;
        }

        @media (max-width: 920px) {
            .workspace {
                grid-template-columns: 1fr;
            }

            .summary-grid,
            .filters {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-panel {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .topbar-inner {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding: 16px 0;
            }

            .summary-grid,
            .filters,
            .timeline-item {
                grid-template-columns: 1fr;
            }

            .timeline-button {
                justify-self: start;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-mark" aria-hidden="true">S</div>
                    <div>
                        <h1>Strata</h1>
                        <p>Staging telemetry dashboard</p>
                    </div>
                </div>

                <div class="status-note">Prototype shell | request and query capture active when enabled</div>
            </div>
        </header>

        <main class="workspace">
            <div class="main-column">
                <section class="summary-grid" aria-label="Dashboard summary">
                    <article class="metric">
                        <strong>{{ $storedEvents ?? 0 }}</strong>
                        <p>stored events</p>
                    </article>
                    <article class="metric">
                        <strong>24h</strong>
                        <p>default retention</p>
                    </article>
                    <article class="metric">
                        <strong>250ms</strong>
                        <p>slow query threshold</p>
                    </article>
                    <article class="metric">
                        <strong>Safe</strong>
                        <p>redaction-first details</p>
                    </article>
                </section>

                <form class="filters" aria-label="Timeline filters" method="get">
                    <label>
                        Search
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Path, route, status, request id">
                    </label>
                    <label>
                        Method
                        <select name="method">
                            <option value="">Any method</option>
                            @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                                <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Status
                        <select name="status">
                            <option value="">Any status</option>
                            @foreach (['started' => 'Started', '200' => '200', '404' => '404', '500' => '500', 'ok' => 'OK', 'slow' => 'Slow', 'possible_n_plus_one' => 'Possible N+1'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <a class="clear-link" href="{{ route('strata.dashboard') }}">Clear filters</a>
                </form>

                <p class="filter-state">
                    Showing {{ $matchingEvents ?? 0 }} of {{ $storedEvents ?? 0 }} stored events
                    @if (($filters['q'] ?? '') !== '' || ($filters['method'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
                        with active filters
                    @else
                        with no active filters
                    @endif
                </p>

                <section class="timeline" aria-labelledby="timeline-heading">
                    <div class="section-heading">
                        <h2 id="timeline-heading">Timeline preview</h2>
                        <span>Newest first</span>
                    </div>

                    @if ($timelineError)
                        <section class="empty-state" aria-labelledby="timeline-error-heading">
                            <h2 id="timeline-error-heading">Timeline unavailable</h2>
                            <p>{{ $timelineError }}</p>
                        </section>
                    @elseif ($events === [])
                        <section class="empty-state" aria-labelledby="empty-heading">
                            <h2 id="empty-heading">No telemetry has been captured yet</h2>
                            <p>When capture and storage are connected, this space will show the first safe, redacted staging events that match the active filters.</p>
                        </section>
                    @else
                        <ol class="timeline-list">
                            @foreach ($events as $event)
                                <li class="timeline-item">
                                    <time>{{ $event['time'] }}</time>
                                    <div>
                                        <div class="event-title">
                                            {{ $event['type'] }}
                                            <span class="badge {{ $event['severity'] === 'warning' ? 'warning' : ($event['severity'] === 'danger' ? 'danger' : '') }}">
                                                {{ $event['status'] }}
                                            </span>
                                        </div>
                                        <p>{{ $event['summary'] }}</p>
                                        <p>{{ $event['meta'] }}</p>
                                    </div>
                                    <a
                                        class="timeline-button {{ ($selectedEventId ?? null) === $event['id'] ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['event' => $event['id']]) }}"
                                    >
                                        Details
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </div>

            <aside class="side-column" aria-label="Event details">
                <section class="detail-panel">
                    <div class="section-heading">
                        <h2>Event detail</h2>
                        <span>Redacted view</span>
                    </div>
                    <div class="detail-body">
                        <p>Details are shaped for sharing issue context without exposing request bodies, cookies, tokens, or raw query bindings.</p>

                        <dl class="detail-list">
                            @foreach ($detail as $label => $value)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd>{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </section>
            </aside>
        </main>
    </div>
</body>
</html>
