<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\ModelBridge;

use Zalt\Html\AElement;
use Zalt\Model\Bridge\BridgeAbstract;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Ra\MultiWrapper;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @since      Class available since version 1.0
 */
class TableBridge extends TableBridgeAbstract
{
    protected array $baseUrl = [];

    /**
     * @var string link class to sort ascending
     */
    protected string $sortAscClass     = 'sortAsc';

    /**
     * @var string link class for element sorted ascending
     */
    protected string $sortAscClassSel  = 'sortAscSelected';

    /**
     * @var string Parameter name for ascending sort from dataModel
     */
    protected string $sortAscParam;
    
    /**
     * @var string link class for element sorted ascending
     */
    protected string $sortDescClassSel = 'sortDescSelected';

    /**
     * @var string Parameter name for descending sort from dataModel
     */
    protected string $sortDescParam;

    /**
     * @var string Parameter name for current sort
     */
    protected string $sortKey;

    public function __construct(DataReaderInterface $dataModel, $elementArgs = null)
    {
        parent::__construct($dataModel, $elementArgs);
        
        $this->setSortData($dataModel);
    }
    
    public function add($name, $label = null, mixed $tdClass = '', mixed $thClass = '')
    {
        if (is_string($name)) {
            $name = $this->_checkName($name);
            
            $tdContent[] = $this->$name;
            if (! $label) {
                $label = $this->metaModel->get($name, $label);
            }
            $thContent[] = $this->getHeaderFormatted($name, $label);
        } else {
            $tdContent[] = $this->$name;
            $thContent[] = $label;
        }

        if ($this->metaModel->has($name, 'tdClass')) {
            $tdClass = array_filter([$tdClass, $this->metaModel->get($name, 'tdClass')]);    
        }
        if ($tdClass) {
            $tdContent['class'] = $tdClass;
        }
        if ($this->metaModel->has($name, 'thClass')) {
            $thClass = array_filter([$thClass, $this->metaModel->get($name, 'thClass')]);
        }
        if ($thClass) {
            $thContent['class'] = $thClass;
        }

        return $this->table->addColumn($tdContent, $thContent);
    }

    /**
     *
     * @param \Zalt\Html\AElement $link Or anything else to put a the column
     * @return \MUtil\MultiWrapper containing the column, header and footer cell
     */
    public function addItemLink(AElement $link)
    {
        $tds = $this->table->addColumnArray($link);
        $tbody = $tds[0];
        $tbody->class = 'table-button';

//        if ($this->useRowHref) {
//            if ($this->row_href) {
//                if ($link instanceof \Zalt\Html\HtmlElement) {
//                    $tds[0]->onclick = array('location.href=\'', $link->href, '\';');
//                } else {
//                    $tds[0]->onclick = '// Dummy on click';
//                }
//                $this->has_multi_refs = true;
//            } else {
//                if ($link instanceof \Zalt\Html\HtmlElement) {
//                    $this->row_href = $link->href;
//                }
//            }
//        }

        return new MultiWrapper($tds);
    }

    public function addSortable(string $name, ?string $label = null, mixed $tdClass = '', mixed $thClass = '')
    {
        $name = $this->_checkName($name);
        if (! $label) {
            $label = $this->metaModel->get($name, $label);
        }
        return $this->add($name, $this->createSortLink($name, $label), $tdClass, $thClass);
    }
    
    /**
     * Create a sort link for the given $name element using the $label if provided or the label from the model
     * when null
     *
     * @param string $name
     * @param mixed $label
     * @return \MUtil\Html\AElement
     */
    public function createSortLink(string $name, $label = null)
    {
        $name = $this->_checkName($name);
        
        if ($this->metaModel->get($name, 'noSort')) {
            return $label;
        }

        if (! $label) {
            $label = $this->metaModel->get($name, $label);
        }
        $label = $this->getHeaderFormatted($name, $label);

        $class      = $this->sortAscClass;
        $sortParam  = $this->sortAscParam;
        $nsortParam = $this->sortDescParam;

        if ($this->sortKey == $name) {
            if ($this->sortAsc) {
                $class      = $this->sortAscClassSel;
                $sortParam  = $this->sortDescParam;
                $nsortParam = $this->sortAscParam;
            } else {
                $class      = $this->sortDescClassSel;
            }
        }

        $sortUrl[$sortParam]  = $name;
        $sortUrl[$nsortParam] = null;  // Fix: no need for RouteReset if the link sets the other sort param to null
        $sortUrl = $sortUrl + $this->baseUrl;

        return \Zalt\Html\Html::create()->a($sortUrl, array('class' => $class, 'title' => $this->metaModel->get($name, 'description')), $label);
    }
}