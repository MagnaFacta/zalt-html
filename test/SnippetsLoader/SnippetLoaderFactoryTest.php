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
use Zalt\Html\Html;
use Zalt\Loader\ProjectOverloader;
use Zalt\Loader\ProjectOverloaderFactory;
use Zalt\Mock\PotemkinTranslator;
use Zalt\Mock\SimpleFlashRequestFactory;
use Zalt\Mock\SimpleServiceManager;
use Zalt\Snippets\NullSnippet;
use Zalt\Snippets\SnippetInterface;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetLoaderFactoryTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
        
        Html::testReset();
    }

    public function testMinimalFactory()
    {
        $sm     = new SimpleServiceManager(['config' => ['overLoader' => [
//            'Paths' => $input,
//            'AddTo' => true,
        ]]]);
        $overFc = new ProjectOverloaderFactory();
        $sm->set(ProjectOverloader::class, $overFc($sm));
        
        $class = new SnippetLoaderFactory();
        $sl    = $class($sm);
        
        $this->assertInstanceOf(SnippetLoader::class, $sl);
    }
    
    public function testWorkingFactory()
    {
        $this->assertFalse(Html::hasSnippetLoader());

        $sm     = new SimpleServiceManager([
            'config' => [
                'overLoader' => [
//                    'Paths' => $input,
//                    'AddTo' => true,
                    ],
                ],
           TranslatorInterface::class => new PotemkinTranslator(),
           ]);
        $overFc = new ProjectOverloaderFactory();
        $sm->set(ProjectOverloader::class, $overFc($sm));

        $class = new SnippetLoaderFactory();
        $sl    = $class($sm);

        $sl->addConstructorVariable(
            RequestInfo::class,
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );

        $this->assertInstanceOf(SnippetLoader::class, $sl);

        // Should be set by factory
        $this->assertTrue(Html::hasSnippetLoader());
        
        $snippet1 = Html::snippet('NullSnippet', ['param1' => 'param2']);
        $snippet2 = Html::snippet(NullSnippet::class, ['param1' => 'param2']);

        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(NullSnippet::class, $snippet1);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);
        $this->assertEquals($snippet1, $snippet2);
    }
}