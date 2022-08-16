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
 * Class for A link element. Assumes first passed argument is the href attribute,
 * unless specified otherwise.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class AElement extends \MUtil\Html\HtmlElement
{
    /**
     * Most elements must be rendered even when empty, others should - according to the
     * xhtml specifications - only be rendered when the element contains some content.
     *
     * $renderWithoutContent controls this rendering. By default an element tag is output
     * but when false the tag will only be present if there is some content in it.
     *
     * Some examples of elements rendered without content are:
     *   a, br, hr, img
     *
     * Some examples of elements NOT rendered without content are:
     *   dd, dl, dt, label, li, ol, table, tbody, tfoot, thead and ul
     *
     * @see $_repeater
     *
     * @var boolean The element is rendered even without content when true.
     */
    public $renderWithoutContent = true;

    /**
     * An A element, shows the url as content when no other content is available.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $href We assume the first element contains the href, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil\Ra::args arguments
     */
    public function __construct($href, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), array('href' => '\\MUtil\\Html\\HrefArrayAttribute'));

        if (isset($args['href']) && (! $args['href'] instanceof \MUtil\Html\AttributeInterface)) {
            $args['href'] = new \MUtil\Html\HrefArrayAttribute($args['href']);
        }

        parent::__construct('a', $args);

        $this->setOnEmpty($this->href);
    }

    /**
     * If the target attribute is specified and no onclick attribute is specified the target is removed and
     * a compatible javascript onclick attribute is created.
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function _htmlAttribs($attribs)
    {
        if (isset($attribs['target']) && (! isset($attribs['onclick']))) {
            // Assumption that is not tested, but when clicking on a target link, no further bubble is needed.
            $attribs['onclick'] = "event.cancelBubble = true;";
        }
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            $key = $this->view->escape($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    require_once 'Zend/Json.php';
                    $val = \Zend_Json::encode($val);
                }
                // Escape single quotes inside event attribute values.
                // This will create html, where the attribute value has
                // single quotes around it, and escaped single quotes or
                // non-escaped double quotes inside of it
                $val = str_replace('\'', '&#39;', $val);
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $val = $this->view->escape($val);
            }

            if ('id' == $key) {
                $val = $this->_normalizeId($val);
            }

            if ($val !== null && strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }
        return $xhtml;
    }

    /**
     * Static helper function to create an A element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $href We assume the first element contains the href, unless a later element is explicitly specified as such
     * @param mixed $arg_array \MUtil\Ra::args arguments
     */
    public static function a($href, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), array('href' => '\\MUtil\\Html\\HrefArrayAttribute'));

        if (isset($args['href'])) {
            $href = $args['href'];
            unset($args['href']);
        } else {
            $href = null;
        }
        return new self($href, $args);
    }

    /**
     * Return a mailto: link object
     *
     * @param mixed $email
     * @param mixed $arg_array
     * @return \self
     */
    public static function email($email, $arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args(), 1);
        if (isset($args['href'])) {
            $href = $args['href'];
            unset($args['href']);
        } else {
            if (! isset($args['title'])) {
                $args['title'] = $email;
            }
            $href = array('mailto:', $email);
        }
        if (! isset($args['onclick'])) {
            // Make sure the mail link only opens a mail window.
            $args['onclick'] = 'event.cancelBubble=true;';
        }

        return new self($href, $email, $args);
    }

    /**
     * Return a link object when $iff is true
     *
     * @param \MUtil\Lazy $iff The test
     * @param mixed $aArgs Arguments when the test is true
     * @param mixed $spanArgs Arguments when the test is false
     * @return mixed
     */
    public static function iflink($iff, $aArgs, $spanArgs = null)
    {
        if ($iff instanceof \MUtil\Lazy\LazyInterface) {
            if ($spanArgs) {
                return \MUtil\Lazy::iff($iff, \MUtil\Html::create('a', $aArgs), \MUtil\Html::create('span', $spanArgs, array('renderWithoutContent' => false)));
            } else {
                return \MUtil\Lazy::iff($iff, \MUtil\Html::create('a', $aArgs));
            }
        }
        if ($iff) {
            return \MUtil\Html::create('a', $aArgs);
        } elseif ($spanArgs) {
            return \MUtil\Html::create('span', $spanArgs, array('renderWithoutContent' => false));
        }
    }

    /**
     * Return a mailto link if $email exists and other wise return nothing.
     *
     * @param mixed $email
     * @param mixed $arg_array
     * @return mixed
     */
    public static function ifmail($email, $arg_array = null)
    {
        $args = func_get_args();
        if ($email instanceof \MUtil\Lazy\LazyInterface) {
            return \MUtil\Lazy::iff($email, call_user_func_array(array(__CLASS__, 'email'), $args));
        }
        if ($email) {
            return self::email($args);
        }
    }
}
