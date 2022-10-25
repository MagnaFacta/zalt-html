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
use Zalt\Base\RequestInfo;
use Zalt\Base\RequestInfoFactory;
use Zalt\Loader\Exception\LoadException;
use Zalt\Loader\ProjectOverloader;
use Zalt\Loader\ProjectOverloaderFactory;
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
        $options  = ['x' => 'y'];

        $sm = new SimpleServiceManager([
           'config' => [
               'overLoader' => [
                   'Paths' => ['Zalt'],
                   'AddTo' => true,
               ],
           ],
           TranslatorInterface::class => new PotemkinTranslator(),
           SnippetOptions::class      => new SnippetOptions($options),
           ]);
        $overFc = new ProjectOverloaderFactory();
        $over   = $overFc($sm);

        $sl = new SnippetLoader($over->createSubFolderOverloader('Snippets'));
        $sl->addConstructorVariable(
            RequestInfo::class, 
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );

        $snippet1 = $sl->getSnippet('Sub\\Null2Snippet', []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(Null2Snippet::class, $snippet1);
        $snippet2 = $sl->getSnippet('NullSnippet', []);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);
    }

    public function testDirLoadOnlySub()
    {
        $options  = ['x' => 'y'];

        $sm = new SimpleServiceManager([
           'config' => [
               'overLoader' => [
                   'Paths' => ['Zalt\\Snippets\\Sub'],
                   'AddTo' => true,
               ],
           ],
           TranslatorInterface::class => new PotemkinTranslator(),
           SnippetOptions::class      => new SnippetOptions($options),
           ]);
        $overFc = new ProjectOverloaderFactory();
        $over   = $overFc($sm);

        $sl = new SnippetLoader($over);
        $sl->addConstructorVariable(
            RequestInfo::class,
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );

        $snippet1 = $sl->getSnippet('Null2Snippet', $options);
        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(Null2Snippet::class, $snippet1);
        $snippet2 = $sl->getSnippet(NullSnippet::class, $options);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);

        $this->expectException(LoadException::class);
        $snippet3 = $sl->getSnippet('NullSnippet', []);
    }
}