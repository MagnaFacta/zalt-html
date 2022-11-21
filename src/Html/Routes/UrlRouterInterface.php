<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Html\Routes
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Routes;

/**
 *
 * @package    Zalt
 * @subpackage Html\Routes
 * @since      Class available since version 1.0
 */
interface UrlRouterInterface
{
    /**
     * @param string $currentRoute
     * @return array label => routeObject 
     */
    public function getChildRoutes(string $currentRoute, array $params): array;

    public function getCurrentparams() : array;

    public function getCurrentRoute(): string;

    public function getParentUrl(string $currentRoute, array $params): ?string;
    
    public function getParentRoutes(string $currentRoute, array $params, int $for = 1): array;
}