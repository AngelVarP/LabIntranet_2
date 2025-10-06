<?php
// backend/api/auth/login.php
require_once __DIR__ . '/../_init.php';

$in = array_merge($_POST, input_json());
$codigo = trim($in['codigo'] ?? $in['code'] ?? '');
$pass   = (string)($in['password'] ?? '');

if ($codigo==='' || $pass==='') json_error('Faltan credenciales',422);

$sql = "SELECT u.id,u.codigo,u.nombre,u.correo,u.password,r.nombre AS rol
        FROM usuarios u
        JOIN roles r ON r.id=u.rol_id
        WHERE u.codigo=? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s',$codigo);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
if(!$u) json_error('Usuario/clave inválidos',401);

$isHash = preg_match('/^\$2[aby]\$/', (string)$u['password']);
$ok = $isHash ? password_verify($pass,$u['password']) : hash_equals($u['password'],$pass);
if(!$ok) json_error('Usuario/clave inválidos',401);

$payload = ['id'=>(int)$u['id'],'codigo'=>$u['codigo'],'nombre'=>$u['nombre'],'rol'=>$u['rol']];
$token = sign_token($payload, 86400);

json_ok(['success'=>true, 'token'=>$token, 'user'=>$payload]);
