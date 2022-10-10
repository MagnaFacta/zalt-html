<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html\Code;

use Zalt\Html\Html;
use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 *
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1
 */
class JavaScript extends DynamicAbstract
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
        if ($this->_inHeader instanceof LateInterface) {
            return (boolean) Late::raise($this->_inHeader);
        } else {
            return (boolean) $this->_inHeader;
        }
    }
    /**
     * Renders the element into a html string
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        $content = $this->getContentOutput();

        // Of course this setting makes little difference if you have optimized
        // your JavaScript loading by putting all script tags at the end of
        // your body. (Except that inlineScript is always loaded last.)
        if ($this->getInHeader()) {
            $scriptTag = Html::getRenderer()->getView()->headScript();
        } else {
            $scriptTag = Html::getRenderer()->getView()->inlineScript();
        }
        $scriptTag->appendScript($content);

        return '';
    }

    /**
     * When true the result is displayed in the result HEAD,
     * otherwise in the BODY.
     *
     * @param boolean $value
     * @return \Zalt\Html\Code\JavaScript (continuation pattern)
     */
    public function setInHeader($value = true)
    {
        $this->_inHeader = $value;
        return $this;
    }
}
