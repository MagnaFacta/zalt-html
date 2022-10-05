<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetOptions implements ContainerInterface
{
    public function __construct(
        private array $options 
    ) {}
    
    public function get(string $id)
    {
        if (isset($this->options[$id])) {
            return $this->options[$id];
        }
        return null;
    }

    public function has(string $id) : bool
    {
        return array_key_exists($id, $this->options);
    }
}