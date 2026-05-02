<?php
declare(strict_types=1);

/**
 * Métadonnées du module admin (plugin) — le détail des libellés est dans config/admin_module_plugin_meta.php
 */
require_once dirname(__DIR__, 2) . "/core/AdminModulePlugin.php";

return AdminModulePlugin::fromModuleDir(__DIR__, dirname(__DIR__, 2));

