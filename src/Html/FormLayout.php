<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 * Interface extensions that allows HtmlElements to define how to display
 * form elements.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface FormLayout
{
    /**
     * Apply this element to the form as the output decorator.
     *
     * @param \Zend_Form $form
     * @return \MUtil\Html\FormLayout
     */
    public function setAsFormLayout(\Zend_Form $form);
}
