<?php

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Late;

use Traversable;

/**
 *
 * @package    Zalt
 * @subpackage Late
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatableByKeyValue extends Repeatable
{
    /**
     *
     * @param array|Traversable $data
     * @throws LateException
     */
    public function __construct($data)
    {
        if (! (is_array($data) || ($data instanceof Traversable))) {
            throw new LateException('The $data parameter is not an array or a Traversable interface instance ');
        }

        $result = array();

        foreach($data as $key => $value) {
            $result[] = array('key' => $key, 'value' => $value);
        }

        parent::__construct($result);
    }
}