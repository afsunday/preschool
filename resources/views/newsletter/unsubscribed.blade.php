<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed — {{ config('app.name') }}</title>
    <link rel="icon" href="/images/brand/logo.png" type="image/png">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: #fff7f0; font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
               color: #2b2b2b; padding: 24px; }
        .card { max-width: 440px; width: 100%; background: #fff; border-radius: 22px; padding: 40px 36px;
                text-align: center; box-shadow: 0 6px 24px rgba(236, 30, 121, 0.08); }
        h1 { margin: 20px 0 8px; font-size: 22px; color: #ec1e79; }
        p { margin: 0; font-size: 15px; line-height: 1.6; color: #6a6a6a; }
        img { width: 84px; height: auto; }
        a { display: inline-block; margin-top: 28px; color: #ec1e79; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <img src="/images/brand/logo.png" alt="{{ config('app.name') }}">
        <h1>You're unsubscribed</h1>
        <p>You won't receive any more newsletters from {{ config('app.name') }}. Changed your mind? You can subscribe again any time from our website.</p>
        <a href="{{ route('home') }}">Back to {{ config('app.name') }} &rarr;</a>
    </div>
</body>
</html>
