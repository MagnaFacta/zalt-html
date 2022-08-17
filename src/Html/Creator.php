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

use Zalt\HtmlUtil\FunctionList;
use Zalt\HtmlUtil\LookupList;
use Zalt\Late\Late;
use Zalt\HtmlUtil\Ra;

/**
 * Class for storing references for creating html attributes, elements and other objects.
 *
 * Basically this class stores list of element and attributes names that should be treated
 * in different from just creating the most basic of element types.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */

class Creator
{
    /**
     *
     * @var LookupList
     */
    protected $_attributeFunctionList;

    /**
     *
     * @var LookupList
     */
    protected $_elementFunctionList;

    /**
     *
     * @var array
     */
    protected $_initialAttributeFunctions = array(
        'href'    => [HrefArrayAttribute::class, 'hrefAttribute'],
        'onclick' => [OnClickArrayAttribute::class, 'onclickAttribute'],
        'src'     => [SrcArrayAttribute::class, 'srcAttribute'],
        'style'   => [StyleArrayAttribute::class, 'styleAttribute'],
    );

    /**
     *
     * @var array
     */
    protected $_initalElementFunctions = array(
        'a'                 => [AElement::class, 'a'],
        'array'             => [Sequence::class, 'createSequence'],
        'call'              => [Late::class, 'call'],
        'col'               => [ColElement::class, 'col'],
        'colgroup'          => [ColGroupElement::class, 'colgroup'],
        'dir'               => [ListElement::class, 'dir'],
        'dd'                => [DdElement::class, 'dd'],
        'dl'                => [DlElement::class, 'dl'],
        'dt'                => [DtElement::class, 'dt'],
        'echo'              => [TableElement::class, 'createVar'],
        'email'             => [AElement::class, 'email'],
        'h1'                => [HnElement::class, 'h1'],
        'h2'                => [HnElement::class, 'h2'],
        'h3'                => [HnElement::class, 'h3'],
        'h4'                => [HnElement::class, 'h4'],
        'h5'                => [HnElement::class, 'h5'],
        'h6'                => [HnElement::class, 'h6'],
        'if'                => [Late::class, 'iff'],
        'iflink'            => [AElement::class, 'iflink'],
        'ifmail'            => [AElement::class, 'ifmail'],
        'iframe'            => [IFrame::class, 'iFrame'],
        'img'               => [ImgElement::class, 'img'],
        'image'             => [ImgElement::class, 'img'],
        'input'             => [InputRenderer::class, 'input'],
        'inputComplete'     => [InputRenderer::class, 'inputComplete'],
        'inputDescription'  => [InputRenderer::class, 'inputDescription'],
        'inputDisplayGroup' => [InputRenderer::class, 'inputDisplayGroup'],
        'inputElement'      => [InputRenderer::class, 'inputElement'],
        'inputErrors'       => [InputRenderer::class, 'inputErrors'],
        'inputExcept'       => [InputRenderer::class, 'inputExcept'],
        'inputForm'         => [InputRenderer::class, 'inputForm'],
        'inputLabel'        => [LabelElement::class, 'label'],
        'inputOnly'         => [InputRenderer::class, 'inputOnly'],
        'inputOnlyArray'    => [InputRenderer::class, 'inputOnlyArray'],
        'inputUntil'        => [InputRenderer::class, 'inputUntil'],
        'inputUpto'         => [InputRenderer::class, 'inputUpto'],
        'label'             => [LabelElement::class, 'label'],
        'menu'              => [ListElement::class, 'menu'],
        'ol'                => [ListElement::class, 'ol'],
        'pagePanel'         => [PagePanel::class, 'pagePanel'],
        'pForm'             => [PFormElement::class, 'pForm'],
        'progress'          => [ProgressPanel::class, 'progress'],
        'progressPanel'     => [ProgressPanel::class, 'progress'],
        'raw'               => [Raw::class, 'raw'],
        'seq'               => [Sequence::class, 'createSequence'],   // A sequence can contain another sequence, so other function name used
        'sequence'          => [Sequence::class, 'createSequence'],   // A sequence can contain another sequence, so other function name used
        'snippet'           => [Html::class, 'snippet'],
        'sprintf'           => [Sprintf::class, 'sprintf'],
        'spaced'            => [Sequence::class, 'createSpaced'],     // A sequence can contain another sequence, so other function name used
        'table'             => [TableElement::class, 'table'],
        'tbody'             => [TBodyElement::class, 'tbody'],
        'td'                => [TdElement::class, 'createTd'],
        'tfoot'             => [TBodyElement::class, 'tfoot'],
        'th'                => [TdElement::class, 'createTh'],
        'thead'             => [TBodyElement::class, 'thead'],
        'tr'                => [TrElement::class, 'tr'],
        'ul'                => [ListElement::class, 'ul'],
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

    public function addAttributeFunction($name1, $function1, $nameN = null, $functionN = null)
    {
        $args = Ra::pairs(func_get_args());

        return $this->setAttributeFunctionList($args, true);
    }

    public function addElementFunction($name1, $function1, $nameN = null, $functionN = null)
    {
        $args = Ra::pairs(func_get_args());

        $this->setElementFunctionList($args, true);

        return $this;
    }

    public function create($tagName, array $args = array())
    {
        if ($function = $this->_elementFunctionList->get($tagName)) {
            return call_user_func_array($function, $args);

        } else {
            return new HtmlElement($tagName, $args);
        }
    }

    public function createAttribute($attributeName, array $args = [])
    {
        if ($function = $this->_attributeFunctionList->get($attributeName)) {
            return call_user_func($function, $args);

        } else {
            return new ArrayAttribute($attributeName, $args);
        }
    }

    public function createRaw($tagName, array $args = array())
    {
        return new HtmlElement($tagName, $args);
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
        if ($attributeFunctions instanceof LookupList) {
            $this->_attributeFunctionList = $attributeFunctions;
        } else {
            $this->_attributeFunctionList = new FunctionList($this->_initialAttributeFunctions);

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
        if ($elementFunctions instanceof LookupList) {
            $this->_elementFunctionList = $elementFunctions;
        } else {
            if (! $this->_elementFunctionList instanceof FunctionList) {
                $this->_elementFunctionList = new FunctionList($this->_initalElementFunctions);
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
