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

use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 * The Raw class is used to output html without character encoding or escaping.
 *
 * Use this class when you have a string containg html or escaped texts that you
 * want to output without further processing.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 */
class Raw implements HtmlInterface
{
    /**
     * Whatever should be the output
     *
     * @var string|LateInterface
     */
    private $_value;

    /**
     * Create the class with the specified string content.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * Simple helper function
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Splits the content into an array where each items either contains
     *  - a tag (starts with '<' and ends with '>'
     *  - an entity (starts with '&' and ends with ';'
     *  - other string content (not starting with '<' or '&'
     *
     * This is a utility function that simplifies e.g. search and replace
     * without messing up the markup, e.g. in the Marker class
     *
     * @see \Zalt\Html\Marker
     *
     * @return array
     */
    public function getElements()
    {
        $CONTENT = 0; // In content between the tages
        $IN_APOS = 1; // In a single quoted string in an element tag
        $IN_ENT  = 2; // In an entity reference
        $IN_TAG  = 3; // In an element tag (closing or opening does not matter
        $IN_QUOT = 4; // In a double quoted string in an element tag

        if ($this->_value instanceof LateInterface) {
            $this->_value = Late::rise($this->_value);
        }
        if ($this->_value instanceof HtmlInterface) {
            $this->_value = $this->_value->render();
        }
        
        $length   = strlen($this->_value);
        $result   = array();
        $startPos = 0;
        $mode     = $CONTENT;

        for ($i = 0; $i < $length; $i++) {
            switch ($this->_value[$i]) {
                case '&':
                    if ($CONTENT === $mode) {
                        if ($i > $startPos) {
                            // Add content (does not start with < or &
                            $result[] = substr($this->_value, $startPos, $i - $startPos);
                        }
                        $startPos = $i;
                        $mode     = $IN_ENT;
                    }
                    break;

                case ';':
                    if ($IN_ENT === $mode) {
                        // Add the entity (including & and ;
                        $result[] = substr($this->_value, $startPos, $i - $startPos + 1);
                        $startPos = $i + 1;
                        $mode     = $CONTENT;
                    }
                    break;

                case '<':
                    if (($CONTENT === $mode) || ($IN_ENT === $mode)) {
                        if ($i > $startPos) {
                            // Add content (does not start with < or &
                            $result[] = substr($this->_value, $startPos, $i - $startPos);
                        }
                        $startPos = $i;
                        $mode     = $IN_TAG;
                    }
                    break;

                case '>':
                    if ($IN_TAG === $mode) {
                        // Add the tag including opening '<' and closing '>'
                        $result[] = substr($this->_value, $startPos, $i - $startPos + 1);
                        $startPos = $i + 1;
                        $mode     = $CONTENT;
                    }
                    break;

                case '\'':
                    if ($IN_TAG === $mode) {
                        // Only a mode change when in an element tag
                        $mode = $IN_APOS;
                    } elseif ($IN_APOS === $mode) {
                        // End quote, resume tag mode
                        $mode = $IN_TAG;
                    }
                    break;

                case '"':
                    if ($IN_TAG === $mode) {
                        // End quote, resume tag mode
                        $mode = $IN_QUOT;
                    } elseif ($IN_QUOT === $mode) {
                        // End quote, resume tag mode
                        $mode = $IN_TAG;
                    }
                    break;

                // default:
                    // Intentional fall through
            }
        }

        if ($startPos < $length) {
            $result[] = substr($this->_value, $startPos);
        }
        return $result;
    }

    /**
     * The current content
     *
     * @return string
     */
    public function getValue()
    {
        return Late::rise($this->_value);
    }

    /**
     * Static helper function for creation, used by @see \Zalt\Html\Creator.
     *
     * @param string $value
     * @return Raw
     */
    public static function raw($value)
    {
        return new self($value);
    }

    /**
     * Echo the content.
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        return $this->getValue();
    }

    /**
     * Change the content.
     *
     * @param string|LateInterface $value
     * @return \Zalt\Html\Raw
     */
    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }
}
