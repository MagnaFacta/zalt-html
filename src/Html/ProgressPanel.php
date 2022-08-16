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
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ProgressPanel extends \MUtil\Html\HtmlElement
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
    protected $_addtoLastChild = true;

    /**
     * Usually no text is appended after an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable in source
     * view.
     *
     * @var string Content added after the element.
     */
    protected $_appendString = "\n";

    /**
     * Default attributes.
     *
     * @var array The actual storage of the attributes.
     */
    protected $_attribs = array(
        'class' => 'ui-progressbar ui-widget ui-widget-content ui-corner-all',
        'id' => 'progress_bar'
    );

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
    protected $_defaultChildTag = 'div';

    /**
     * Usually no text is appended before an element, but for certain elements we choose
     * to add a "\n" newline character instead, to keep the output readable in source
     * view.
     *
     * @var string Content added before the element.
     */
    protected $_prependString = "\n";

    /**
     * Class name of inner element that displays text
     *
     * @var string
     */
    public $progressTextClass = 'ui-progressbar-text';

    /**
     * Creates a 'div' progress panel
     *
     * @param mixed $arg_array A \MUtil\Ra::args data collection.
     */
    public function __construct($arg_array = null)
    {
        $args = \MUtil\Ra::args(func_get_args());

        parent::__construct('div', $args);
    }

    /**
     * Returns the tag name for the default child.
     *
     * Exposed as needed by some classes using this class.
     *
     * @see \MUtil\Batch\BatchAbstract
     *
     * @return string
     */
    public function getDefaultChildTag()
    {
        return $this->_defaultChildTag;
    }

    /**
     * Creates a 'div' progress panel
     *
     * @param mixed $arg_array A \MUtil\Ra::args data collection.
     * @return self
     */
    public static function progress($arg_array = null)
    {
        $args = func_get_args();
        return new self($args);
    }

    /**
     * Function to allow overloading of tag rendering only
     *
     * Renders the element tag with it's content into a html string
     *
     * The $view is used to correctly encode and escape the output
     *
     * @param \Zend_View_Abstract $view
     * @return string Correctly encoded and escaped html output
     */
    protected function renderElement(\Zend_View_Abstract $view)
    {
        if ($this->_lastChild) {
            $this->_lastChild->class = $this->progressTextClass;

            // These style elements inline because they are REQUIRED to make the panel work.
            //
            // Making the child position absolute means it is positioned over the content that
            // the JQuery progress widget displays (the bar itself) and so this solution allows
            // the text to be displayed over the progress bar (when it has a relative position).
            //
            // The elements should be display neutral.
            //
            $this->_lastChild->style = 'left: 0; height: 100%; position: absolute; top: 0; width: 100%;';
            $this->style = 'position: relative;';
        }

        return parent::renderElement($view);
    }
}
