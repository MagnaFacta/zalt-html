<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late_Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

/**
 * Defines a source for variable values in a late evaluation.
 *
 * As it works as an alternative stack, that is wat we call it.
 *
 * @package    Zalt
 * @subpackage Late_Stack
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface StackInterface
{
    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function LateGet($name);
}
