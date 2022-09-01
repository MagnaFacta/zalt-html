<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsLoader;

use PHPUnit\Framework\TestCase;
use Zalt\Html\Html;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class TestNoSnippetLoader extends TestCase
{
    public function testNoSnippetLoader()
    {
        $this->expectException(SnippetLoaderMissingException::class);
        Html::getSnippetLoader();
    }
}