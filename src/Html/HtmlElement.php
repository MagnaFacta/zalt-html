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

use Zalt\Html\Zend\ZendLabelElement;
use Zalt\Late\LateCall;
use Zalt\Ra\Ra;
use Zalt\Late\LateInterface;
use Zalt\Late\ObjectWrap;
use Zalt\Late\ParallelRepeater;
use Zalt\Late\Procrastinator;
use Zalt\Late\Repeatable;
use Zalt\Late\RepeatableInterface;

/**
 * HtmlElement is a simple to use, extensible interface to output HTML.
 *
 * The design specifications for HtmlElement were:
 *
 * - it must be easier to use than concatenating strings,
 * - the program must echo the underlying structure of the Html output,
 * - usuable for the Html version you use without any adaptations,
 * - only minimal knowledge of the element required for use.
 *
 *
 * BASIC WORKINGS
 *
 * - at creation you specify a $tagName and a mix of content and attributes,
 * - the content of the element are the array items of the element,
 * - new child elements can be created with their tagName as function name: e.g. $this->br();
 * - the attributes are treated as properties,
 * - certain types are always used in a fixed manner (e.g. \Zalt\Late\RepeatableInterface -> setRepeater()).
 *
 * Evil but usefull functionality includes the possibility of changing the $tagName at
 * a later stage.
 *
 *
 * CREATION
 *
 * At creation, after the tag name, all other parameters are treated as a mix
 * of content an attributes, the class discerns the difference in this matter:
 * - attributes are specified in nested array with the attribute name as key,
 * - content is specified either as top-level parameter or in a nested array
 *   with numerical index,
 * - AttributeInterface objects will always be added as attributes,
 * - ElementInterface objects will always be added as content,
 *
 *
 * Most of the time elements are constructed using the Html static helper class.
 * These six examples are all equivalent:
 *
 * <code>
 * 1: $div = new HtmlElement('div', 'some content', array('class' => 'some class'));
 * 2: $div = new HtmlElement('div', array('some content', 'class' => 'some class'));
 *
 * 3: $div = Html::create('div', 'some content', array('class' => 'some class'));
 * 4: $div = Html::create('div', array('some content', 'class' => 'some class'));
 *
 * 5: $div = Html::create()->div('some content', array('class' => 'some class'));
 * 6: $div = Html::create()->div(array('some content', 'class' => 'some class'));
 * </code>
 *
 * As a style guide: use option 5 unless there is a reason to use another method.
 *
 *
 * ATTRIBUTES
 *
 * These examples result in the same attribute value:
 *
 * <code>
 * $div->class = 'some class another class';
 * $div->setAttrib('class', 'some class another class');
 * $div->appendAttrib('class', 'another class');
 * </code>
 *
 * ADDING ELEMENT CONTENT
 *
 * These eight examples add the same child element to the previous $div:
 * <code>
 * 1: $div[] = new HtmlElement('b', 'bold text', array('class' => 'bold'));
 * 2: $div[] = new HtmlElement('b', array('bold text', 'class' => 'bold'));
 *
 * 3: $div[] = Html::create('b', 'bold text', array('class' => 'bold'));
 * 4: $div[] = Html::create('b', array('bold text', 'class' => 'bold'));
 *
 * 5: $div[] = Html::create()->b('bold text', array('class' => 'bold'));
 * 6: $div[] = Html::create()->b(array('bold text', 'class' => 'bold'));
 *
 * 7: $div->b('bold text', array('class' => 'bold'));
 * 8: $div->b(array('bold text', 'class' => 'bold'));
 * </code>
 * Use option 7 for readability unless there is a reason one of the other methods is better.
 *
 *
 * ADDING TEXT CONTENT
 *
 * Adding text is easy as well:
 * <code>
 * $div[] = 'end text';
 * </code>
 * The output of <code>$div->render()</code> will be:
 * <code>
 * <div class='some class another class'>some content<b class='bold'>bold text</b>end text</div>
 * </code>
 *
 * CONTENT MANIPULATION
 *
 * Content can be manipulated:
 * <code>
 * foreach ($div as $key => $content) {
 *      unset($div[$key]);
 * }
 * $div->render()
 *  =>
 * <div class='some class another class' />
 * </code>
 *
 * OUTPUT ESCAPING
 *
 * All string input is escaped using the static escape function of the Html class
 * <code>
 * $div = Html::create()->div('<b>content</b>');
 * $div[] = ' <br/> ';
 * $div[] = '<i>content</i>';
 * $div->render()
 *  =>
 * <div>&lt;b&gt;content&lt;/b&gt; &lt;br/&gt; &lt;i&gt;content&lt;/i&gt;</div>
 * </code>
 * To prevent output escaping and add raw Html contant use the \Zalt\Html\Raw
 * class by creating an instance or invoking \Zalt\Html\Html::raw() or $this->raw().
 * This example shows all three approaches in the first three lines:
 * <code>
 * $div = Html::create()->div(new Raw('<b>content</b>'));
 * $div[] = Html::raw(' <br/> ');
 * $div-raw('<i>content</i>');
 * $div->render()
 *  =>
 * <div><b>content</b> <br/> <i>content</i></div>
 * </code>
 * Again use of the $div->raw() function is preferred.
 *
 *
 * SPECIAL TYPES
 *
 * Certain types of elements are special for an HtmlElement, they have special
 * set() functions. The current example of this is:
 * <code>
 *   \Zalt\Late\RepeatableInterface => setRepeater
 * </code>
 * The following 4 examples show how you can create a repeatablediv
 * <code>
 * 1: $div = Html::create()->div(array('repeater' => $repeater));
 * 2: $div = Html::create()->div($repeater);
 * 3: $div['repeater'] = $repeater;
 * 4: $div[] = $repeater;
 * 5: $div->repeater = $repeater;
 * 6: $div->x = repeater;
 * 7: $div->setRepeater($repeater);
 * </code>
 * Use option 7 for readablity, unless there is reason to use another method.
 *
 * Options 2, 4 and 6 will only work when $repeater really is an instance of \Zalt\Late\RepeatableInterface
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 *
 * @method     AElement               a(...$arguments)
 * @method     Sequence               array(...$arguments)
 * @method     HtmlElement            br(...$arguments)
 * @method     ColElement             col(...$arguments)
 * @method     ColGroupElement        colgroup(...$arguments)
 * @method     HtmlElement            div(...$arguments)
 * @method     DlElement              dd(...$arguments)
 * @method     DlElement              dl(...$arguments)
 * @method     DlElement              dt(...$arguments)
 * @method     HtmlElement            em(...$arguments)
 * @method     HtmlElement            i(...$arguments)
 * @method     LateCall               if(...$arguments)
 * @method     IFrame                 iframe(...$arguments)
 * @method     HnElement              h1(...$arguments)
 * @method     HnElement              h2(...$arguments)
 * @method     HnElement              h3(...$arguments)
 * @method     HnElement              h4(...$arguments)
 * @method     HnElement              h5(...$arguments)
 * @method     HnElement              h6(...$arguments)
 * @method     HtmlElement            hr(...$arguments)
 * @method     ImgElement             img(...$arguments)
 * @method     ImgElement             image(...$arguments)
 * @method     ZendLabelElement       label(...$arguments)
 * @method     HtmlElement            li(...$arguments)
 * @method     ListElement            ol(...$arguments)
 * @method     HtmlElement            p(...$arguments)
 * @method     HtmlElement            pInfo(...$arguments)
 * @method     Raw                    raw(...$arguments)
 * @method     Sequence               seq(...$arguments)
 * @method     Sequence               sequence(...$arguments)
 * @method     HtmlElement            small(...$arguments)
 * @method     SnippetInterface|null  snippet(...$arguments)
 * @method     Sequence               spaced(...$arguments)
 * @method     HtmlElement            span(...$arguments)
 * @method     Sprintf                sprintf(...$arguments)
 * @method     HtmlElement            strong(...$arguments)
 * @method     TableElement           table(...$arguments)
 * @method     TBodyElement           tbody(...$arguments)
 * @method     TdElement              td(...$arguments)
 * @method     TBodyElement           tfoot(...$arguments)
 * @method     TdElement              th(...$arguments)
 * @method     TBodyElement           thead(...$arguments)
 * @method     ListElement            ul(...$arguments)
 */
