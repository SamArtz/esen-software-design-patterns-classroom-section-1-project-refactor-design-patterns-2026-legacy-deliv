<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Delivery</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        nav { background: #2c3e50; color: white; padding: 1rem 2rem; display: flex; gap: 2rem; align-items: center; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>
    <nav>
        <span>🚀 Legacy Delivery</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('orders.index') }}">Órdenes</a>
    </nav>
    <div class="container">
        @yield('content')
    </div>
    @yield('scripts')
</body>
</html>
