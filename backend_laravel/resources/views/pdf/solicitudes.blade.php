<!doctype html>
<html><head><meta charset="utf-8"><title>{{ $titulo }}</title></head>
<body>
<h2>{{ $titulo }}</h2>
@if($filtros['laboratorio_id'] || $filtros['estado'] || $filtros['desde'] || $filtros['hasta'])
<p><small>Filtros:
    @if($filtros['laboratorio_id']) Lab: {{ $filtros['laboratorio_id'] }}; @endif
    @if($filtros['estado']) Estado: {{ $filtros['estado'] }}; @endif
    @if($filtros['desde']) Desde: {{ $filtros['desde'] }}; @endif
    @if($filtros['hasta']) Hasta: {{ $filtros['hasta'] }}; @endif
</small></p>
@endif
<table width="100%" border="1" cellspacing="0" cellpadding="4">
<thead>
<tr>
  <th>ID</th><th>Estado</th><th>Prioridad</th><th>Laboratorio</th>
  <th>Curso / Sección</th><th>Práctica</th><th>Grupo</th><th>Creado</th>
</tr>
</thead>
<tbody>
@foreach($rows as $r)
<tr>
  <td>{{ $r->id }}</td>
  <td>{{ $r->estado }}</td>
  <td>{{ $r->prioridad }}</td>
  <td>{{ $r->laboratorio }}</td>
  <td>{{ $r->curso }} / {{ $r->seccion }}</td>
  <td>{{ $r->practica }}</td>
  <td>{{ $r->grupo }}</td>
  <td>{{ $r->creado_at }}</td>
</tr>
@endforeach
</tbody>
</table>
</body></html>
