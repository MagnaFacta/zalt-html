<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html\Zend;

/**
 * Interface extensions that allows HtmlElements to define how to display
 * form elements.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface ZendFormLayout
{
    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @return ZendFormLayout
     */
    public function setAsFormLayout(\Zend_Form $form);
}
