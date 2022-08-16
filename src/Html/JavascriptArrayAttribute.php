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
 * Default attribute for javascript attributes with extra functions for common tasks
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version \MUtil 1.2
 */
class JavascriptArrayAttribute extends \MUtil\Html\ArrayAttribute
{
    /**
     * String used to glue items together
     *
     * Empty string as not each array element corresponds to a single command.
     *
     * @var string
     */
    protected $_separator = '';

    /**
     * Specially treated types for a specific subclass
     *
     * @var array function name => class
     */
    protected $_specialTypes = array(
        'addUrl' => '\\MUtil\\Html\\UrlArrayAttribute',
    );

    /**
     *
     * @param string $type
     * @param mixed $arg_array \MUtil\Ra::args
     */
    public function __construct($type, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), 1);
        parent::__construct($type, 'javascript:', $args);
    }

    /**
     * Add a cancel bubble command
     *
     * @param boolean $cancelBubble
     * @return \MUtil\Html\JavascriptArrayAttribute (continuation pattern)
     */
    public function addCancelBubble($cancelBubble = true)
    {
        if ($cancelBubble) {
            $this->add("event.cancelBubble = true;");
        } else {
            $this->add("event.cancelBubble = false;");
        }
        return $this;
    }

    /**
     * Add a cancel bubble command
     *
     * @param boolean $cancelBubble
     * @return \MUtil\Html\JavascriptArrayAttribute (continuation pattern)
     */
    public function addConfirm($question)
    {
        $this->add(array(
            "if (!confirm('",
            \MUtil\Lazy::call('addslashes', $question),
            "')) {event.cancelBubble = true; return false;}"
            ));
        return $this;
    }

    /**
     * Add single code line
     *
     * @param mixed $line
     * @return \MUtil\Html\JavascriptArrayAttribute (continuation pattern)
     */
    public function addLine($line_args)
    {
        $lines = \MUtil\Ra::flatten(func_get_args());

        foreach ($lines as $line) {
            $this->add($line);
        }
        if (! (isset($line) && (';' == substr($line, -1)))) {
            $this->add(';');
        }

        return $this;
    }

    /**
     * Add a print command
     *
     * @return \MUtil\Html\JavascriptArrayAttribute (continuation pattern)
     */
    public function addPrint()
    {
        $this->add('window.print();');
        return $this;
    }

    /**
     * Add a form submit
     *
     * @param string $condition Optional condition for submit
     * @return \MUtil\Html\JavascriptArrayAttribute
     */
    public function addSubmit($condition = null)
    {
        if ($condition) {
            $this->add("if ($condition) {this.form.submit();}");
        } else {
            $this->add('this.form.submit();');
        }

        return $this;
    }

    /**
     * Add a form submit when a value has changed
     *
     * @param string $condition Optional extra condition for submit
     * @return \MUtil\Html\JavascriptArrayAttribute
     */
    public function addSubmitOnChange($condition = null)
    {
        if ($condition) {
            $this->add("if (($condition) && (this.getAttribute('value') != this.value)) {this.form.submit();}");
        } else {
            $this->add("if (this.getAttribute('value') != this.value) {this.form.submit();}");
        }

        return $this;
    }

    /**
     * Add a url open command by specifying only the link
     *
     * @param mixed $href Anything, e.g. a \MUtil\Html\UrlArrayAttribute that the code will transform to an url
     * @return \MUtil\Html\JavascriptArrayAttribute (continuation pattern)
     */
    public function addUrl($href)
    {
        $last = is_array($this->_values) ? end($this->_values) : null;
        if (false === strpos($last, 'location.href')) {
            $this->_values[] = "location.href='";
            $this->_values[] = $href;
            $this->_values[] = "';";
        } else {
            $this->_values[] = $href;
        }

        return $this;
    }
}