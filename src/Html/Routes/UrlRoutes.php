<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Html\Routes
 * @author     Matijs de Jong <mjong@magnafacta.nl>MoSn
 */

namespace Zalt\Html\Routes;

/**
 *
 * @package    Zalt
 * @subpackage Html\Routes
 * @since      Class available since version 1.0
 */
class UrlRoutes
{
    protected static ?UrlRouterInterface $router = null;
    
    public static function getCurrentChildRoutes(): array
    {
        return self::$router?->getChildRoutes(self::$router->getCurrentRoute(), self::$router->getCurrentparams()) ?: [];
    }

    public static function getCurrentParentUrl(): ?string
    {
        return self::$router?->getParentUrl(self::$router->getCurrentRoute(), self::$router->getCurrentparams());
    }

    public static function setUrlRouter(UrlRouterInterface $router): void
    {
        self::$router = $router;
    }
}