<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | Server Error</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #09090b;
            --panel: rgba(24, 24, 27, 0.88);
            --border: rgba(255, 255, 255, 0.08);
            --text: #f4f4f5;
            --muted: #a1a1aa;
            --accent: #f59e0b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top, rgba(245, 158, 11, 0.18), transparent 35%),
                linear-gradient(160deg, #111827 0%, #09090b 60%, #030712 100%);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 1.5rem;
        }

        .card {
            width: min(100%, 36rem);
            border: 1px solid var(--border);
            background: var(--panel);
            backdrop-filter: blur(18px);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 20px 80px rgba(0, 0, 0, 0.45);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.12);
            color: #fcd34d;
            font-size: 0.875rem;
            font-weight: 600;
        }

        h1 {
            margin: 1rem 0 0.75rem;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            line-height: 1.1;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }

        .actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0 1rem;
            border-radius: 0.85rem;
            text-decoration: none;
            font-weight: 600;
        }

        .primary {
            background: var(--accent);
            color: #111827;
        }

        .secondary {
            border: 1px solid var(--border);
            color: var(--text);
            background: rgba(255, 255, 255, 0.03);
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="eyebrow">Server error</div>
        <h1>Something went wrong.</h1>
        <p>
            The request could not be completed safely. No internal details are shown here.
            Please try again in a moment or return to the homepage.
        </p>
        <div class="actions">
            <a class="primary" href="{{ route('home') }}">Go home</a>
            <a class="secondary" href="{{ url()->current() }}">Retry</a>
        </div>
    </main>
</body>
</html>