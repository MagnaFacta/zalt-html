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
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Loader\Exception\LoadException;
use Zalt\Mock\PotemkinTranslator;
use Zalt\Mock\SimpleFlashRequestFactory;
use Zalt\Mock\SimpleServiceManager;
use Zalt\Snippets\NullSnippet;
use Zalt\Snippets\SnippetInterface;
use Zalt\Snippets\Sub\Null2Snippet;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetLoaderDirTest extends TestCase
{
    public function testDirLoadWithSub()
    {
        $config  = ['x' => 'y'];
        $classes = [
            ServerRequestInterface::class => SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'),
            TranslatorInterface::class => new PotemkinTranslator(),
            'config' => $config,
        ];

        $sm = new SimpleServiceManager($classes);
        $sl = new SnippetLoader($sm, ['Zalt\\Snippets']);

        $snippet1 = $sl->getSnippet('Sub\\Null2Snippet', []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(Null2Snippet::class, $snippet1);
        $snippet2 = $sl->getSnippet('NullSnippet', []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);
    }

    public function testDirLoadOnlySub()
    {
        $config  = ['x' => 'y'];
        $classes = [
            ServerRequestInterface::class => SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'),
            TranslatorInterface::class => new PotemkinTranslator(),
            'config' => $config,
        ];

        $sm = new SimpleServiceManager($classes);
        $sl = new SnippetLoader($sm, ['Zalt\\Snippets\\Sub']);

        $snippet1 = $sl->getSnippet('Null2Snippet', []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(Null2Snippet::class, $snippet1);
        $snippet2 = $sl->getSnippet(NullSnippet::class, []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);

        $this->expectException(LoadException::class);
        $snippet3 = $sl->getSnippet('NullSnippet', []);
    }
}