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
 * An object to pass simple striung, boolean, etc.. objects to a snipper, but will also pass extra objects to
 * the constructor that are not available in the Service manager or overrule those in the Service manager container
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
    
    public function get(string $id): mixed
    {
        if (isset($this->options[$id])) {
            return $this->options[$id];
        }
        return null;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function has(string $id) : bool
    {
        return array_key_exists($id, $this->options);
    }
}