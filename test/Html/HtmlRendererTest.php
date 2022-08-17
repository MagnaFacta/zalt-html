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

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HtmlRendererTest extends \PHPUnit\Framework\TestCase
{
    public function elementRenderProvider()
    {
        return [
            ['a', ['http://nu.nl'], '<a href="http://nu.nl">http://nu.nl</a>'],
            ['div', ['class' => 'div'], '<div class="div" />'],
            ['div', ['content', 'class' => 'div'], '<div class="div">content</div>'],
            ['div', ['content', 'class' => 'd"i"v'], "<div class='d\"i\"v'>content</div>"],
            ['h1', 'header', '<h1>header</h1>'],
            ['h2', 'header', '<h2>header</h2>'],
            ['h3', 'header', '<h3>header</h3>'],
            ['h4', 'header', '<h4>header</h4>'],
            ['h5', 'header', '<h5>header</h5>'],
            ['h6', 'header', '<h6>header</h6>'],
            ['p', 'text', '<p>text</p>'],
            ['ul', ['a', 'b'], "<ul><li>a</li><li>b</li></ul>", true],
            ['ul', [['a', 'b']], "<ul><li>a</li><li>b</li></ul>", true],
            ['ul', null, "<ul><li /></ul>", true],
        ];
    }

    public function otherRenderProvider()
    {
        return [
            ['array', ['a', 'b', 'c'], 'abc'],
            ['raw', 'a<b>c', 'a<b>c'],
            ['seq', ['a', 'b', 'c', 'glue' => '-'], 'a-b-c'],
            ['spaced', ['a', 'b', 'c', 'glue' => '|'], 'a|b|c'],
            ['spaced', 'a<b>c', 'a&lt;b&gt;c'],
            ['spaced', ['a', 'b', 'c'], 'a b c'],
        ];
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
        $html = Html::create($tagName, $content);
        $this->assertInstanceOf(HtmlElement::class, $html);

        if ($trim) {
            $this->assertEquals($result, trim($html->render()));
        } else {
            $this->assertEquals($result, $html->render());
        }
    }

    /**
     * @dataProvider otherRenderProvider
     */
    public function testOtherRenderer($tagName, $content, $result, $trim = false)
    {
        $html = Html::create($tagName, $content);
        $this->assertInstanceOf(HtmlInterface::class, $html);

        if ($trim) {
            $this->assertEquals($result, trim($html->render()));
        } else {
            $this->assertEquals($result, $html->render());
        }
    }
}