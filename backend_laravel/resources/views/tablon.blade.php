@extends('layouts.app')
@section('title','Tablón de Pedidos')

@section('header-right')
  <span x-data="{d:new Date().toLocaleString()}" x-text="d"></span>
@endsection

@section('content')
  <div x-data="tablonPage()" class="space-y-4">
    <div class="flex gap-2">
      <input x-model="q" @input.debounce.400ms="load()"
             class="border rounded px-3 py-2 w-64" placeholder="Buscar…">
      <select x-model="estado" @change="load()"
              class="border rounded px-3 py-2">
        <option value="">Estado</option>
        <template x-for="e in estados" :key="e">
          <option :value="e" x-text="e"></option>
        </template>
      </select>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="p-2 text-left">ID</th>
            <th class="p-2 text-left">Curso/Sección</th>
            <th class="p-2 text-left">Práctica</th>
            <th class="p-2 text-left">Grupo</th>
            <th class="p-2 text-left">Lab</th>
            <th class="p-2 text-left">Estado</th>
            <th class="p-2 text-left">Actualizado</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="loading">
            <tr><td colspan="7" class="p-4 text-center">Cargando…</td></tr>
          </template>
          <template x-if="!loading && rows.length===0">
            <tr><td colspan="7" class="p-4 text-center text-slate-500">Sin resultados</td></tr>
          </template>
          <template x-for="r in rows" :key="r.solicitud_id">
            <tr class="border-t">
              <td class="p-2" x-text="`#${r.solicitud_id}`"></td>
              <td class="p-2" x-text="`${r.curso_nombre} / ${r.seccion_nombre}`"></td>
              <td class="p-2" x-text="r.practica_titulo"></td>
              <td class="p-2" x-text="r.grupo_nombre"></td>
              <td class="p-2" x-text="r.laboratorio_nombre"></td>
              <td class="p-2">
                <span class="px-2 py-1 rounded text-white"
                      :class="badge(r.estado)" x-text="r.estado"></span>
              </td>
              <td class="p-2" x-text="new Date(r.actualizado_at).toLocaleString()"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
@endsection
