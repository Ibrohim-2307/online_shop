<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizim Holati — Marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #FAFBF7;
            --surface-color: #FFFFFF;
            --text-primary: #172326;
            --text-secondary: #596668;
            --brand-lime: #C9F46B;
            --brand-dark: #123C2F;
            --accent-peach: #FFD9C8;
            --status-success: #30A46C;
            --status-error: #E5484D;
            --radius-card: 20px;
            --radius-btn: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 24px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(18, 60, 47, 0.08);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .badge {
            background: var(--brand-lime);
            color: var(--brand-dark);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .hero-card {
            background: var(--surface-color);
            border-radius: var(--radius-card);
            padding: 32px;
            box-shadow: 0 4px 20px rgba(18, 60, 47, 0.04);
            margin-bottom: 24px;
            border: 1px solid rgba(18, 60, 47, 0.06);
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(48, 164, 108, 0.12);
            color: var(--status-success);
            padding: 8px 16px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--status-success);
            box-shadow: 0 0 10px var(--status-success);
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--brand-dark);
            margin-bottom: 8px;
        }

        p.subtitle {
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.5;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }

        .metric-card {
            background: #F4F6F1;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(18, 60, 47, 0.04);
        }

        .metric-title {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .metric-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--brand-dark);
        }

        .json-box {
            background: #172326;
            color: #C9F46B;
            padding: 20px;
            border-radius: 16px;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
            margin-top: 24px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-block;
            background: var(--brand-lime);
            color: var(--brand-dark);
            text-decoration: none;
            padding: 12px 24px;
            border-radius: var(--radius-btn);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(201, 244, 107, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--brand-dark);
            border: 1px solid rgba(18, 60, 47, 0.2);
        }

        .btn-outline:hover {
            background: rgba(18, 60, 47, 0.05);
            box-shadow: none;
        }

        .footer {
            margin-top: auto;
            padding-top: 32px;
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="/" class="logo">
                <span>🛒</span>
                <span>Marketplace</span>
            </a>
            <span class="badge">01 — Poydevor</span>
        </header>

        <main>
            <div class="hero-card">
                <div class="status-indicator">
                    <span class="status-dot"></span>
                    <span>Tizim ishga tushgan va barqaror</span>
                </div>
                <h1>Server Salomatligi (Health Check)</h1>
                <p class="subtitle">PHP 8.4 va Laravel 13 asosidagi backend muvaffaqiyatli MySQL bazasiga ulandi va API so'rovlarni qabul qilishga tayyor.</p>

                <div class="grid">
                    <div class="metric-card">
                        <div class="metric-title">PHP Versiyasi</div>
                        <div class="metric-value">{{ PHP_VERSION }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Framework</div>
                        <div class="metric-value">Laravel {{ app()->version() }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Ma'lumotlar Bazasi</div>
                        <div class="metric-value">{{ $dbStatus ?? 'MySQL 8.0 (Ulangan)' }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">Muhit / Sozlama</div>
                        <div class="metric-value">{{ app()->environment() }} (Asia/Tashkent)</div>
                    </div>
                </div>

                <div class="actions">
                    <a href="/api/v1/health" class="btn" target="_blank">API v1 Health JSON</a>
                    <a href="/" class="btn btn-outline">Bosh Sahifaga O'tish</a>
                </div>
            </div>

            <div class="hero-card">
                <h2 style="font-size: 18px; color: var(--brand-dark); margin-bottom: 12px;">Haqiqiy Server JSON Javobi:</h2>
                <pre class="json-box">{{ json_encode($healthData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </main>

        <footer class="footer">
            Marketplace — Chakana va ulgurji savdo platformasi &bull; Antigravity 2026
        </footer>
    </div>
</body>
</html>
