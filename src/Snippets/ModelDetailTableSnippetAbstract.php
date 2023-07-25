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
use Zalt\Model\Bridge\BridgeInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Snippets\ModelBridge\DetailTableBridge;

/**
 * Displays each fields of a single item in a model in a row in a Html table.
 *
 * To use this class either subclass or use the existing default ModelDetailTableSnippet.
 *
 * @see        ModelDetailTableSnippet.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
abstract class ModelDetailTableSnippetAbstract extends ModelSnippetAbstract
{
    protected string $bridgeClass = 'itemTable';

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
    protected $bridgeMode = BridgeInterface::MODE_LAZY;

    protected mixed $onEmpty = null;

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
     * @param \Zalt\Snippets\ModelBridge\DetailTableBridge $bridge
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return void
     */
    protected function addShowTableRows(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    {
        $metaModel = $dataModel->getMetaModel();

        foreach($metaModel->getItemsOrdered() as $name) {
            if ($label = $metaModel->get($name, 'label')) {
                $bridge->addItem($name, $label);
            }
        }

        if ($metaModel->has('row_class')) {
            // Make sure deactivated rounds are shown as deleted
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
    public function getHtmlOutput()
    {
        $dataModel = $this->getModel();
        if ($this->trackUsage) {
            $dataModel->getMetaModel()->trackUsage();
        }

        $table = $this->getShowTable($dataModel);
        $this->applyHtmlAttributes($table);

        $container = Html::create()->div(array('class' => 'table-container', 'renderWithoutContent' => false));
        $container[] = $table;
        return $container;
    }

    /**
     * Function that allows for overruling the repeater loading.
     *
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return \Zalt\Late\RepeatableInterface
     */
    public function getRepeater(DataReaderInterface $dataModel)
    {
        return $dataModel->loadRepeatable();
    }

    /**
     * Creates from the model a \Zalt\Html\TableElement that can display multiple items.
     *
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return \Zalt\Html\TableElement
     */
    public function getShowTable(DataReaderInterface $dataModel)
    {
        $metaModel = $dataModel->getMetaModel();
        
        $bridge = $dataModel->getBridgeFor($this->bridgeClass);
        $bridge->setColumnCount($this->bridgeColumns)
                ->setMode($this->bridgeMode);

        if ($metaModel->hasDependencies()) {
            $this->bridgeMode = BridgeInterface::MODE_SINGLE_ROW;
        }
        if (BridgeInterface::MODE_SINGLE_ROW == $this->bridgeMode) {
            // Trigger the dependencies
            $bridge->getRow();
        }

        $this->setShowTableHeader($bridge, $dataModel);
        $this->setShowTableFooter($bridge, $dataModel);
        $this->addShowTableRows($bridge, $dataModel);

        if (! $bridge->hasRepeater()) {
            $bridge->setRepeater($this->getRepeater($dataModel));
        }
        if ($this->onEmpty) {
            $bridge->getTable()->setOnEmpty($this->onEmpty);
        }

        return $bridge->getTable();
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Snippets\ModelBridge\DetailTableBridge $bridge
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return void
     */
    protected function setShowTableFooter(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    { }

    /**
     * Set the header of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Snippets\ModelBridge\DetailTableBridge $bridge
     * @param \Zalt\Model\Data\DataReaderInterface $dataModel
     * @return void
     */
    protected function setShowTableHeader(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    { }
}
