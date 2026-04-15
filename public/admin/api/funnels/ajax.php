<?php
// public/admin/api/funnels/ajax.php
// Point d'entrée unique pour toutes les actions AJAX du module Funnels

define('ROOT_PATH', dirname(dirname(dirname(dirname(dirname(__DIR__))))));
require_once ROOT_PATH . '/core/bootstrap.php';

if (!defined('MODULES_PATH')) {
    define('MODULES_PATH', ROOT_PATH . '/modules');
}

require_once MODULES_PATH . '/funnels/ajax/save_funnel.php';
