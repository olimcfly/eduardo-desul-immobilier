<?php
require_once '../../core/bootstrap.php';

Auth::logout();
header('Location: /admin/login');
exit;
