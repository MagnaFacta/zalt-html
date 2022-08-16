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
 * Extends the \MUtil\MultiWrapper with a render function so the result can be output as Html.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class MultiWrapper extends \MUtil\MultiWrapper implements \MUtil\Html\HtmlInterface
{
    /**
     * The class name used to create new class instances for function call results
     *
     * Those should be of this class, not the parent class.
     *
     * @var string
     */
    protected $_class = __CLASS__;

    /**
     * Renders the element into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        $results = array();

        $renderer = \MUtil\Html::getRenderer();
        foreach ($this->_array as $item) {
            $result = $renderer->renderAny($view, $item);

            if ((null !== $result) && strlen($result)) {
                $results[] = $result;
            }
        }

        if ($results) {
            return implode('', $results);
        }
    }
}