class HtmlElement implements ElementInterface, Procrastinator
{
    /**
     * For some elements (e.g. table and tbody) the logical thing to do when content
     * is added that does not have an $_allowedChildTags is to add that content to
     * the last item (i.e. row: tr) instead of adding a new row to the table or element.
     *
     * This is different from the standard behaviour: if you add a non-li item to an ul
     * item it is added in a new li item.
     *
     * @see $_allowedChildTags
     * @see $_lastChild
     *
     * @var boolean When true new content not having a $_allowedChildTags is added to $_lastChild.
     */
    protected $_addtoLastChild = false;

    /**
     * In some elements only certain elements are allowed as content. By specifying
     * $_allowedChildTags the element automatically ensures this is the case.
     *
     * Examples of such elements are:
     *   colgroup => col
     *   dl => dt, dd
     *   ol => li
     *   table => caption, colgroups, tbody, tfoot, thead
     *   tbody, tfoot, thead => tr
     *   tr => td, th
     *   ul => li
     *
     * At construction the $_defaultChildTag of the object is added (when needed) to
     * the $_allowedChildTags.
     *
     * When content is added it is checked against the $_allowedChildTags.
     *  - for \Zalt\Html\ElementInterface items the $tagName is extracted
     *    (when that tagname is late we assume the programmer knows what he is doing)
     *  - for \Zalt\Html\Raw elements we try to extract the tagname
     *
     * When the tagname of the child is not in the $_allowedChildTags a new
     * $_defaultChildTag element is created, unless $_addtoLastChild is true
     * and a $_lastChild does exist.
     *
     * @see $_addtoLastChild
     * @see $_defaultChildTag
     * @see $_lastChild
     *
     * @var string|array A string or array of string values of the allowed element tags.
     */
    protected $_allowedChildTags;

