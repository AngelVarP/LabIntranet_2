@extends('layouts.lab')
@section('title','Inicio')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Panel principal</h1>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="{{ route('app.tablon') }}" class="p-5 bg-white rounded-xl shadow hover:shadow-md transition">
      <div class="text-lg font-semibold">Tablón de Pedidos</div>
      <p class="text-sm text-slate-600">Revisa solicitudes por estado, curso y búsqueda.</p>
    </a>
    <a href="{{ route('app.insumos') }}" class="p-5 bg-white rounded-xl shadow hover:shadow-md transition">
      <div class="text-lg font-semibold">Insumos</div>
      <p class="text-sm text-slate-600">Listado y búsqueda rápida de insumos.</p>
    </a>
  </div>
@endsection
