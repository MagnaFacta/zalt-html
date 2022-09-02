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
use Zalt\Late\Late;
use Zalt\Late\Stack\ArrayStack;
use Zalt\Late\Stack\LateStackException;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class LateStackTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        
        Late::clearStack();
    }

    public function testArrayStack()
    {
        $get = Late::get('a');

        $this->assertInstanceOf(LateGet::class, $get);
        
        $this->expectException(LateStackException::class);
        Late::raise($get);
        
        $stack = new ArrayStack(['a' => 'A', 'b' => 'B']);
        Late::setStack($stack);
        $this->assertEquals('A', Late::raise($get));


        $stack = new ArrayStack(['a' => 'X', 'b' => 'Y']);
        Late::setStack($stack);
        $this->assertEquals('X', Late::raise($get));
    }
}