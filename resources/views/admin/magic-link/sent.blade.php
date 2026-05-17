<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="referrer" content="no-referrer">
<title>Ссылка отправлена — {{ config('app.name') }}</title>
<style>
    :root { color-scheme: light dark; }
    body { font-family: 'IBM Plex Sans', system-ui, -apple-system, Segoe UI, sans-serif; background: #f5f5f4; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); padding: 32px; max-width: 420px; width: 100%; text-align: center; }
    h1 { font-size: 20px; margin: 0 0 12px; color: #1c1917; font-weight: 600; }
    p { color: #57534e; font-size: 14px; line-height: 1.5; margin: 0 0 8px; }
    .check { font-size: 36px; color: #16a34a; margin-bottom: 8px; }
    .back { display: inline-block; margin-top: 20px; font-size: 13px; color: #57534e; text-decoration: none; }
    .back:hover { color: #1c1917; text-decoration: underline; }
</style>
</head>
<body>
<main class="card">
    <div class="check">✓</div>
    <h1>Если адрес подходит — ссылка отправлена</h1>
    <p>Проверьте почту. Ссылка действует 15 минут и сработает один раз.</p>
    <p>Если письмо не пришло за пару минут — посмотрите в спам.</p>
    <a class="back" href="{{ url('/admin/login') }}">← К форме входа</a>
</main>
</body>
</html>
