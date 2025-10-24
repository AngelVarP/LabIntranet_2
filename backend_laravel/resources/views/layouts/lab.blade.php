<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Intranet de Laboratorio')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-slate-800">
  <header class="bg-[#0c4a6e] text-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
      <img src="{{ asset('images/logoLab.png') }}" alt="Logo" class="h-9 w-auto">
      <div class="font-semibold text-lg">Intranet de Laboratorio</div>
      <nav class="ml-auto flex items-center gap-4 text-sm">
        <a href="{{ route('app.home') }}" class="hover:underline">Inicio</a>
        <a href="{{ route('app.tablon') }}" class="hover:underline">Tablón</a>
        <a href="{{ route('app.insumos') }}" class="hover:underline">Insumos</a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
          @csrf
          <button class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded">Salir</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-6">
    @yield('content')
  </main>
</body>
</html>
