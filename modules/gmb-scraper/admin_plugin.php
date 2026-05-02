<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/core/AdminModulePlugin.php';

return AdminModulePlugin::fromModuleDir(__DIR__, dirname(__DIR__, 2));
