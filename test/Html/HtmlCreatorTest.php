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
use Zalt\HtmlUtil\MultiWrapper;
use Zalt\Late\Late;
use Zalt\Late\LateCall;

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
            ['col', ColElement::class, ['style' => 'width: 1em;']],
            ['colgroup', ColGroupElement::class],
            ['dir', ListElement::class, ['class' => 'dir']],
            ['div', HtmlElement::class, ['class' => 'div']],
            ['dd', DdElement::class, ['content', 'class' => 'dd']],
            ['dl', DlElement::class, ['content', 'class' => 'dl']],
            ['dt', DtElement::class, ['content', 'class' => 'dt']],
            ['echo', TableElement::class, [new \DateTime()], 'table'],
            ['email', AElement::class, ['me@mo.ma', 'bla'], 'a'],
            ['h1', HnElement::class],
            ['h2', HnElement::class],
            ['h3', HnElement::class],
            ['h4', HnElement::class],
            ['h5', HnElement::class],
            ['h6', HnElement::class],
            ['iflink', AElement::class, [true, ['href' => 'https://go.url', 'link'], ['nolink']], 'a'],
            ['iflink', HtmlElement::class, [false, ['href' => 'https://go.url', 'link'], ['nolink']], 'span'],
            ['ifmail', AElement::class, ['me@mo.ma', 'mail'], 'a'],
            ['iframe', IFrame::class, ['src'=> 'https://go.url']],
            ['img', ImgElement::class, ['delete.png']],
            ['img', ImgElement::class, ['blank.png']],
            ['img', ImgElement::class, ['nope.png']],
            ['img', ImgElement::class, [null]],
            ['menu', ListElement::class, [['class' => 'menu', 'item1']]],
            ['ol', ListElement::class, [['class' => 'num-list', 'item1']]],
            ['p', HtmlElement::class],
            ['ul', ListElement::class, [['class' => 'bullet-list', 'item1']]],
        ]; 
    }

    public function otherCreationProvider()
    {
        return [
            ['array', Sequence::class, ['a', 'b']],
            ['call', LateCall::class, ['time']],
            ['if', LateCall::class, ['time', 'a', 'b']],
            ['iflink', LateCall::class, [Late::call('time'), ['href' => 'https://go.url', 'link'], ['nolink']]],
            ['ifmail', LateCall::class, [Late::call('sprintf', '%s', 'me@mo.ma'), 'mail']],
            ['ifmail', null, [null, 'mail']],
            ['raw', Raw::class, ['test']],
        ];
    }

    public function setUp(): void
    {
        ImgElement::addImageDir('icons');
    }

    public function testColGroups()
    {
        $creator = Html::getCreator();

        $colgroup = $creator->colgroup();
        $this->assertInstanceOf(ColGroupElement::class, $colgroup);
        $this->assertEquals(0, $colgroup->getColumnCount());
        
        $col1 = $colgroup->col(['style' => 'width: 1em;']);
        $this->assertInstanceOf(ColElement::class, $col1);
        $this->assertEquals(1, $colgroup->getColumnCount());
        
        $colgroup[2] = ['style' => 'width: 2em;'];
        $col2 = $colgroup[2];
        $this->assertInstanceOf(ColElement::class, $col2);
        $this->assertEquals(2, $colgroup->getColumnCount());
    }
    
    public function testCreator()
    {
        $creator = Html::getCreator();
        $this->assertInstanceOf(Creator::class, $creator);
    }

    public function testDlDtDd()
    {
        $dl = Html::create()->dl();
        $this->assertInstanceOf(DlElement::class, $dl);
        $this->assertEquals(0, $dl->count());

        $dItem1 = $dl->addItem('first', 'one');
        $this->assertInstanceOf(MultiWrapper::class, $dItem1);
        $this->assertEquals(2, $dl->count());

        $dItem2 = $dl->addItemArray('second', 'one');
        $this->assertIsArray($dItem2);
        $this->assertArrayHasKey('dt', $dItem2);
        $this->assertArrayHasKey('dd', $dItem2);
        $this->assertEquals(4, $dl->count());
        
        $dItem3 = $dl->addItemArray('third');
        $this->assertIsArray($dItem3);
        $this->assertArrayHasKey('dt', $dItem3);
        $this->assertArrayNotHasKey('dd', $dItem3);
        $this->assertEquals(5, $dl->count());
        
        $dItem4 = $dl->addItemArray( null, 'forth');
        $this->assertIsArray($dItem4);
        $this->assertArrayNotHasKey('dt', $dItem4);
        $this->assertArrayHasKey('dd', $dItem4);
        $this->assertEquals(6, $dl->count());

        $dItem5 = $dl->addItem('fifth');
        $this->assertInstanceOf(DtElement::class, $dItem5);
        $this->assertEquals(7, $dl->count());


        $dItem6 = $dl->addItem(null, 'sixth');
        $this->assertInstanceOf(DdElement::class, $dItem6);
        $this->assertEquals(8, $dl->count());

        $dItem7 = $dl->addItemArray();
        $this->assertIsArray($dItem7);
        $this->assertEmpty($dItem7);
        $this->assertArrayNotHasKey('dt', $dItem7);
        $this->assertArrayNotHasKey('dd', $dItem7);
        
        $this->assertEquals(8, $dl->count());
        
        $dl2 = Html::create()->dl('just', 'one');
        $this->assertInstanceOf(DlElement::class, $dl2);
        $this->assertEquals(2, $dl2->count());
        
        $dl3 = Html::create()->dl('all', 'four', 'of', 'them');
        $this->assertInstanceOf(DlElement::class, $dl3);
        $this->assertEquals(4, $dl3->count());
    }
    
    /**
     * @dataProvider elementCreationProvider
     */
    public function testElementCreation($tagName, $className, array $params = [], $altTag = null)
    {
        $html = Html::create($tagName, ...$params);
        $this->assertInstanceOf($className, $html);

        $this->assertEquals($altTag ?: $tagName, $html->tagName);
    }

    /**
     * @dataProvider elementCreationProvider
     */
    public function testElementCreationRaw($tagName, $className, array $params = [], $altTag = null)
    {
        $html = Html::createRaw($tagName, [...$params]);
        $this->assertInstanceOf(HtmlElement::class, $html);

        $this->assertEquals($tagName, $html->tagName);
    }

    public function testImageDirs()
    {
        $imagesDirs = ImgElement::getImageDirs();
        $this->assertCount(3, $imagesDirs);

        $this->assertEquals('/images/', ImgElement::getImageDir('delete.png'));
        $this->assertEquals('/images/', ImgElement::getImageDir('info.png'));
        $this->assertEquals('/icons/', ImgElement::getImageDir('blank.png'));
        $this->assertEquals('/icons/', ImgElement::getImageDir('empty.png'));
    }
    
    /**
     * @dataProvider otherCreationProvider
     */
    public function testOtherCreation($tagName, $className, array $params = [])
    {
        $html = Html::create($tagName, ...$params);
        if (null === $className) {
            $this->assertNull($html);   
        } else {
            $this->assertInstanceOf($className, $html);
        }
    }
    
    /**
     * @dataProvider otherCreationProvider
     */
    public function testOtherCreationRaw($tagName, $className, array $params = [])
    {
        $html = Html::createRaw($tagName, [...$params]);
        $this->assertInstanceOf(HtmlElement::class, $html);

        $this->assertEquals($tagName, $html->tagName);
    }
}