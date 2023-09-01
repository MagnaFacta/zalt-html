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

/**
 *
 * @package    Zalt
 * @subpackage SnippetsLoader
 * @since      Class available since version 1.0
 */
class SnippetsParameterTest extends TestCase
{
    protected SimpleServiceManager $sm;
    protected SnippetLoader $sl;

    public static function resultOutputProvider()
    {
        return [
            [[], '<div />'],
            [['class' => 'myClass'], '<div class="myClass" />'],
            [['content' => 'text'], '<div>text</div>'],
            [['style' => 'font-weight: bold;'], '<div style="font-weight: bold;" />'],
            [['class' => 'myClass', 'content' => 'text'], '<div class="myClass">text</div>'],
            [['class' => 'myClass', 'content' => 'text', 'style' => 'font-weight: bold;'], '<div style="font-weight: bold;" class="myClass">text</div>'],
            ];
    }
    
    public function setUp() : void
    {
        parent::setUp();

        $options  = ['x' => 'y'];

        $this->sm = new SimpleServiceManager([
           'config' => [
               'overLoader' => [
                    'Paths' => ['Zalt'],
//                    'AddTo' => true,
                     ],
               ],
           TranslatorInterface::class => new PotemkinTranslator(),
           SnippetOptions::class      => new SnippetOptions($options),
           ]);
        $overFc = new ProjectOverloaderFactory();
        $this->sm->set(ProjectOverloader::class, $overFc($this->sm));

        $class = new SnippetLoaderFactory();

        $this->sl = $class($this->sm);
        $this->sl->addConstructorVariable(
            RequestInfo::class,
            RequestInfoFactory::getMezzioRequestInfo(SimpleFlashRequestFactory::createWithoutServiceManager('http://localhost/index.php'))
        );

        Html::setSnippetLoader($this->sl);
    }

    public function testNoParams()
    {
        $snippet1 = $this->sl->getSnippet('NowYouSeeMeSnippet', []);
        $this->assertFalse($snippet1->hasHtmlOutput());
    }

    public function testNoParamsHtml()
    {
        // Does return an invisible snippet
        $snippet1 = Html::snippet('NowYouSeeMeSnippet', []);
        $this->assertNull($snippet1);
    }

    public function testParamMNoPass()
    {
        $snippet2 = $this->sl->getSnippet('NowYouSeeMeSnippet', ['visibility' => false]);
        $this->assertFalse($snippet2->hasHtmlOutput());
    }
    
    public function testParamPassing()
    {
        $snippet2 = $this->sl->getSnippet('NowYouSeeMeSnippet', ['visibility' => true]);
        $this->assertTrue($snippet2->hasHtmlOutput());
    }

    /**
     * @dataProvider resultOutputProvider
     */
    public function testResultOutput($params, $output)
    {
        $snippet1 = Html::snippet('DivClass', $params);
        $this->assertEquals($output, $snippet1->render());
    }
}