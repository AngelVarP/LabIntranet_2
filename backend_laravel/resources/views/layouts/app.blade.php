<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','LabIntranet')</title>
  <link rel="icon" href="{{ asset('logoLab.png') }}">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
  <header class="p-4 shadow bg-white flex items-center gap-3">
    <img src="{{ asset('logoLab.png') }}" alt="Labintranet" class="h-10 w-10 rounded">
    <h1 class="text-xl font-semibold text-slate-700">Labintranet</h1>
    <div class="ml-auto text-sm text-slate-500">@yield('header-right')</div>
  </header>

  <main class="max-w-6xl mx-auto p-4">
    @yield('content')
  </main>
</body>
</html>
