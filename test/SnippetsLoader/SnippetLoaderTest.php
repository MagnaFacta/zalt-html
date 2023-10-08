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
use Zalt\Base\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\RequestInfoFactory;
use Zalt\Html\Html;
use Zalt\Loader\Exception\LoadException;
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
class SnippetLoaderTest extends TestCase
{
    protected $sm;
    
    public function setUp() : void
    {
        parent::setUp();

        $options  = ['x' => 'y'];

        $this->sm = new SimpleServiceManager([
           'config' => [
               'overLoader' => [
//                    'Paths' => $input,
//                    'AddTo' => true,
                       ],
                   ],
                   TranslatorInterface::class => new PotemkinTranslator(),
                   SnippetOptions::class      => new SnippetOptions($options),
               ]);
        $overFc = new ProjectOverloaderFactory();
        $this->sm->set(ProjectOverloader::class, $overFc($this->sm));

        $class = new SnippetLoaderFactory();
        $sl    = $class($this->sm);

        $sl->addConstructorVariable(
            RequestInfo::class,
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );
       
        Html::setSnippetLoader($sl);
        Html::getSnippetLoader()->addConstructorVariable(
            RequestInfo::class,
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );

    }
    
    public function testLoaderLoading()
    {
        $loader = Html::getSnippetLoader();
        
        $this->assertInstanceOf(SnippetLoaderInterface::class, $loader);
    }
    
    public function testLoaderLoadingSnippets()
    {
        $snippet1 = Html::snippet('NullSnippet', ['param1' => 'param2']);
        $snippet2 = Html::snippet(NullSnippet::class, ['param1' => 'param2']);
        
        $this->assertInstanceOf(SnippetInterface::class, $snippet1);
        $this->assertInstanceOf(NullSnippet::class, $snippet1);
        $this->assertInstanceOf(SnippetInterface::class, $snippet2);
        $this->assertInstanceOf(NullSnippet::class, $snippet2);
        $this->assertEquals($snippet1, $snippet2);
    }

    public function testLoaderNotExistingFile()
    {
        $this->expectException(LoadException::class);
        $snippet = Html::snippet('NotExistingSnippet');
    }

    public function testLoaderNotSnippet()
    {
        $this->expectException(SnippetNotSnippetException::class);
        $snippet = Html::snippet('NotAnySnippet');
    }
}