<?php

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html\Code;

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class JavaScript extends \MUtil\Html\Code\DynamicAbstract
{
    protected $_inHeader = true;

    /**
     * When true the output should be displayed in the result HEAD,
     * otherwise in the BODY.
     *
     * @return boolean
     */
    public function getInHeader()
    {
        if ($this->_inHeader instanceof \MUtil\Lazy\LazyInterface) {
            return (boolean) \MUtil\Lazy::raise($this->_inHeader);
        } else {
            return (boolean) $this->_inHeader;
        }
    }
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
        $content = $this->getContentOutput($view);

        // Of course this setting makes little difference if you have optimized
        // your JavaScript loading by putting all script tags at the end of
        // your body. (Except that inlineScript is always loaded last.)
        if ($this->getInHeader()) {
            $scriptTag = $view->headScript();
        } else {
            $scriptTag = $view->inlineScript();
        }
        $scriptTag->appendScript($content);

        return '';
    }

    /**
     * When true the result is displayed in the result HEAD,
     * otherwise in the BODY.
     *
     * @param boolean $value
     * @return \MUtil\Html\Code\JavaScript (continuation pattern)
     */
    public function setInHeader($value = true)
    {
        $this->_inHeader = $value;
        return $this;
    }
}
