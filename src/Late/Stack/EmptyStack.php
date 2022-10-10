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

namespace Zalt\Late\Stack;

use Zalt\Late\StackInterface;

/**
 * There is no stack, throw errors when used
 *
 * Defines a source for variable values in a late evaluation.
 *
 * @package    Zalt
 * @subpackage Late_Stack
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class EmptyStack implements StackInterface
{
    /**
     * @private string A string describing where this object was created.
     */
    private $_source;

    /**
     * The constructor can be used to set a source name.
     *
     * Debugging late stuff is hard enough, so we can use all the easy help we can get.
     *
     * @param string $source An optional source name to specify where this stack was created.
     */
    public function __construct(string $source = null)
    {
        $this->_source = $source;
    }

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @throws LateStackException
     */
    public function LateGet($name)
    {
        if ($this->_source) {
            throw new LateStackException("No late stack defined when called from '$this->_source', but asked for '$name' parameter.");
        } else {
            throw new LateStackException("No late stack defined, but asked for '$name' parameter.");
        }
    }
}
