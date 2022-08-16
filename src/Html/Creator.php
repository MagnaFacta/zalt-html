<?php

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 * Class for storing references for creating html attributes, elements and other objects.
 *
 * Basically this class stores list of element and attributes names that should be treated
 * in different from just creating the most basic of element types.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */

class Creator
{
    /**
     *
     * @var \MUtil\Util\LookupList
     */
    protected $_attributeFunctionList;

    /**
     *
     * @var \MUtil\Util\LookupList
     */
    protected $_elementFunctionList;

    /**
     *
     * @var array
     */
    protected $_initialAttributeFunctions = array(
        'href'    => '\\MUtil\\Html\\HrefArrayAttribute::hrefAttribute',
        'onclick' => '\\MUtil\\Html\\OnClickArrayAttribute::onclickAttribute',
        'src'     => '\\MUtil\\Html\\SrcArrayAttribute::srcAttribute',
        'style'   => '\\MUtil\\Html\\StyleArrayAttribute::styleAttribute',
    );

    /**
     *
     * @var array
     */
    protected $_initalElementFunctions = array(
        'a'                 => '\\MUtil\\Html\\AElement::a',
        'array'             => '\\MUtil\\Html\\Sequence::createSequence',
        'bbcode'            => '\\MUtil\\Html::bbcode',
        'call'              => '\\MUtil\\Lazy::call',
        'col'               => '\\MUtil\\Html\\ColElement::col',
        'colgroup'          => '\\MUtil\\Html\\ColGroupElement::colgroup',
        'dir'               => '\\MUtil\\Html\\ListElement::dir',
        'dd'                => '\\MUtil\\Html\\DdElement::dd',
        'dl'                => '\\MUtil\\Html\\DlElement::dl',
        'dt'                => '\\MUtil\\Html\\DtElement::dt',
        'echo'              => '\\MUtil\\Html\\TableElement::createVar',
        'email'             => '\\MUtil\\Html\\AElement::email',
        'h1'                => '\\MUtil\\Html\\HnElement::h1',
        'h2'                => '\\MUtil\\Html\\HnElement::h2',
        'h3'                => '\\MUtil\\Html\\HnElement::h3',
        'h4'                => '\\MUtil\\Html\\HnElement::h4',
        'h5'                => '\\MUtil\\Html\\HnElement::h5',
        'h6'                => '\\MUtil\\Html\\HnElement::h6',
        'if'                => '\\MUtil\\Lazy::iff',
        'iflink'            => '\\MUtil\\Html\\AElement::iflink',
        'ifmail'            => '\\MUtil\\Html\\AElement::ifmail',
        'iframe'            => '\\MUtil\\Html\\IFrame::iFrame',
        'img'               => '\\MUtil\\Html\\ImgElement::img',
        'image'             => '\\MUtil\\Html\\ImgElement::img',
        'input'             => '\\MUtil\\Html\\InputRenderer::input',
        'inputComplete'     => '\\MUtil\\Html\\InputRenderer::inputComplete',
        'inputDescription'  => '\\MUtil\\Html\\InputRenderer::inputDescription',
        'inputDisplayGroup' => '\\MUtil\\Html\\InputRenderer::inputDisplayGroup',
        'inputElement'      => '\\MUtil\\Html\\InputRenderer::inputElement',
        'inputErrors'       => '\\MUtil\\Html\\InputRenderer::inputErrors',
        'inputExcept'       => '\\MUtil\\Html\\InputRenderer::inputExcept',
        'inputForm'         => '\\MUtil\\Html\\InputRenderer::inputForm',
        'inputLabel'        => '\\MUtil\\Html\\LabelElement::label',
        'inputOnly'         => '\\MUtil\\Html\\InputRenderer::inputOnly',
        'inputOnlyArray'    => '\\MUtil\\Html\\InputRenderer::inputOnlyArray',
        'inputUntil'        => '\\MUtil\\Html\\InputRenderer::inputUntil',
        'inputUpto'         => '\\MUtil\\Html\\InputRenderer::inputUpto',
        'label'             => '\\MUtil\\Html\\LabelElement::label',
        'menu'              => '\\MUtil\\Html\\ListElement::menu',
        'ol'                => '\\MUtil\\Html\\ListElement::ol',
        'pagePanel'         => '\\MUtil\\Html\\PagePanel::pagePanel',
        'pForm'             => '\\MUtil\\Html\\PFormElement::pForm',
        'progress'          => '\\MUtil\\Html\\ProgressPanel::progress',
        'progressPanel'     => '\\MUtil\\Html\\ProgressPanel::progress',
        'raw'               => '\\MUtil\\Html\\Raw::raw',
        'seq'               => '\\MUtil\\Html\\Sequence::createSequence',
        'sequence'          => '\\MUtil\\Html\\Sequence::createSequence',   // A sequence can contain another sequence, so other function name used
        'snippet'           => '\\MUtil\\Html::snippet',
        'sprintf'           => '\\MUtil\\Html\\Sprintf::sprintf',
        'spaced'            => '\\MUtil\\Html\\Sequence::createSpaced',     // A sequence can contain another sequence, so other function name used
        'table'             => '\\MUtil\\Html\\TableElement::table',
        'tbody'             => '\\MUtil\\Html\\TBodyElement::tbody',
        'td'                => '\\MUtil\\Html\\TdElement::createTd',
        'tfoot'             => '\\MUtil\\Html\\TBodyElement::tfoot',
        'th'                => '\\MUtil\\Html\\TdElement::createTh',
        'thead'             => '\\MUtil\\Html\\TBodyElement::thead',
        'tr'                => '\\MUtil\\Html\\TrElement::tr',
        'ul'                => '\\MUtil\\Html\\ListElement::ul',
    );

