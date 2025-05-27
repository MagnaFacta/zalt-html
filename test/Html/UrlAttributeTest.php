<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html;

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @since      Class available since version 1.0
 */
class UrlAttributeTest extends \PHPUnit\Framework\TestCase
{
    public function testHrefGeneration()
    {
        $attr = new HrefArrayAttribute(['/controller/', '/action/', 'sort' => 'asc', 'new' => 'value']);

        $this->assertEquals('/controller/action?sort=asc&new=value', $attr->render());
        $this->assertEquals('href', $attr->getAttributeName());
    }

    public function testSrcGeneration()
    {
        $attr = new SrcArrayAttribute(['/controller/', '/action.img']);

        $this->assertEquals('/controller/action.img', $attr->render());
        $this->assertEquals('src', $attr->getAttributeName());
    }

    /**
     * @dataProvider urlGenerationProvider
     * @param array $urlInput
     * @param string $urlOutput
     * @return void
     */
    public function testUrlGeneration(array $urlInput, string $urlOutput)
    {
        $attr = new UrlArrayAttribute('href', $urlInput);
        
        $this->assertEquals($urlOutput, $attr->render());
        $this->assertEquals('href', $attr->getAttributeName());
    }    
    
    public static function urlGenerationProvider()
    {
        return [
            [['http://localhost/', 'dump'], 'http://localhost/dump'],
            [['dump', 'http://localhost/'], 'dumphttp://localhost'],
            [['dump/', '/more'], 'dump/more'],
            [['/dump', 'more'], '/dumpmore'],
            [['use', '/param', 'value' => 'pair'], 'use/param?value=pair'],
            [['use', 'multiple' => 'param', 'value' => 'pairs'], 'use?multiple=param&value=pairs'],
            [['use', 'multiple' => 'param', 'with', 'everything', 'value' => 'pairs', '/mixed'], 'usewitheverything/mixed?multiple=param&value=pairs'],
            [['just' => 'use', 'multiple' => 'param', 'value' => 'pairs'], '?just=use&multiple=param&value=pairs'],
            [['escape', 'param' => 'values', 'with' => '&>'], 'escape?param=values&with=%26amp%3B%26gt%3B'],
            [['/but', 'do' => 'not', 'show' => 'empty', 'values' => ''], '/but?do=not&show=empty'],
            [['/and', 'do' => 'not', 'show' => 'null', 'values' => null], '/and?do=not&show=null'],
            [['/but', 'do' => 'always', 'show' => 'zero', 'values' => 0], '/but?do=always&show=zero&values=0'],
            [['/also', '', 'ignore/', null, 'all', '/empty/', 'stringparts'], '/alsoignore/all/empty/stringparts'],
            [['/also/', '', '/ignore/', null, 'all', '/empty/', 'stringparts/', false, '/except/', 0], '/also/ignore/all/empty/stringparts/except/0'],
            [['/and', '' => 'don\'t', 'ignore' => 'any', 'empty' => 'keys'], '/and?ignore=any&empty=keys'],
            [[1 => '/but/', 0 => 'dont', 'ignore' => 'any', 'zero' => 'keys'], '/but/dont?ignore=any&zero=keys'],
            [[1 => '/but/', 0 => 'use?ampersand=when', 'already' => 'questionmark', 'was' => 'used'], '/but/use?ampersand=when&already=questionmark&was=used'],
            [['/but/when?query=is&a=single&string=with&an=ampersand'], '/but/when?query=is&a=single&string=with&an=ampersand'],
            [['/but/wrong/url?when=later', 'params=are&not=array'], '/but/wrong/url?when=laterparams=are&amp;not=array'],
            ];
    }
}