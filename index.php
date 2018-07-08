<?php
require_once __DIR__ . '/vendor/autoload.php';
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

(new \application\Application())->start();