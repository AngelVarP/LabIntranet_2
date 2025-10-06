<?php
// backend/api/usuarios/me.php
require_once __DIR__ . '/../_init.php';
$u = require_token();
json_ok($u);