    public function __call($name, array $arguments)
    {
        return $this->create($name, $arguments);
    }

    public function __construct($elementFunctions = null, $attributeFunctions = null, $append = true)
    {
        $this->setElementFunctionList($elementFunctions, $append);
        $this->setAttributeFunctionList($attributeFunctions, $append);
    }

    public function addAttributeFunction($name_1, $function_1, $name_n = null, $function_n = null)
    {
        $args = \MUtil\Ra::pairs(func_get_args());

        return $this->setAttributeFunctionList($args, true);
    }

    public function addElementFunction($name_1, $function_1, $name_n = null, $function_n = null)
    {
        $args = \MUtil\Ra::pairs(func_get_args());

        $this->setElementFunctionList($args, true);

        return $this;
    }

    public function create($tagName, array $args = array())
    {
        if ($function = $this->_elementFunctionList->get($tagName)) {
            return call_user_func_array($function, $args);

        } else {
            return new \MUtil\Html\HtmlElement($tagName, $args);
        }
    }

    public function createAttribute($attributeName, array $args = array())
    {
        if ($function = $this->_attributeFunctionList->get($attributeName)) {
            return call_user_func($function, $args);

        } else {
            return new \MUtil\Html\ArrayAttribute($attributeName, $args);

        }
    }

    public function createRaw($tagName, array $args = array())
    {
        return new \MUtil\Html\HtmlElement($tagName, $args);
    }

    public function getAttributeFunctionList()
    {
        return $this->_attributeFunctionList;
    }

    public function getElementFunctionList()
    {
        return $this->_elementFunctionList;
    }

    public function setAttributeFunctionList($attributeFunctions, $append = false)
    {
        if ($attributeFunctions instanceof \MUtil\Util\LookupList) {
            $this->_attributeFunctionList = $attributeFunctions;
        } else {
            $this->_attributeFunctionList = new \MUtil\Util\FunctionList($this->_initialAttributeFunctions);

            if ($attributeFunctions) {
                if ($append) {
                    $this->_attributeFunctionList->add((array) $attributeFunctions);
                } else {
                    $this->_attributeFunctionList->set((array) $attributeFunctions);
                }
            }
        }
        return $this;
    }

    public function setElementFunctionList($elementFunctions, $append = false)
    {
        if ($elementFunctions instanceof \MUtil\Util\LookupList) {
            $this->_elementFunctionList = $elementFunctions;
        } else {
            if (! $this->_elementFunctionList instanceof \MUtil\Util\FunctionList) {
                $this->_elementFunctionList = new \MUtil\Util\FunctionList($this->_initalElementFunctions);
            }

            if ($elementFunctions) {
                if ($append) {
                    $this->_elementFunctionList->add((array) $elementFunctions);
                } else {
                    $this->_elementFunctionList->set((array) $elementFunctions);
                }
            }
        }
        return $this;
    }
}
