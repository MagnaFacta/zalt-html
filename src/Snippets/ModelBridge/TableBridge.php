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
use Zalt\Html\Html;
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
    /**
     * @var bool What is the main direction of the sort
     */
    protected bool $sortAsc = true;

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
    public string $sortAscParam;
    
    /**
     * @var string link class for element sorted ascending
     */
    protected string $sortDescClassSel = 'sortDescSelected';

    /**
     * @var string Parameter name for descending sort from dataModel
     */
    public string $sortDescParam;

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

            if ($this->metaModel->has($name, 'tdClass')) {
                $tdClass = array_filter([$tdClass, $this->metaModel->get($name, 'tdClass')]);
            }
            if ($this->metaModel->has($name, 'thClass')) {
                $thClass = array_filter([$thClass, $this->metaModel->get($name, 'thClass')]);
            }
        } else {
            $tdContent[] = $name;
            $thContent[] = $label;
            
            $tdClass = null;
            $thClass = null;
        }

        if ($tdClass) {
            $tdContent['class'] = $tdClass;
        }
        if ($thClass) {
            $thContent['class'] = $thClass;
        }

        return $this->table->addColumn($tdContent, $thContent);
    }

    /**
     *
     * @param \Zalt\Html\AElement $link Or anything else to put a the column
     * @return \Zalt\Ra\MultiWrapper containing the column, header and footer cell
     */
    public function addItemLink(AElement $link)
    {
        $tds = $this->table->addColumnArray($link);
        $tbody = $tds[0];
        $tbody->class = 'table-button';

        return new MultiWrapper($tds);
    }

    public function addMultiSort(...$args)
    {
        $headers = null;
        $content = null;

        foreach ($args as $name) {
            if (is_string($name)) {
                $name = $this->_checkName($name);

                $headers[] = $this->getHeaderFormatted($name, $this->metaModel->get($name, 'label'));
                $content[] = $this->$name;

            } elseif (is_array($name)) {
                if ($c = array_shift($name)) {
                    $content[] = $c;
                }
                if ($h = array_shift($name)) {
                    $headers[] = $h;
                }
                if ($cc = array_shift($name)) { // Content class
                    $content[] = $cc;
                }
                if ($hc = array_shift($name)) {
                    $headers[] = $hc;
                } elseif ($cc) {
                    $headers[] = $cc;
                }

            } else {
                $headers[] = $name;
                $content[] = $name;
            }
        }

        return $this->table->addColumn($content, $headers);
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
     * @return \Zalt\Html\AElement|string
     */
    public function createSortLink(string $name, $label = null): AElement|string
    {
        $name = $this->_checkName($name);
        
        if ($this->metaModel->get($name, 'noSort')) {
            return $label;
        }

        if (! $label) {
            $label = $this->metaModel->get($name, 'label');
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

        return Html::create()->a($this->getUrl([$sortParam => $name]), ['class' => $class, 'title' => $this->metaModel->get($name, 'description')], $label);
    }

    /**
     * @return array Get the output data as a set of rows
     */
    public function getRows(): array
    {
        $repeater = $this->getRepeater();
        $output = [];
        $repeater->__start();
        while ($row = $repeater->__next()) {
            $output[] = $row;
        }
        // Reset repeater
        $repeater->__start();

        return $output;
    }

    public function setSortData(DataReaderInterface $model): TableBridgeAbstract
    {
        $sort = $model->getSort();

        $this->sortAsc = reset($sort) !== SORT_DESC;
        $this->sortKey = key($sort) ?: '';

        return $this;
    }
}