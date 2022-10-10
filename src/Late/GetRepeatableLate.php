<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

/**
 *
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class GetRepeatableLate extends LateAbstract
{
    /**
     *
     * @var string
     */
    protected $fieldName;

    /**
     *
     * @var RepeatableInterface
     */
    protected $repeater;

    /**
     *
     * @param RepeatableInterface $bridge
     * @param string $fieldName
     */
    public function __construct(RepeatableInterface $repeater, $fieldName)
    {
        $this->repeater  = $repeater;
        $this->fieldName = $fieldName;
    }

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a late value.
     *
     * @param StackInterface $stack A \Zalt\Late\StackInterface object providing variable data
     * @return mixed
     */
    public function __toValue(StackInterface $stack)
    {
        $current = $this->repeater->__current();
        if ($current) {
            if (isset($current->{$this->fieldName})) {
                return $current->{$this->fieldName};
            }
        }
    }
}
