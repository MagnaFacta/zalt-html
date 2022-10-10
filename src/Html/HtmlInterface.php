<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

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
 * @see \Zalt\Html\AttributeInterface
 * @see \Zalt\Html\ElementInterface
 * @see \Zalt\Html\MultiWrapper
 * @see \Zalt\Html\Raw
 *
 * @package    Zalt
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
     * @return string Correctly encoded and escaped html output
     */
    public function render();
}
