<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizimga kirish — Marketplace</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #FAFBF7; color: #172326; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 32px; width: 100%; max-width: 400px; }
        h1 { font-size: 24px; margin-bottom: 24px; font-weight: 700; color: #123C2F; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #123C2F; }
        .btn { background: #123C2F; color: #FFFFFF; border: none; border-radius: 8px; padding: 12px; width: 100%; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .error { color: #DC2626; font-size: 13px; margin-top: 4px; }
        .alert-error { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Tizimga kirish</h1>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login">Email yoki Telefon</label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Parol</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Kirish</button>
        </form>
    </div>
</body>
</html>
