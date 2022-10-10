<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

use Countable;

/**
 * Late logical AND
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class LateAnd extends LateAbstract implements Countable
{
    /**
     * All the values to test
     *
     * @var array
     */
    private $_values = array();

    /**
     * Initializes the functions with all the parameters as AND values.
     *
     * Array parameters are added using addArray(), all other objects using add()
     *
     * @param mixed $value1
     */
    public function __construct($value1 = null)
    {
        foreach (func_get_args() as $value) {
            if (is_array($value)) {
                $this->addArray($value);
            } else {
                $this->add($value);
            }
        }
    }

    /**
     * Add a test value to this And object
     *
     * @param mixed $value
     * @return LateAnd (continuation pattern)
     */
    public function add($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add an arrya of test values to this And object
     *
     * @param mixed $value
     * @return LateAnd (continuation pattern)
     */
    public function addArray(array $values)
    {
        foreach ($values as $val) {
            $this->_values[] = $val;
        }
        return $this;
    }

    /**
     * Return the number of conditions
     *
     * @return int
     */
    public function count()
    {
        return count($this->_values);
    }

    /**
    * The functions that fixes and returns a value.
    *
    * Be warned: this function may return a late value.
    *
    * @param StackInterface $stack A StackInterface object providing variable data
    * @return mixed
    */
    public function __toValue(StackInterface $stack)
    {
        foreach ($this->_values as $value) {
            if ($value && ($value instanceof LateInterface)) {
                $value = Late::riseObject($value, $stack);
            }
            if (! $value) {
                return false;
            }
        }
        return true;
    }
}
