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

use Zalt\Late\LateCall;
use Zalt\Ra\ArrayString;
use Zalt\Ra\Ra;

/**
 * The Sequence class is for sequentional Html content, kind of like a DOM document fragment.
 *
 * It usual use is where you should return a single ElementInterface object but want to return a
 * sequence of objects. While implementing the \Zalt\Html\ElementInterface it does have attributes
 * nor does it return a tagname so it is not really an element, just treated as one.
 *
 * This object also contains functions for processing parameters of special types. E.g. when a
 * \Zend_View object is passed it should be stored in $this->view, not added to the core array.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.0
 *
 * @method     HtmlElement     br(...$arguments)
 * @method     HtmlElement     div(...$arguments)
 * @method     AElement        a(...$arguments)
 * @method     ColElement      col(...$arguments)
 * @method     ColGroupElement colgroup(...$arguments)
 * @method     DlElement       dd(...$arguments)
 * @method     DlElement       dl(...$arguments)
 * @method     DlElement       dt(...$arguments)
 * @method     HtmlElement     em(...$arguments)
 * @method     HtmlElement     i(...$arguments)
 * @method     LateCall        if(...$arguments)
 * @method     IFrame          iframe(...$arguments)
 * @method     HnElement       h1(...$arguments)
 * @method     HnElement       h2(...$arguments)
 * @method     HnElement       h3(...$arguments)
 * @method     HnElement       h4(...$arguments)
 * @method     HnElement       h5(...$arguments)
 * @method     HnElement       h6(...$arguments)
 * @method     HtmlElement     li(...$arguments)
 * @method     ListElement     ol(...$arguments)
 * @method     HtmlElement     p(...$arguments)
 * @method     HtmlElement     pInfo(...$arguments)
 * @method     Raw             raw(...$arguments)
 * @method     HtmlElement     small(...$arguments)
 * @method     Sequence        spaced(...$arguments)
 * @method     HtmlElement     span(...$arguments)
 * @method     Sprintf         sprintf(...$arguments)
 * @method     HtmlElement     strong(...$arguments)
 * @method     TdElement       td(...$arguments)
 * @method     TdElement       th(...$arguments)
 * @method     TrElement       tr(...$arguments)
 * @method     ListElement     ul(...$arguments)
 */
class Sequence extends ArrayString implements ElementInterface
{
    /**
     * Object classes that should not be added to the core array, but should be set using
     * a setXxx() function.
     *
     * This parameter enables sub-classes to define their own special types.
     *
     * @var array Null or array containing className => setFunction()
     */
    protected $_specialTypes = [];

    /**
     * Adds an HtmlElement to this element
     *
     * @see Creator
     *
     * @param string $name Function name becomes tagname (unless specified otherwise in \Zalt\Html\Creator)
     * @param array $arguments The content and attributes values
     * @return HtmlInterface With '$name' tagName
     */
    public function __call($name, array $arguments)
    {
        $elem = Html::createArray($name, $arguments);

        $this[] = $elem;

        return $elem;
    }

    /**
     *
     * @param mixed $args Optional Ra::args processed settings
     */
    public function __construct(...$args)
    {
        parent::__construct();

        $args = Ra::args($args);

        // Passing the $args  to parent::__construct()
        // means offsetSet() is not called.
        foreach ($args as $key => $arg) {
            $this->offsetSet($key, $arg);
        }
    }

    /**
     * Return a sequence with the items concatened without spaces or breajs
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return Sequence
     */
    public static function createSequence(...$args)
    {
        // BUG FIX: this function used to be called sequence() just
        // like all other static HtmlInterface element creation
        // functions, but as a sequence can contain a sequence
        // this lead to unexpected behaviour.
        $args = Ra::args($args);

        $seq = new self($args);

        if (! isset($args['glue'])) {
            $seq->setGlue('');
        }

        return $seq;
    }

    /**
     * Return a sequence with the items separated by spaces
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return Sequence
     */
    public static function createSpaced(...$args)
    {
        // BUG FIX: this function used to be called spaced() just
        // like all other static HtmlInterface element creation
        // functions, but as a sequence can contain a sequence
        // this lead to unexpected behaviour.
        $args = Ra::args($args);

        $seq = new self($args);

        if (! isset($args['glue'])) {
            $seq->setGlue(' ');
        }

        return $seq;
    }

    /**
     * Interface required function, not in real use
     *
     * @return string
     */
    public function getTagName()
    {
        return '';
    }

    /**
     * Set the item in the sequence, unless a set{Index} function
     * exists or the new value is an instance of a special type.
     *
     * @param mixed $index scalar
     * @param mixed $newval
     * @return void
     */
    public function offsetSet(mixed $index, mixed $newval): void
    {
        if ($index && (! is_numeric($index))) {
            if (method_exists($this, $fname = 'set' . $index)) {
                $this->$fname($newval);

                return;
            }
        }

        /*
        if (! $this->_specialTypes) {
            \Zalt\EchoOut\EchoOut::backtrace();
        } // */
        foreach ($this->_specialTypes as $class => $method) {
            if ($newval instanceof $class) {
                $this->$method($newval, $index);

                return;
            }
        }

        parent::offsetSet($index, $newval);
        return;
    }

    /**
     * Renders the element into a html string
     *
     * @return string Correctly encoded and escaped html output
     */
    public function render()
    {
        return Html::getRenderer()->renderArray($this->getIterator(), $this->getGlue());
    }
}
