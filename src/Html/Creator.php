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

use Zalt\Html\Zend\ZendPFormElement;
use Zalt\Html\Zend\ZendInputRenderer;
use Zalt\Html\Zend\ZendLabelElement;
use Zalt\Late\Late;
use Zalt\Late\LateCall;
use Zalt\Lists\FunctionList;
use Zalt\Lists\LookupList;
use Zalt\Ra\Ra;

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
 *
 * @method     AElement        a(...$arguments)
 * @method     HtmlElement     br(...$arguments)
 * @method     ColElement      col(...$arguments)
 * @method     ColGroupElement colgroup(...$arguments)
 * @method     HtmlElement     div(...$arguments)
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
        'input'             => [ZendInputRenderer::class, 'input'],
        'inputComplete'     => [ZendInputRenderer::class, 'inputComplete'],
        'inputDescription'  => [ZendInputRenderer::class, 'inputDescription'],
        'inputDisplayGroup' => [ZendInputRenderer::class, 'inputDisplayGroup'],
        'inputElement'      => [ZendInputRenderer::class, 'inputElement'],
        'inputErrors'       => [ZendInputRenderer::class, 'inputErrors'],
        'inputExcept'       => [ZendInputRenderer::class, 'inputExcept'],
        'inputForm'         => [ZendInputRenderer::class, 'inputForm'],
        'inputLabel'        => [ZendLabelElement::class, 'label'],
        'inputOnly'         => [ZendInputRenderer::class, 'inputOnly'],
        'inputOnlyArray'    => [ZendInputRenderer::class, 'inputOnlyArray'],
        'inputUntil'        => [ZendInputRenderer::class, 'inputUntil'],
        'inputUpto'         => [ZendInputRenderer::class, 'inputUpto'],
        'label'             => [ZendLabelElement::class, 'label'],
        'menu'              => [ListElement::class, 'menu'],
        'ol'                => [ListElement::class, 'ol'],
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