    /**
     * Usually no text is appended after an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable as source.
     *
     * @var string Content added after the element.
     */
    protected $_appendString = '';

    /**
     * @var array The actual storage of the attributes.
     */
    protected $_attribs = array();

    /**
     * The tag closing bracket
     *
     * @var string
     */
    protected $_closingBracket = null;

    /**
     * @var array The actual storage of the content.
     */
    protected $_content = array();

    /**
     * Allows the addition of any string content to an attribute with the name specified.
     *
     * E.g. in the ImgElement all content is added to the 'alt' attribute.
     *
     * @var boolean|string When not false, content is not used as element content, but added to the attribute
     */
    protected $_contentToTag = false;

    /**
     * When content must contain certain element types only the default child tag contains
     * the tagname of the element that is created to contain the content.
     *
     * When not in $_allowedChildTags the value is added to it in __construct().
     *
     * When empty set to the first value of $_allowedChildTags (if any) in __construct().
     *
     * @see $_allowedChildTags
     *
     * @var string The tagname of the element that should be created for content not having an $_allowedChildTags.
     */
    protected $_defaultChildTag;

    /**
     * The last child element added.
     *
     * Used when content must contain certain element types only and $_addtoLastChild is true.
     *
     * @see $_addtoLastChild
     * @see $_allowedChildTags
     * @see $_lastChild
     *
     * @var mixed Often an instance of \Zalt\Html\HtmlElement but can contain any content that was recently added.
     */
    protected $_lastChild;

    /**
     * Cache for Late object version of this element.
     *
     * @var null|\Zalt\Late\ObjectWrap
     */
    protected $_late = null;

    /**
     * The content to display when there is no other data to display when rendering.
     *
     * The reason for there being nothing to display can be that the $_repeater contains
     * no data. But another reason might be that there is simply nothing to display e.g.
     * because of conditional statements.
     * <code>
     * $div = \Zalt\Html\Html::create()->div();
     * if (isset($data['short_description])) {
     *   $div->p($data['short_description]);
     * }
     * if (isset($data['long_description])) {
     *   $div->p($data['long_description]);
     * }
     * $div->setOnEmpty(\Zalt\\HtmlHtml::create()->p('We do not yet have a description for this item.'));
     * </code>
     *
     * When asking for the content an empty \Zalt\Html\Sequence is returned, so the last line can
     * be simplified to:
     * <code>
     * $div->getOnEmpty()->p('We do not yet have a description for this item.');
     * </code>
     *
     * @see $_repeater;
     * @see setOnEmpty()
     *
     * @var mixed Content to display when the $_repeater contains no data.
     */
    protected $_onEmptyContent;

    /**
     * Usually no text is appended before an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable as source output.
     *
     * @var string Content added before the element.
     */
    protected $_prependString = '';

