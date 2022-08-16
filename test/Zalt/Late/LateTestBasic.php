<?php

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2022, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace Zalt\Late;

use PHPUnit\Framework\TestCase;
use Zalt\Late\LateCall;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @license    New BSD License
 * @since      Class available since version 1.90
 */
class LateTestBasic extends TestCase
{
    /**
     * This method is called before a test is executed.
     * /
    protected function setUp()
    {
    } // */

    public function testLateAlternate()
    {
        $call = Late::alternate(1, 2, 3);
        
        $this->assertEquals(Alternate::class, get_class($call));
        $this->assertEquals(1, Late::raise($call));
        $this->assertEquals(2, Late::raise($call));
        $this->assertEquals(3, Late::raise($call));
        $this->assertEquals(1, Late::raise($call));
        $this->assertNotEquals(1, Late::raise($call));
        $this->assertEquals(3, Late::raise($call));
        $this->assertEquals(1, Late::raise($call));
    }
    
    public function testLateCall()
    {
        $call = Late::call('max', 0, 1);
        
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals(1, Late::raise($call));
    }

    public function lateCompProvider()
    {
        return [
            [0, '==', 1, false],
            [1, '==', '1', true],
            [1, '===', '1', false],
            [1, '===', 1, true],
            [0, '!=', 1, true],
            [0, '<>', 1, true],
            [1, '!=', 1, false],
            [1, '!=', '1', false],
            [1, '!==', 1, false],
            [1, '!==', '1', true],
            [0, '<', 1, true],
            [1, '<', 1, false],
            [0, '>', 1, false],
            [1, '>', 1, false],
            [0, '<=', 1, true],
            [1, '<=', 1, true],
            [0, '>=', 1, false],
            [1, '>=', 1, true],
            [1, '<=>', 1, 0],
            [1, '<=>', 2, -1],
            [2, '<=>', 1, 1],
            [1, '&', 2, 0],
            [1, '&', 3, 1],
            [1, '|', 2, 3],
            [1, '|', 3, 3],
        ];
    }
    
    /**
     * @dataProvider lateCompProvider
     * @return void
     */
    public function testLateComp($val1, $oper, $val2, $expected)
    {
        $call = Late::comp( $val1, $oper, $val2);
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals($expected, Late::raise($call));
    }
    
    public function testLateConcat()
    {
        $call = Late::concat('max', ' ', 'min');
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals('max min', Late::raise($call));

        $call = Late::concat('a', ['b', 'c'], 'd', ['e']);
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals('abcde', Late::raise($call));

        $object = new \stdClass();
        $object->a = 'X';
        $call = Late::concat(Late::property($object, 'a'), ' ', Late::property($object, 'b'));
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals('X ', Late::raise($call));
        
        
        $object->b = 'Y';
        $this->assertEquals('X Y', Late::raise($call));

        $object->a = 'B';
        $object->b = 'B';
        $this->assertEquals('B B', Late::raise($call));
    }
    
    public function testLazyProperty()
    {
        $object = new \stdClass();
        $object->a = 'X';
        
        $call = Late::property($object, 'a');

        $this->assertEquals(LateProperty::class, get_class($call));
        $this->assertEquals('X', Late::raise($call));

        $object->a = 'Y';
        $this->assertNotEquals('X', Late::raise($call));
        $this->assertEquals('Y', Late::raise($call));
    }
}