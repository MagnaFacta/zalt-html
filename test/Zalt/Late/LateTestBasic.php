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

    public function testLazyAlternate()
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
    
    public function testLazyCall()
    {
        $call = Late::call('max', 0, 1);
        
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertEquals(1, Late::raise($call));
    }

    public function lazeCompProvider()
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
        ];
    }
    
    /**
     * @dataProvider lazeCompProvider
     * @return void
     */
    public function testLazyComp($val1, $oper, $val2, $trueExpected)
    {
        $call = Late::comp( $val1, $oper, $val2);
        $this->assertEquals(LateCall::class, get_class($call));
        if ($trueExpected) {
            $this->assertTrue(Late::raise($call));
        } else {
            $this->assertFalse(Late::raise($call));
        }
        $call = Late::comp( 1, '==', 1);
        $this->assertEquals(LateCall::class, get_class($call));

        $call = Late::comp( 0, '<', 1);
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertTrue(Late::raise($call));

        $call = Late::comp( 0, '>', 1);
        $this->assertEquals(LateCall::class, get_class($call));
        $this->assertFalse(Late::raise($call));
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