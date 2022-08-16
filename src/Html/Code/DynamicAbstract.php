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
abstract class DynamicAbstract implements \MUtil\Html\HtmlInterface
{
    /**
     * Contains the content to output. Can be a mix of filenames and string content.
     *
     * @var array Numeric array of strings or \MUtil\Html\HtmlInterface elements
     */
    protected $_content = array();

    /**
     * The fields that must be replaced in the content before output.
     *
     * @var array Key => string array
     */
    protected $_fields  = array();

    /**
     *
     * @var string The seperator used to join multiple content items together
     */
    protected $_seperator = "\n";

    /**
     * Creates the object storing any values with a name as a field, unless
     * there exists a set{Name} function. Other values are treated as content.
     *
     * @param mixed $args_array \MUtil\Ra::args() parameters
     */
    public function __construct($args_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        foreach ($args as $name => $value) {
            if (is_integer($name))  {
                $this->addContent($value);
            } else {
                $function = 'set' . ucfirst($name);
                if (method_exists($this, $function)) {
                    $this->$function($value);
                } else {
                    $this->setField($name, $value);
                }
            }
        }
    }

    /**
     * Add a filename or some other content that can be rendered.
     *
     * @param mixed $content
     */
    public function addContent($content)
    {
        $this->_content[] = $content;
    }

    /**
     * Renders the content
     *
     * @param \Zend_View_Abstract $view
     * @return string
     */
    protected function getContentOutput(\Zend_View_Abstract $view)
    {
        if (! $this->_content) {
            return null;
        }

        $output = array();

        $renderer = \MUtil\Html::getRenderer();
        foreach ($this->_content as $content) {
            if (! is_string($content)) {
                $content = $renderer->renderAny($view, $content);
            }

            if ((false === strpos($content, "\n")) && file_exists($content)) {
                $content = file_get_contents($content);
            }

            $output[] = $content;
        }

        if ($this->_fields) {
            $output = str_replace(array_keys($this->_fields), $this->_fields, $output);
        }

        return implode($this->_seperator, $output);
    }

    /**
     * Returns the current field value.
     *
     * No markers are used. If you want to replace '{path}' with 'x', you
     * must specificy the name '{path}', not 'path'.
     *
     * @param string $name Full name of the field.
     * @return string The value placed.
     */
    public function getField($name)
    {
        if (isset($this->_fields[$name])) {
            return $this->_fields[$name];
        }
    }

    /**
     * Checks for the existence of a field value.
     *
     * No markers are used. If you want to replace '{path}' with 'x', you
     * must specificy the name '{path}', not 'path'.
     *
     * @param string $name Full name of the field.
     * @return boolean True if it exists
     */
    public function hasField($name)
    {
        return array_key_exists($name, $this->_fields);
    }

    /**
     * Sets the default value for a field to search and replace in the content.
     *
     * Used to set the value only when it is empty.
     *
     * No markers are used. If you want to replace '{path}' with 'x', you
     * must specificy the name '{path}', not 'path'.
     *
     * @param string $name Full name to replace.
     * @param string $value The value placed.
     * @return \MUtil\Html_Link_LinkAbstract (continuation pattern)
     */
    public function setDefault($name, $value)
    {
        if (! isset($this->_fields[$name])) {
            $this->_fields[$name] = $value;
        }
        return $this;
    }

    /**
     * Set a field to search and replace in the content.
     *
     * No markers are used. If you want to replace '{path}' with 'x', you
     * must specificy the name '{path}', not 'path'.
     *
     * @param string $name Full name to replace.
     * @param string $value The value placed.
     * @return \MUtil\Html_Link_LinkAbstract (continuation pattern)
     */
    public function setField($name, $value)
    {
        $this->_fields[$name] = $value;
        return $this;
    }

    /**
     *
     * @param string $seperator
     * @return \MUtil\Html_Link_LinkAbstract (continuation pattern)
     */
    public function setSeperator($seperator)
    {
        $this->_seperator = $seperator;
        return $this;
    }
}
