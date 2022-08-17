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

use PHPUnit\Framework\TestCase;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class HtmlCreatorTest extends TestCase
{
    public function elementCreationProvider()
    {
        return [
            ['a', AElement::class, ['url']],
            ['div', HtmlElement::class, ['class' => 'div']],
            ['h1', HnElement::class],
            ['h2', HnElement::class],
            ['h3', HnElement::class],
            ['h4', HnElement::class],
            ['h5', HnElement::class],
            ['h6', HnElement::class],
            ['p', HtmlElement::class],
        ]; 
    }
    
    public function testCreator()
    {
        $creator = Html::getCreator();
        $this->assertInstanceOf(Creator::class, $creator);
    }

    /**
     * @dataProvider elementCreationProvider
     */
    public function testElementCreation($tagName, $className, array $params = [])
    {
        $html = Html::create($tagName, ...$params);
        $this->assertInstanceOf($className, $html);

        $this->assertEquals($tagName, $html->tagName);
    }
}