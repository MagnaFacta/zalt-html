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
use Zalt\Ra\Ra;

/**
 * Class for A link element. Assumes first passed argument is the href attribute,
 * unless specified otherwise.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class AElement extends HtmlElement
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
     * @var bool The element is rendered even without content when true.
     */
    public $renderWithoutContent = true;

    /**
     * An A element, shows the url as content when no other content is available.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $href We assume the first element contains the href, unless a later element is explicitly specified as such
     * @param mixed $argArray Ra::args arguments
     */
    public function __construct($href, ...$argArray)
    {
        $args = Ra::args(func_get_args(), ['href' => HrefArrayAttribute::class]);

        if (isset($args['href']) && (! $args['href'] instanceof AttributeInterface)) {
            $args['href'] = new HrefArrayAttribute($args['href']);
        }

        parent::__construct('a', $args);

        $this->setOnEmpty($this->__get('href'));
    }

    /**
     * Static helper function to create an A element.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param mixed $href We assume the first element contains the href, unless a later element is explicitly specified as such
     * @param mixed $argArray Ra::args arguments
     */
    public static function a($href, ...$argArray)
    {
        $args = Ra::args(func_get_args(), array('href' => HrefArrayAttribute::class));

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
     * @param mixed $args
     * @return AElement
     */
    public static function email($email, ...$args)
    {
        $args = Ra::args($args);
        if (isset($args['href'])) {
            $href = $args['href'];
            unset($args['href']);
        } else {
            if (! isset($args['title'])) {
                $args['title'] = $email;
            }
            $href = ['mailto:', $email];
        }

        return new self($href, $email, $args);
    }

    /**
     * Return a link object when $iff is true
     *
     * @param \Zalt\Late\LateCall $iff The test
     * @param array $aArgs Arguments when the test is true
     * @param array $spanArgs Arguments when the test is false
     * @return mixed
     */
    public static function iflink($iff, array $aArgs, array $spanArgs = [])
    {
        if ($iff instanceof LateInterface) {
            if ($spanArgs) {
                return Late::iff($iff, Html::create('a', $aArgs), Html::create('span', $spanArgs, array('renderWithoutContent' => false)));
            } else {
                return Late::iff($iff, Html::create('a', $aArgs));
            }
        }
        if ($iff) {
            return Html::create('a', $aArgs);
        } elseif ($spanArgs) {
            return Html::create('span', $spanArgs, array('renderWithoutContent' => false));
        }
    }

    /**
     * Return a mailto link if $email exists and other wise return nothing.
     *
     * @param mixed $email
     * @param array $args
     * @return mixed
     */
    public static function ifmail($email, ...$args)
    {
        if ($email instanceof LateInterface) {
            return Late::iff($email, call_user_func_array([self::class, 'email'], $args));
        }
        if ($email) {
            return self::email($email, ...$args);
        }
        return null;
    }
}
