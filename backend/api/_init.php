<?php
// backend/api/_init.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../config/db.php';

/* ==== Helpers JSON ==== */
function json_ok($data = [], $code = 200) { http_response_code($code); echo json_encode($data); exit; }
function json_error($msg, $code = 400) { http_response_code($code); echo json_encode(['success'=>false,'message'=>$msg]); exit; }
function input_json() { $raw = file_get_contents('php://input'); if(!$raw) return []; $d=json_decode($raw,true); return is_array($d)?$d:[]; }

/* ==== JWT HS256 simple ==== */
const JWT_SECRET = 'mi_clave_ultrasecreta_2025';   // <-- Usa una sola clave fija para todo el proyecto

function b64url($s){return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');}
function b64url_dec($s){return base64_decode(strtr($s, '-_', '+/'));}

function sign_token($payload, $exp = 86400) {
  $header = ['alg'=>'HS256','typ'=>'JWT'];
  $payload['exp'] = time() + $exp;
  $h = b64url(json_encode($header));
  $p = b64url(json_encode($payload));
  $s = b64url(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
  return "$h.$p.$s";
}

function parse_token($jwt) {
  $parts = explode('.', $jwt);
  if(count($parts)!==3) return null;
  [$h,$p,$s] = $parts;
  $chk = b64url(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
  if(!hash_equals($chk,$s)) return null;
  $payload = json_decode(b64url_dec($p), true);
  if(!is_array($payload)) return null;
  if(isset($payload['exp']) && time() >= (int)$payload['exp']) return null;
  return $payload;
}

/* ==== Header Authorization robusto ==== */
function bearer_token() {
    $h = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['Authorization']
        ?? '';
    if (!$h && function_exists('apache_request_headers')) {
        $hdrs = apache_request_headers();
        if (isset($hdrs['Authorization'])) $h = $hdrs['Authorization'];
    }
    return (stripos($h,'Bearer ')===0) ? trim(substr($h,7)) : null;
}

function current_user(){ $t=bearer_token(); if(!$t) return null; return parse_token($t); }
function require_token(){ $u=current_user(); if(!$u) json_error('No autenticado',401); return $u; }
function require_roles($roles){ $u=require_token(); if(!in_array($u['rol'],$roles,true)) json_error('No autorizado',403); return $u; }


