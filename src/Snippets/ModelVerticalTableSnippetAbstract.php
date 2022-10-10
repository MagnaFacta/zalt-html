<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\Html;

/**
 * Displays each fields of a single item in a model in a row in a Html table.
 *
 * To use this class either subclass or use the existing default ModelVerticalTableSnippet.
 *
 * @see ModelVerticalTableSnippet.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
abstract class ModelVerticalTableSnippetAbstract extends \Zalt\Snippets\ModelSnippetAbstract
{
    /**
     *
     * @var int The number of columns used in the table bridge.
     */
    protected $bridgeColumns = 1;

    /**
     * One of the \Zalt\Model\Bridge\BridgeAbstract MODE constants
     *
     * @var int
     */
    protected $bridgeMode = \Zalt\Model\Bridge\BridgeAbstract::MODE_LAZY;

    /**
     * Shortfix to add class attribute
     *
     * @var string
     */
    protected $class;

    /**
     *
     * @var boolean True when only tracked fields should be retrieved by the nodel
     */
    protected $trackUsage = true;

    /**
     * Adds rows from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Model\Bridge\VerticalTableBridge $bridge
     * @param \Zalt\Model\ModelAbstract $model
     * @return void
     */
    protected function addShowTableRows(\Zalt\Model\Bridge\VerticalTableBridge $bridge, \Zalt\Model\ModelAbstract $model)
    {
        foreach($model->getItemsOrdered() as $name) {
            if ($label = $model->get($name, 'label')) {
                $bridge->addItem($name, $label);
            }
        }

        if ($model->has('row_class')) {
            // Make sure deactivated rounds are show as deleted
            foreach ($bridge->getTable()->tbody() as $tr) {
                foreach ($tr as $td) {
                    if ('td' === $td->tagName) {
                        $td->appendAttrib('class', $bridge->row_class);
                    }
                }
            }
        }
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \Zalt\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $model = $this->getModel();
        if ($this->trackUsage) {
            $model->trackUsage();
        }

        $table = $this->getShowTable($model);

        $container = Html::create()->div(array('class' => 'table-container', 'renderWithoutContent' => false));
        $container[] = $table;
        return $container;
    }

    /**
     * Function that allows for overruling the repeater loading.
     *
     * @param \Zalt\Model\ModelAbstract $model
     * @return \Zalt\Late\RepeatableInterface
     */
    public function getRepeater(\Zalt\Model\ModelAbstract $model)
    {
        return $model->loadRepeatable();
    }

    /**
     * Creates from the model a \Zalt\Html\TableElement that can display multiple items.
     *
     * Allows overruling
     *
     * @param \Zalt\Model\ModelAbstract $model
     * @return \Zalt\Html\TableElement
     */
    public function getShowTable(\Zalt\Model\ModelAbstract $model)
    {
        $bridge = $model->getBridgeFor('itemTable', array('class' => $this->class));
        $bridge->setColumnCount($this->bridgeColumns)
                ->setMode($this->bridgeMode);

        if ($model->hasDependencies()) {
            $this->bridgeMode = \Zalt\Model\Bridge\BridgeAbstract::MODE_SINGLE_ROW;
        }
        if (\Zalt\Model\Bridge\BridgeAbstract::MODE_SINGLE_ROW == $this->bridgeMode) {
            // Trigger the dependencies
            $bridge->getRow();
        }

        $this->setShowTableHeader($bridge, $model);
        $this->setShowTableFooter($bridge, $model);
        $this->addShowTableRows($bridge, $model);

        if (! $bridge->getRepeater()) {
            $bridge->setRepeater($this->getRepeater($model));
        }

        return $bridge->getTable();
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Model\Bridge\VerticalTableBridge $bridge
     * @param \Zalt\Model\ModelAbstract $model
     * @return void
     */
    protected function setShowTableFooter(\Zalt\Model\Bridge\VerticalTableBridge $bridge, \Zalt\Model\ModelAbstract $model)
    { }

    /**
     * Set the header of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Model\Bridge\VerticalTableBridge $bridge
     * @param \Zalt\Model\ModelAbstract $model
     * @return void
     */
    protected function setShowTableHeader(\Zalt\Model\Bridge\VerticalTableBridge $bridge, \Zalt\Model\ModelAbstract $model)
    { }
}
