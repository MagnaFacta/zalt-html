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
class LateBasicTest extends TestCase
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
        
        $this->assertInstanceOf(Alternate::class, $call);
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
        
        $this->assertInstanceOf(LateCall::class, $call);
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
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals($expected, Late::raise($call));
    }
    
    public function testLateConcat()
    {
        $call = Late::concat('max', ' ', 'min');
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('max min', Late::raise($call));

        $call = Late::concat('a', ['b', 'c'], 'd', ['e']);
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('abcde', Late::raise($call));

        $object = new \stdClass();
        $object->a = 'X';
        $call = Late::concat(Late::property($object, 'a'), ' ', Late::property($object, 'b'));
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('X ', Late::raise($call));
        
        
        $object->b = 'Y';
        $this->assertEquals('X Y', Late::raise($call));

        $object->a = 'B';
        $object->b = 'B';
        $this->assertEquals('B B', Late::raise($call));
    }
    
    public function testLateFirst()
    {
        $class = new \stdClass();
        $class->a = null;
        $class->b = false;
        $class->c = 0;
        $class->d = '';
        $class->e = 'Yes';
        
        $call = Late::first(
            Late::property($class, 'a'),
            Late::property($class, 'b'),
            Late::property($class, 'c'),
            Late::property($class, 'd'),
            Late::property($class, 'e')
        );
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('Yes', Late::raise($call));
        
        $class->d = 'Now this!';
        $this->assertEquals('Now this!', Late::raise($call));
        
        $class->c = true;
        $this->assertTrue(Late::raise($call));
    }

    public function testLateObjectWrap()
    {
        $class = new \stdClass();
        $class->a = null;
        $class->b = false;
        $class->c = 0;
        $class->d = '';
        $class->e = 'Yes';
        
        $wrap = Late::L($class);
        $this->assertInstanceOf(ObjectWrap::class, $wrap);

        $call = Late::first($wrap->a, $wrap->b, $wrap->c, $wrap->d, $wrap->e);
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('Yes', Late::raise($call));

        $class->d = 'Now this!';
        $this->assertEquals('Now this!', Late::raise($call));

        $class->c = true;
        $this->assertTrue(Late::raise($call));
    }

    public function testLateObjectArrayWrap()
    {
        $array = [
            'a' => null,
            'b' => false,
            'c' => 0,
            'd' => '',
            'e' => 'Yes',
            ];

        $wrap = Late::L($array);
        $this->assertInstanceOf(ArrayWrap::class, $wrap);

        $call = Late::first($wrap['a'], $wrap['b'], $wrap['c'], $wrap['d'], $wrap['e']);
        $this->assertInstanceOf(LateCall::class, $call);
        $this->assertEquals('Yes', Late::raise($call));

        $wrap['d'] = 'Now this!';
        $this->assertEquals('Now this!', Late::raise($call));

        $wrap['b'] = true;
        $this->assertTrue(Late::raise($call));
    }

    public function testLateMethod()
    {
        $date = new \DateTime('2001-01-31');

        $call1 = Late::method($date, 'format', 'd-m-Y');
        $call2 = Late::method($date, 'format', 'n/j/Y');
        $this->assertInstanceOf(LateCall::class, $call1);
        $this->assertEquals('31-01-2001', Late::raise($call1));
        
        $this->assertInstanceOf(LateCall::class, $call2);
        $this->assertEquals('1/31/2001', Late::raise($call2));
        
        $date->add(new \DateInterval('P9D'));
        $this->assertEquals('09-02-2001', Late::raise($call1));
        $this->assertEquals('2/9/2001', Late::raise($call2));
    }

    public function testLateProperty()
    {
        $object = new \stdClass();
        $object->a = 'X';
        
        $call = Late::property($object, 'a');

        $this->assertInstanceOf(LateProperty::class, $call);
        $this->assertEquals('X', Late::raise($call));

        $object->a = 'Y';
        $this->assertNotEquals('X', Late::raise($call));
        $this->assertEquals('Y', Late::raise($call));
    }
}