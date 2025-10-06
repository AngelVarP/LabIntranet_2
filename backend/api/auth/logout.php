<?php
require_once __DIR__ . '/../_init.php';
session_unset();
session_destroy();
json_ok(['success'=>true]);