    /**
     * The traditional method of outputting repeated data is added the items to the output
     * element in a loop. E.g.:
     * <code>
     * $ul = new \Zalt\Html\ListElement('ul');
     * foreach ($data as $row) {
     *   $ul->li($row['title'], array('class' => $row['class']));
     * }
     * </code>
     *
     * The \Zalt Html sub package allows an alternate method of specifying this, eliminating
     * the loop:
     * <code>
     * $ul  = new \Zalt\Html\ListElement('ul');
     * $ul->setRepeater($data);
     *
     * $rep = $ul->getRepeater();
     * $ul->li($rep->title, array('class' => $rep['class']));
     * </code>
     * Both the property and the array notation can be used to access data in the repeater,
     * but the propery version is preferred.
     *
     * By default only the contents of the element is repeated, not the tags themselves.
     * Setting _repeatTags to true will repeat the whole element including the tags. This
     * example repeats the 'li' items including their tags instead of the contents of the
     * 'ul' element without their tags.
     * <code>
     * $ul  = new \Zalt\Html\ListElement('ul');
     * $rep = new \Zalt\Late\Repeatable($data);
     * $ul->li($rep->title, array('class' => $rep->class, 'repeater' => $repeater, 'repeatTags' => true));
     * </code>
     * As long as the 'ul' element contains only a single 'li' the resulting output is the same.
     *
     * If there is no data in the input, e.g. when $data is an empty array, the tradition loop requires
     * an extra if:
     * <code>
     * if ($data) {
     *   $ul = new \Zalt\Html\ListElement('ul');
     *   foreach ($data as $row) {
     *     $ul->li($row['title'], array('class' => $row['class']));
     *   }
     * }
     * </code>
     * Using a \Zalt\Html\ListElement there is no need to do anything, as $renderWithoutContent is set to
     * true in that subclass. The result is that there is no output for the element when there is no content
     * to output. However the default for \Zalt\Html\HtmlElement's is to output the tags even when there is
     * no content, so when you used the default element you need to set $renderWithoutContent to false to
     * get the correct behaviour.
     * <code>
     * $ul  = new \Zalt\Html\HtmlElement('ul');
     * $rep = new \Zalt\Late\Repeatable($data)
     * $ul->setRepeater($rep);
     * $ul->renderWithoutContent = false;
     * $ul->li($rep->title, array('class' => $rep->class));
     * </code>
     *
     * When outputting an alternative value when the array is empty like this:
     * <code>
     * $ul = new \Zalt\Html\ListElement('ul');
     * if ($data) {
     *   foreach ($data as $row) {
     *     $ul->li($row['title'], array('class' => $row['class']));
     *   }
     * } else {
     *   $ul->li('No data');
     * }
     * </code>
     * You can use setOnEmpty() to achieve the same.
     * <code>
     * $ul  = new \Zalt\Html\ListElement('ul');
     * $rep = new \Zalt\Late\Repeatable($data)
     * $ul->setRepeater($rep);
     * $ul->setOnEmpty('No data');
     * $ul->li($rep->title, array('class' => $rep->class));
     * </code>
     * The ListElement will autmatically put the 'No data' text in its own 'li'
     * element. Use getOnEmpty() to change it:
     * <code>
     * $ul->getOnEmpty()->class = 'disabled';
     * </code>
     *
     *
     * @see $_onEmptyContent
     * @see $_repeatTags
     * @see $renderWithoutContent
     * @see setOnEmpty()
     * @see setRepeater()
     * @see setRepeatTags()
     * @see RepeatableInterface
     *
     * @var ?RepeatableInterface
     */
    protected ?RepeatableInterface $_repeater = null;

    /**
     * When repeatTags is false (the default) only the content is repeated but
     * not the element tags. When repeatTags is true the both the tags and the
     * content are repeated.
     *
     * @see $_repeater
     *
     * @var boolean The repeatTags switch, default false.
     */
    protected $_repeatTags = false;

    /**
     * Extra array with special types for subclasses.
     *
     * When an object of one of the key types is used, then use
     * the class method defined as the value.
     *
     * @see $_specialTypesDefault
     *
     * @var array Of 'class or interfacename' => 'class method' of null
     */
    protected $_specialTypes;

    /**
     * When an object of one of the key types is used, then use
     * the class method defined as the value.
     *
     * @var array Of 'class or interfacename' => 'class method'
     */
    private $_specialTypesDefault = [
        RepeatableInterface::class => 'setRepeater',
        ];

    /**
     * Some elements, e.g. iframe elements, must always be rendered with a closing
     * tag because otherwise some poor browsers get confused.
     *
     * Overrules $renderWithoutContent: the element is always rendered when
     * $renderClosingTag is true.
     *
     * @see $renderWithoutContent
     *
     * @var boolean The element is always rendered with a closing tag.
     */
    public $renderClosingTag = false;

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
     * The tagName is always set at element creation, but can be changed
     * later on.
     *
     * The tagName must be a string value of an object with a __toString
     * function. (This includes Late objects.)
     *
     * @var string The tagname
     */
    public $tagName;

    /**
     * Adds an HtmlElement to this element
     *
     * @see \Zalt\Html\Creator
     *
     * @param string $name Function name becomes tagname (unless specified otherwise in \Zalt\Html\Creator)
     * @param array $arguments The content and attributes values
     * @return \Zalt\Html\ElementInterface With '$name' tagName
     */
    public function __call($name, array $arguments)
    {
        $elem = Html::createArray($name, $arguments);

        $this->append($elem);

        return $elem;
    }

