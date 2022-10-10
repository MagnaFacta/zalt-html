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

use Zalt\Late\RepeatableInterface;

/**
 * RepeatRenderer wraps itself around some content and returns at rendering
 * time that content repeated multiple times or the $_emptyContent when the
 * repeater is empty.
 *
 * Most of the functions are the just to implement the ElementInterface and
 * are nothing but a stub to the internal content. These functions will
 * throw errors if you try to use them in ways that the actual $_content does
 * not allow.
 *
 * @see \Zalt\Lates\Repeatable
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatRenderer implements \Zalt\Html\ElementInterface
{
    /**
     * The content to be repeated.
     *
     * @var mixed
     */
    protected $_content;

    /**
     * The content to show when the $_repeater returns no data.
     *
     * @var mixed Optional
     */
    protected $_emptyContent;

    /**
     * Any content to mixed between the instances of content.
     *
     * @var mixed Optional
     */
    protected $_glue;

    /**
     * The repeater containing a dataset
     *
     * @var \Zalt\Late\RepeatableInterface
     */
    protected $_repeater;

    /**
     *
     * @param RepeatableInterface $repeater
     * @param string $glue Optional, content to display between repeated instances
     */
    public function __construct(RepeatableInterface $repeater, $glue = null)
    {
        $this->setRepeater($repeater);
        $this->setGlue($glue);
    }

    public function append($value)
    {
        $this->_content[] = $value;

        return $value;
    }

    public function count()
    {
        return count($this->_content);
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_content);
    }

    public function getOnEmpty()
    {
        return $this->_emptyContent;
    }

    public function getTagName()
    {
        if ($this->_content instanceof \Zalt\Html\ElementInterface) {
            return $this->_content->getTagName();
        }
        return null;
    }

    public function getRepeater()
    {
        return $this->_repeater;
    }

    public function hasRepeater()
    {
        return $this->_repeater ? true : false;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_content);
    }

    public function offsetGet($offset)
    {
        return $this->_content[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_content[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_content[$offset]);
    }

    /**
     * Renders the element into a html string
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        $renderer = Html::getRenderer();
        if ($this->hasRepeater() && $this->_content) {
            $data = $this->getRepeater();
            if ($data->__start()) {
                $html = array();
                while ($data->__next()) {
                    $html[] = $renderer->renderArray($this->_content);
                }

                if ($html) {
                    return implode($renderer->renderAny($this->_glue), $html);
                }
            }
        }
        if ($this->_emptyContent) {
            return $renderer->renderAny($this->_emptyContent);
        }

        return null;
    }

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    private function setRepeater(RepeatableInterface $data)
    {
        $this->_repeater = $data;
        return $this;
    }

    public function setGlue($glue)
    {
        $this->_glue = $glue;
        return $this;
    }

    public function setOnEmpty($content)
    {
        $this->_emptyContent = $content;
        return $this;
    }
}