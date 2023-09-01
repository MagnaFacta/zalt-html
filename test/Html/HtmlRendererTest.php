<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2022, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace Zalt\Html;

use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HtmlRendererTest extends \PHPUnit\Framework\TestCase
{
    public static function elementRenderProvider()
    {
        return [
            ['a', ['http://nu.nl'], '<a href="http://nu.nl">http://nu.nl</a>'],
            ['col', [['style' => 'width: 1em;']], '<col style="width: 1em;" />'],
            ['colgroup', [['style' => 'width: 10em;', 'id' => 'col']], '<colgroup style="width: 10em;" id="col" />'],
            ['colgroup', [['style' => 'width: 10em;', 'id' => 'col', Html::create('col', ['style' => 'width: 1em;'])]], '<colgroup style="width: 10em;" id="col"><col style="width: 1em;" /></colgroup>'],
            ['dir', [['class' => 'dir', 'item1', 'item2']], '<dir class="dir"><li>item1</li><li>item2</li></dir>', true],
            ['div', [['class' => 'div']], '<div class="div" />'],
            ['div', [['content', 'class' => 'div']], '<div class="div">content</div>'],
            ['div', [['content', 'class' => 'd"i"v']], "<div class='d\"i\"v'>content</div>"],
            ['h1', ['header'], '<h1>header</h1>'],
            ['h2', ['header'], '<h2>header</h2>'],
            ['h3', ['header'], '<h3>header</h3>'],
            ['h4', ['header'], '<h4>header</h4>'],
            ['h5', ['header'], '<h5>header</h5>'],
            ['h6', ['header'], '<h6>header</h6>'],
            ['iflink', [true, ['href' => 'https://go.url', 'link'], ['nolink']], '<a href="https://go.url">link</a>'],
            ['iflink', [false, ['href' => 'https://go.url', 'link'], ['nolink']], '<span>nolink</span>'],
            ['ifmail', ['me@mo.ma', ' mail'], '<a title="me@mo.ma" onclick="event.cancelBubble=true;" href="mailto:me@mo.ma">me@mo.ma mail</a>'],
            ['iframe', [['src'=> 'https://go.url']], '<iframe src="https://go.url"></iframe>'],
            ['img', [['src' => 'delete.png']], '<img src="/images/delete.png" width="16" height="16" />'],
            ['img', [['src' => 'blank.png', 'width' => 32, 'height' => 32]], '<img src="/icons/blank.png" width="32" height="32" />'],
            ['img', [['src' => 'nope.png']], '<img src="nope.png" />'],
            ['img', [['src' => null]], ''],
            ['ol', [['a', 'b']], "<ol><li>a</li><li>b</li></ol>", true],
            ['p', ['text'], '<p>text</p>'],
            ['menu', [['class' => 'menu', 'item1', 'item2']], '<menu class="menu"><li>item1</li><li>item2</li></menu>', true],
            ['ol', [['class' => 'num-list', 'item1', 'item2']], '<ol class="num-list"><li>item1</li><li>item2</li></ol>', true],
            ['ul', ['a', 'b'], "<ul><li>a</li><li>b</li></ul>", true],
            ['ul', [['a', 'b']], "<ul><li>a</li><li>b</li></ul>", true],
            ['ul', [[null]], "<ul><li /></ul>", true],
        ];
    }

    public static function lateRenderProvider()
    {
        return [
            ['call', ['time'], Late::call('time')],
            ['if', ['time', 'a', 'b'], 'a'],
            ['if', [Late::first(null, true), 'a', 'b'], 'a'],
            ['if', [Late::first(null, false), 'a', 'b'], 'b'],
            ['if', [Late::get('yes'), 'a', 'b'], 'a'],
            ['if', [Late::get('no'), 'a', 'b'], 'b'],
        ];
    }

    public static function otherRenderProvider()
    {
        return [
            ['array', ['a', 'b', 'c'], 'abc'],
            ['raw', ['a<b>c'], 'a<b>c'],
            ['raw', [null], ''],
            ['raw', [Late::get('euro')], '&euro;'],
            ['raw', [Late::get('euroEsc')], '&amp;euro;'],
            ['seq', ['a', 'b', 'c', 'glue' => '-'], 'a-b-c'],
            ['spaced', ['a', 'b', 'c', 'glue' => '|'], 'a|b|c'],
            ['spaced', ['a<b>c'], 'a&lt;b&gt;c'],
            ['spaced', ['a', 'b', 'c'], 'a b c'],
        ];
    }

    public function setUp(): void
    {
        ImgElement::setWebRoot(dirname(__DIR__));
        ImgElement::addImageDir('icons');

        Late::setStack([
            'yes' => true,
            'no' => false,
            'euro' => '&euro;',
            'euroEsc' => Html::escape('&euro;'),
            ]);
    }
    
    public function testClassRenderer()
    {
        $creator = Html::getRenderer();
        $this->assertInstanceOf(Renderer::class, $creator);
    }

    /**
     * @dataProvider elementRenderProvider
     */
    public function testElementRenderer($tagName, $content, $result, $trim = false)
    {
        $html = Html::create($tagName, ...$content);
        $this->assertInstanceOf(HtmlElement::class, $html);

        if ($trim) {
            $this->assertEquals($result, trim($html->render()));
        } else {
            $this->assertEquals($result, $html->render());
        }
    }

    /**
     * @dataProvider lateRenderProvider
     */
    public function testLateRenderer(string $tagName, array $content, $result, bool $trim = false)
    {
        $html = Html::create($tagName, ...$content);
        $this->assertInstanceOf(LateInterface::class, $html);

        if ($trim) {
            $this->assertEquals(Late::rise($result), trim(Html::getRenderer()->renderAny(Late::rise($html))));
        } else {
            $this->assertEquals(Late::rise($result), Html::getRenderer()->renderAny(Late::rise($html)));
        }
    }
    
    /**
     * @dataProvider otherRenderProvider
     */
    public function testOtherRenderer(string $tagName, array $content, string $result, bool $trim = false)
    {
        $html = Html::create($tagName, ...$content);
        $this->assertInstanceOf(HtmlInterface::class, $html);

        if ($trim) {
            $this->assertEquals($result, trim($html->render()));
        } else {
            $this->assertEquals($result, $html->render());
        }
    }
}