    /**
     * Make an element with the specified tag name.
     *
     * Any extra parameters are added as either content, attributes or handled
     * as special types, if defined as such for this element.
     *
     * @param string $tagName
     * @param mixed $args Ra::args arguments
     */
    public function __construct($tagName, ...$args)
    {
        $args = Ra::args($args);

        $this->tagName = $tagName;

        if ($this->_specialTypes) {
            $this->_specialTypes = $this->_specialTypes + $this->_specialTypesDefault;
        } else {
            $this->_specialTypes = $this->_specialTypesDefault;
        }

        if ($this->_allowedChildTags || $this->_defaultChildTag) {
            // These variables influence each other, make sure they match
            if ($this->_allowedChildTags) {
                if (! is_array($this->_allowedChildTags)) {
                    $this->_allowedChildTags = array($this->_allowedChildTags);
                }
            } else {
                $this->_allowedChildTags = array();
            }

            if ($this->_defaultChildTag) {
                if (! in_array($this->_defaultChildTag, $this->_allowedChildTags)) {
                    $this->_allowedChildTags[] = $this->_defaultChildTag;
                }
            } else {
                // Get the first
                $this->_defaultChildTag = reset($this->_allowedChildTags);
            }
        }

        $this->_processParameters($args);
    }

    /**
     * Returns the attribute, if it exists.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attribs)) {
            return $this->_attribs[$name];
        }
    }

    /**
     * Does this attribute exist
     *
     * @param string $name
     * @return boolean
     */
    public function __isset ($name)
    {
        return array_key_exists($name, $this->_attribs);
    }

    /**
     * Set an attribite; except when the $value/$name is a special type
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if ($this->_notSpecialType($value, $name)) {
            if (is_array($value)) {
                $this->_attribs[$name] = Html::createAttribute($name, $value);
            } else {
                $this->_attribs[$name] = $value;
            }
        }
    }

    /**
     * Renders the object 
     *
     * Otherwise a warning is returned as it is not a good idea to throw
     * exceptions in a __toString() function.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Remove an attribute
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->_attribs[$name]);
    }

    /**
     * Create an default element for content.
     *
     * Some elements put their content in a fixed sub element, e.g. table uses tbody,
     * tbody uses tr and tr uses td or th.
     *
     * @param mixed $value
     * @param string $offset or null
     * @return \Zalt\Html\HtmlElement
     */
    protected function _createDefaultTag($value, $offset = null)
    {
        if (null === $offset) {
            return Html::create($this->_defaultChildTag, $value);
        } else {
            return Html::create($this->_defaultChildTag, array($offset => $value));
        }
    }

