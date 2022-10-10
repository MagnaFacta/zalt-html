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

namespace Zalt\Html;

/**
 * Extends the \Zalt\MultiWrapper with a render function so the result can be output as Html.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class MultiWrapper extends \Zalt\Ra\MultiWrapper implements \Zalt\Html\HtmlInterface
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
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        $results = array();

        $renderer = Html::getRenderer();
        foreach ($this->_array as $item) {
            $result = $renderer->renderAny($item);

            if ((null !== $result) && strlen($result)) {
                $results[] = $result;
            }
        }

        if ($results) {
            return implode('', $results);
        }
    }
}
