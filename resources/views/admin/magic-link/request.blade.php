<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Вход по ссылке — {{ config('app.name') }}</title>
<style>
    :root { color-scheme: light dark; }
    body { font-family: 'IBM Plex Sans', system-ui, -apple-system, Segoe UI, sans-serif; background: #f5f5f4; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); padding: 32px; max-width: 420px; width: 100%; }
    h1 { font-size: 20px; margin: 0 0 8px; color: #1c1917; font-weight: 600; }
    p { color: #57534e; font-size: 14px; line-height: 1.5; margin: 0 0 20px; }
    label { display: block; font-size: 13px; font-weight: 500; color: #1c1917; margin-bottom: 6px; }
    input[type=email] { width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 14px; border: 1px solid #d6d3d1; border-radius: 8px; background: #fff; color: #1c1917; }
    input[type=email]:focus { outline: 2px solid #f97316; outline-offset: -1px; border-color: #f97316; }
    button { margin-top: 16px; width: 100%; padding: 10px 16px; font-size: 14px; font-weight: 600; color: #fff; background: #ea580c; border: 0; border-radius: 8px; cursor: pointer; }
    button:hover { background: #c2410c; }
    .err { color: #b91c1c; font-size: 13px; margin-top: 6px; }
    .back { display: inline-block; margin-top: 16px; font-size: 13px; color: #57534e; text-decoration: none; }
    .back:hover { color: #1c1917; text-decoration: underline; }
</style>
</head>
<body>
<main class="card">
    <h1>Вход по ссылке на e-mail</h1>
    <p>Введите e-mail администратора. На него придёт одноразовая ссылка для входа — действует 15 минут.</p>

    <form method="post" action="{{ route('admin.magic-link.send') }}" novalidate>
        @csrf
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" required autofocus autocomplete="email" value="{{ old('email') }}">
        @error('email')
            <div class="err">{{ $message }}</div>
        @enderror
        <button type="submit">Отправить ссылку</button>
    </form>

    <a class="back" href="{{ url('/admin/login') }}">← Войти по паролю</a>
</main>
</body>
</html>
