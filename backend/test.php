<?php
require_once __DIR__ . '/lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo class_exists('PHPMailer\PHPMailer\PHPMailer')
  ? "✅ PHPMailer está instalado correctamente"
  : "❌ PHPMailer no se encontró";
