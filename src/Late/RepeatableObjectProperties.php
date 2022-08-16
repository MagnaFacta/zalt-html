<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

/**
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */

class RepeatableObjectProperties extends Repeatable
{
    /**
     *
     * @var boolean
     */
    private $_hasProperties;

    public function __construct($data)
    {
        $result = array();

        $cvars = get_class_vars(get_class($data));
        $vars = get_object_vars($data);
        if (count($vars)) {
            foreach($vars as $name => $value) {
                $result[] = array('name' => $name,
                    'value' => Late::property($data, $name),
                    'from_code' => array_key_exists($name, $cvars));
            }

            $this->_hasProperties = true;
        } else {
            $this->_hasProperties = false;

        }


        parent::__construct($result);
    }

    public function hasProperties()
    {
        return $this->_hasProperties;
    }
}