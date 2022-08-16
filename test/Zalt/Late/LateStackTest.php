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

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @license    New BSD License
 * @since      Class available since version 1.9.2
 */
class LateStackTest extends TestCase
{
    public function testArrayStack()
    {
        $get = Late::get('a');

        $this->assertEquals('Zalt\\Late\\LateGet', get_class($get));
        
        $this->expectException('Zalt\\Late\\Stack\\LateStackException');
        Late::raise($get);
        
        $stack = new ArrayStack(['a' => 'A', 'b' => 'B']);
        Late::setStack($stack);
        $this->assertEquals('A', Late::raise($get));


        $stack = new ArrayStack(['a' => 'X', 'b' => 'Y']);
        Late::setStack($stack);
        $this->assertEquals('X', Late::raise($get));
    }
}