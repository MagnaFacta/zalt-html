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
 * Classes implementing the HtmlInterface are translateable into correctly
 * encoded and escaped html or even xml depending on the view passed as a
 * parameter to the one function in this interface.
 *
 * Most library classes implementing this interface either implement the
 * AttributeInterface or the ElementInterface but good examples of straight
 * implementation are Raw and (Html) MultiWrapper classes.
 *
 * As this is the simplest method to ourput html with some control over
 * it's rendering it is often used for quick implementations in
 * non-library code.
 *
 * @see \MUtil\Html\AttributeInterface
 * @see \MUtil\Html\ElementInterface
 * @see \MUtil\Html\MultiWrapper
 * @see \MUtil\Html\Raw
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface HtmlInterface
{
    /**
     * Renders the element into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    public function render(\Zend_View_Abstract $view);
}