    /**
     * Make sure a default child tag element exists.
     */
    protected function _ensureDefaultTag()
    {
        if ($this->_defaultChildTag && (! $this->_content)) {
            $value = Html::create($this->_defaultChildTag);
            $this->_lastChild = $value;
            $this->_content[] = $value;
        }
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function _htmlAttribs($attribs)
    {
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            $key = Html::escape($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    $val = \json_encode($val);
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
                if (null == $val) {
                    $val = '';
                } else {
                    $val = Html::escape($val);
                }
            }

            if ('id' == $key) {
                $val = $this->_normalizeId($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }
        return $xhtml;
    }

    /**
     * Normalize an ID
     *
     * @param  string $value
     * @return string
     */
    protected function _normalizeId($value)
    {
        if (strstr($value, '[')) {
            if ('[]' == substr($value, -2)) {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }
        return $value;
    }

    /**
     * Returns true if this element is not allowed as a child element.
     *
     * @param mixed $element
     * @return boolean
     */
    private function _notAllowedChild($element)
    {
        if ($this->_allowedChildTags) {
            if ($element instanceof LateInterface) {
                // When a late object is passed we assume that the programnmer
                // had the sense to pass an object that devolves to an element
                // with allowed child tag.
                return false;
            }

            if ($tagName = self::extractTagName($element, '::')) {
                return ! in_array($tagName, $this->_allowedChildTags);
            }

            // When it does not have a tag name, it is not an allowed child element.
            return true;
        } else {
            // When no allowed child tags exist, this function is never true;
            return false;
        }
    }

    /**
     * Certain types must always be processed in a special manner.
     * This is independent of whether the type is passed as an
     * attribute or element content.
     *
     * @param $value mixed The value to check
     * @param $key optional The key used to add the value.
     * @return true|false True if nothing was done, false if the $value was processed.
     */
    private function _notSpecialType($value, $key = null)
    {
        if ($key && (! is_numeric($key))) {
            if (method_exists($this, $fname = 'set' . $key)) {
                if (is_array($value)) {
                    call_user_func_array(array($this, $fname), $value);
                } else {
                    $this->$fname($value);
                }

                return false;
            }
        }

        foreach ($this->_specialTypes as $class => $method) {
            if ($value instanceof $class) {
                $this->$method($value, $key);

                return false;
            }
        }

        return true;
    }

    /**
     * Checks the constructor parameters
     *
     * Special types are added as such. The same goes for element
     * and attribute objects.
     *
     * Other items with a null or integer key are added to the content
     * while named items are added as attributes.
     *
     * @param array $params
     */
    protected function _processParameters(array $params)
    {
        foreach ($params as $key => $param) {
            if ($this->_notSpecialType($param, $key)) {

                if ($param instanceof ElementInterface) {
                    $this->offsetSet($key, $param);

                } elseif ($param instanceof AttributeInterface) {
                    $key = $param->getAttributeName();
                    $this->$key = $param;

                } elseif (is_int($key)) {
                    $this->offsetSet($key, $param);

                } else {
                    $this->$key = $param;
                }
            }
        }
    }

    /**
     * Render the attribute values
     *
     * @return array With rendered versions of the attributes
     */
    private function _renderAttributes()
    {
        return Html::getRenderer()->renderArray($this->_attribs, false);
    }

    /**
     * Allows an extra repeater to be added to this object.
     *
     * If this is the first repeater this acts no different from setRepeater,
     * but when a repeater already exist a ParallelRepeater is created and
     * the repeaters are repeated through in parallel.
     *
     * @param mixed $repeater
     * @param string $name
     * @return RepeatableInterface
     */
    public function addRepeater($repeater, $name = null)
    {
        if (! $repeater instanceof RepeatableInterface) {
             $repeater = new Repeatable($repeater);
        }

        if ($name || $this->_repeater) {
            if (! $this->_repeater instanceof ParallelRepeater) {
                $this->_repeater = new ParallelRepeater($this->_repeater);
            }
            $this->_repeater->addRepeater($repeater, $name);
        } else {
            $this->_repeater = $repeater;
        }

        return $repeater;
    }

    /**
     * Checks for the child in question to have one of the tagnames in the guards array
     *
     * @param mixed $child
     * @param array $guards
     * @return boolean
     */
    public static function alreadyIsA($child, array $guards)
    {
        $tagName = self::extractTagName($child);

        return in_array($tagName, $guards);
    }

    /**
     * Add stuff to this element
     *
     * When it is a special type it is treated as such, otherwise the
     * value is appended to the content.
     *
     * @param mixed $value The value to append
     * @return \Zalt\Html\HtmlElement
     */
    public function append($value = null)
    {
        foreach (func_get_args() as $val) {
            $this->offsetSet(null, $val);
        }

        return $this;
    }

    /**
     * Appends the value to an existing attribute or creates the
     * attribute if it does not yet exist.
     *
     * @param string $name
     * @param mixed $value
     * @param string $offset Optional offset for adding
     * @return \Zalt\Html\HtmlElement
     */
    public function appendAttrib($name, $value, $offset = null)
    {
        $attrib = $this->$name;

        if ($attrib instanceof LateInterface) {
            $attrib = new ArrayAttribute($name, $attrib);

        } elseif ($attrib && ($value instanceof LateInterface)) {
            if (! $attrib instanceof AttributeInterface) {
                $attrib = new ArrayAttribute($name, $attrib);
            }
        }

        if ($attrib instanceof AttributeInterface) {
            $attrib->add($value);

        } elseif (is_array($attrib) || ($attrib instanceof \ArrayAccess)) {
            if (null !== $offset) {
                $attrib[$offset] = $value;

            } elseif (! in_array($value, $attrib)) {
                // Prevent double adding to attributes
                $attrib[] = $value;
            }

        } elseif ($attrib) {
            $attrib .= ' ' . $value;

        } else {
            $attrib = $value;

        }

        $this->$name = $attrib;

        return $this;
    }

    /**
     * The number of items in the content
     * @return int
     */
    public function count(): int
    {
        return count($this->_content);
    }

    /**
     * Returns the tagname from a \Zalt\Html\ElementInterface or a string or raw object
     * @param mixed $element
     * @param string $defaultName
     * @return string
     */
    public static function extractTagName($element, $defaultName = null)
    {
        if ($element instanceof HtmlInterface) {
            if ($element instanceof ElementInterface) {
                if ($tagname = $element->getTagName()) {
                    return strtolower($tagname);
                } else {
                    return $defaultName;
                }
            }

        } else {
            if (is_string($element) && (strlen($element) > 2)) {
                if ('<' === $element[0]) {
                    if (preg_match('/^<([a-zA-Z]+)[\\s>]*/', $element, $matches)) {
                        return strtolower($matches[1]);
                    }
                }
            }
            return $defaultName;
        }

        // $defaultName is only returned when there actually is a tag.
        return '';
    }

    /**
     * Return an attribute or a property of this object
     *
     * @param string $name
     * @return mixed
     */
    public function getAttrib($name)
    {
        if (array_key_exists($name, $this->_attribs)) {
            return $this->_attribs[$name];
        } elseif (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    /**
     * When content must contain certain element types only the default child tag contains
     * the tagname of the element that is created to contain the content.
     *
     * @see $_defaultChildTag
     * @see setDefaultChildTag()
     *
     * @return string
     */
    public function getDefaultChildTag()
    {
        return $this->_defaultChildTag;
    }

    /**
     * Get the first child element.
     *
     * @param boolean $create A default child tag is created if the element does not exist and has a default child tag
     * @return ?\Zalt\Html\HtmlElement or another \Zalt\Html\HtmlInterface element
     */
    public function getFirst($create = false)
    {
        if ($create && (! $this->_content)) {
            $this->_ensureDefaultTag();
        }
        if ($this->_content) {
            return reset($this->_content);
        }
        return null;
    }

    /**
     * Return the content as an iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->_content);
    }

    /**
     * Get the last child element.
     *
     * @param boolean $create A default child tag is created if the element does not exist
     * @return ?\Zalt\Html\HtmlElement or another \Zalt\Html\HtmlInterface element
     */
    public function getLast($create = false)
    {
        if ($create && (! $this->_content)) {
            $this->_ensureDefaultTag();
        }
        if ($this->_content) {
            return end($this->_content);
        }
        return null;
    }

    /**
     * Get the content displayed by the item when it is empty during rendering.
     *
     * The reason for there being nothing to display can be that the $_repeater contains
     * no data. But another reason might be that there is simply nothing to display e.g.
     * because of conditional statements.
     *
     * @see setOnEmpty()
     *
     * @return mixed
     */
    public function getOnEmpty()
    {
        if (! $this->_onEmptyContent) {
            // To add to on the usual $x->getOnEmpty()->p('Text') manner
            $this->setOnEmpty(new Sequence());
        }

        return $this->_onEmptyContent;
    }

    /**
     *
     * @return RepeatableInterface
     */
    public function getRepeater()
    {
        return $this->_repeater;
    }

    /**
     * When repeatTags is false (the default) only the content is repeated but
     * not the element tags. When repeatTags is true the both the tags and the
     * content are repeated.
     *
     * @return boolean The repeatTags switch.
     */
    public function getRepeatTags()
    {
        return $this->_repeatTags;
    }

    /**
     * The element tag
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }

    /**
     *
     * @return bool
     */
    public function hasRepeater(): bool
    {
        return (bool) $this->_repeater;
    }

    /**
     * Does a specific item exist in the content
     *
     * @param scalar $offset
     * @return boolean
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->_content);
    }

    /**
     * Return a specific item of the content
     *
     * @param scalar $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (array_key_exists($offset, $this->_content)) {
            return $this->_content[$offset];
        }
        error_log(sprintf("Non existing HtmlElement ofhset %s requested for tag %s using url %s.", $offset, $this->tagName, (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli')));
        return null;
    }

    /**
     * Adds $value to the content, unless the $$value / $offset
     * combination is a special type like e.g. a repeater. In
     * that case the element is added to the proper variable
     * instead of to the content.
     *
     * The content can be wrapped in another HtmlElement if the
     * rules of this object demand this, e.g. by requiring all
     * content to be of a certain element type.
     *
     * @param scalar|null $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->_notSpecialType($value, $offset)) {
            if ($this->_contentToTag) {
                $this->appendAttrib($this->_contentToTag, $value);

            } else {
                if ($this->_defaultChildTag && $this->_notAllowedChild($value)) {

                    if ($this->_addtoLastChild && $this->_lastChild) {
                        if (null === $offset) {
                            $this->_lastChild[] = $value;
                        } else {
                            $this->_lastChild[$offset] = $value;
                        }
                        return;
                    }

                    $value = $this->_createDefaultTag($value, $offset);
                }

                // Set as last child when this is a new item only.
                if (! isset($this->_content[$offset])) {
                    $this->_lastChild = $value;
                }

                if (null === $offset) {
                    $this->_content[] = $value;
                } else {
                    $this->_content[$offset] = $value;
                }
            }
        }
    }

    /**
     * Removes $offset from content of the element
     *
     * @param scalar $offset
     */
    public function offsetUnset(mixed $offset): void
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
        if ($this->_repeater &&
                $this->_repeatTags &&
                $this->_repeater->__start()) {

            $html = null;
            while ($this->_repeater->__next()) {
                $html .= $this->renderElement();
            }

            return $html;

        } else {
            return $this->renderElement();
        }
    }

    /**
     * Function to allow overloading of content rendering only
     *
     * @return string Correctly encoded and escaped html output
     */
    protected function renderContent()
    {
        $renderer = Html::getRenderer();
        if ($this->_content) {
            if ($this->_repeater && (! $this->_repeatTags)) {
                if ($this->_repeater->__start()) {
                    $html = '';
                    while ($this->_repeater->__next()) {
                        $html .= $renderer->renderArray($this->_content);
                    }

                    return $html;
                }

            } else {
                $content = $renderer->renderArray($this->_content);
                if (strlen($content)) {
                    return $content;
                }
            }
        }

        if ($this->_onEmptyContent) {
            return $renderer->renderArray($this->_onEmptyContent);
        }

        return '';
    }

    /**
     * Function to allow overloading  of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement()
    {
        $content     = $this->renderContent();
        $has_content = ('' !== $content);

        if ($has_content || $this->renderWithoutContent || $this->renderClosingTag) {

            $html = '<' . $this->tagName;

            if ($this->_attribs) {
                $html .= $this->_htmlAttribs($this->_renderAttributes());
            }

            if ($has_content || $this->renderClosingTag) {
                $html .= '>';

                $html .= $content;

                $html .= '</' . $this->tagName . '>';

            } else {
                $html .= ' />';
            }

            return $this->_prependString . $html . $this->_appendString;
        }

        return '';
    }

    /**
     * Set this elements attribute value
     *
     * @param string $name
     * @param mixed $value
     * @return \Zalt\Html\HtmlElement  (continuation pattern)
     */
    public function setAttrib($name, $value)
    {
        $this->$name = $value;
        return $this;
    }

    /**
     * When content must contain certain element types only the default child tag contains
     * the tagname of the element that is created to contain the content.
     *
     * @see $_defaultChildTag
     *
     * @param string $tag Tagname
     * @return \Zalt\Html\HtmlElement (continuation pattern)
     */
    public function setDefaultChildTag($tag)
    {
        $this->_defaultChildTag = $tag;
        return $this;
    }

    /**
     * Set the content displayed by the item when it is empty during rendering.
     *
     * The reason for there being nothing to display can be that the $_repeater contains
     * no data. But another reason might be that there is simply nothing to display e.g.
     * because of conditional statements.
     * <code>
     * $div = \Zalt\Html\Html::create()->div();
     * if (isset($data['short_description])) {
     *   $div->p($data['short_description]);
     * }
     * if (isset($data['long_description])) {
     *   $div->p($data['long_description]);
     * }
     * $div->setOnEmpty(\Zalt\Html\Html::create()->p('We do not yet have a description for this item.'));
     * </code>
     *
     * Some subclasses require their content to be a HtmlElement of a certain type. If the content
     * is not of that type, then it is automatically put in an element with $_defaultChildTag as
     * $tagName.
     *
     * @see $_defaultChildTag
     * @see $_onEmptyContent;
     * @see $_repeater;
     * @see getOnEmpty()
     *
     * @param mixed $content Content that can be rendered.
     * @return \Zalt\Html\HtmlElement (continuation pattern)
     */
    public function setOnEmpty($content)
    {
        if ($this->_defaultChildTag && $this->_notAllowedChild($content)) {
            $content = Html::create($this->_defaultChildTag, $content);
        }

        $this->_onEmptyContent = $content;
        return $this;
    }

    /**
     * Repeat the element when rendering.
     *
     * When repeatTags is false (the default) only the content is repeated but
     * not the element tags. When repeatTags is true the both the tags and the
     * content are repeated.
     *
     * @param mixed $repeater RepeatableInterface or something that can be made into one.
     * @param mixed $onEmptyContent Optional. When not null the content to display when the repeater does not result in data is set.
     * @param boolean $repeatTags Optional when not null the repeatTags switch is set.
     * @return \Zalt\Html\HtmlElement (continuation pattern)
     */
    public function setRepeater($repeater, $onEmptyContent = null, $repeatTags = null)
    {
        if ($repeater instanceof RepeatableInterface) {
            $this->_repeater = $repeater;
        } else {
            $this->_repeater = new Repeatable($repeater);
        }

        if (null !== $onEmptyContent) {
            $this->setOnEmpty($onEmptyContent);
        }

        if (null !== $repeatTags) {
            $this->setRepeatTags($repeatTags);
        }

        return $this;
    }

    /**
     * When repeatTags is false (the default) only the content is repeated but
     * not the element tags. When repeatTags is true the both the tags and the
     * content are repeated.
     *
     * @param boolean $repeatTags Set the repeatTags switch.
     * @return \Zalt\Html\HtmlElement (continuation pattern)
     */
    public function setRepeatTags($repeatTags)
    {
        $this->_repeatTags = $repeatTags;
        return $this;
    }

    /**
     * Returns a late instance of item. Do NOT use \Zalt\Late\Late::L() in this function as that will turn on array object!
     *
     * @return ObjectWrap
     */
    public function toLate()
    {
        if (! $this->_late) {
            $this->_late = new ObjectWrap($this);
        }

        return $this->_late;
    }
}
