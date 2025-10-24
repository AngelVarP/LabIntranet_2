<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $titulo }}</title>
</head>
<body>
<h2>{{ $titulo }}</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="4">
  <thead>
    <tr>
      <th>Código</th><th>Insumo</th><th>Categoría</th><th>Stock</th><th>Mín.</th><th>Und</th>
    </tr>
  </thead>
  <tbody>
  @foreach($rows as $r)
    <tr>
      <td>{{ $r->codigo }}</td>
      <td>{{ $r->nombre }}</td>
      <td>{{ $r->categoria }}</td>
      <td>{{ number_format($r->stock,2) }}</td>
      <td>{{ number_format($r->minimo,2) }}</td>
      <td>{{ $r->unidad }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
</body>
</html>
