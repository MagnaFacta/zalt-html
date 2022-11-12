<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\ModelBridge;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @since      Class available since version 1.0
 */
class DetailTableBridge extends TableBridgeAbstract
{
    /**
     * @var int Maximum number of columns per item
     */
    protected $columnCount   = 1;

    /**
     * @var array store of colspans to use together with rowspans
     */
    protected $columnCounts  = [];

    /**
     * @var int Keep track of the column we're at.
     */
    protected $currentColumn = 0;

    /**
     *
     * @var boolean True if th's should be used for label class.
     */
    protected $labelTh = true;

    private function _checkAttributesFor(string $name, array $attr): array
    {
        if ($this->metaModel->has($name)) {
            $attr = $attr + $this->metaModel->get($name, 'colspan', 'rowspan', 'tdClass', 'thClass');
            
            if ($this->metaModel->has($name, 'description') && !isset($attr['title'])) {
                $attr['title'] = $this->metaModel->get($name, 'description');
            }
        }

        $hattr = $attr;
        if (isset($attr['colspan'])) {
            // Colspan is applied only to value
            $attr['colspan'] = ($attr['colspan'] * 2) - 1;
            unset($hattr['colspan']);
        }
        if (isset($attr['thClass'])) {
            $hattr['class'] = $attr['thClass'];
            unset($attr['thClass'], $hattr['thClass']);
        }
        if (isset($attr['tdClass'])) {
            $attr['class'] = $attr['tdClass'];
            unset($attr['tdClass'], $hattr['tdClass']);
        }

        return [$attr, $hattr];
    }

    private function _checkColumnAdded(array $attr)
    {
        // Without columnCount the programmer must set the rows by hand.
        if ($this->columnCount) {
            // Get the COLSPAN and add it to the current number of columns
            $colCount = isset($attr['colspan']) ? $attr['colspan'] : 1;
            $this->currentColumn += $colCount;

            // Add the ROWSPAN by substracting COLSPAN from a future number of rows
            //
            // Yep, complicated array work
            if (isset($attr['rowspan']) && $attr['rowspan'] > 1) {
                // Leave out the current row
                $rowspan = $attr['rowspan'];

                // Decrease all already defined column counts with one
                foreach ($this->columnCounts as &$count) {
                    if ($rowspan == 0) {
                        break;
                    }

                    $count -= $colCount;
                    $rowspan--;
                }

                // Define lower column counts for not yet defined rows
                if ($rowspan) {
                    $this->columnCounts = array_pad($this->columnCounts, $rowspan, $this->columnCount - $colCount);
                }

                // \MUtil\EchoOut\EchoOut::r($this->columnCounts);
            }
        }
    }

    private function _checkColumnNewRow()
    {
        // Without columnCount the programmer must set the rows by hand.
        if ($this->columnCount) {
            // Check for end of rows
            //
            // First get the number of columns that should be in the current row
            if ($this->columnCounts) {
                // \MUtil\EchoOut\EchoOut::r($this->columnCounts);
                $maxColumns = reset($this->columnCounts);
            } else {
                $maxColumns = $this->columnCount;
            }

            // Now add new row if over column margin.
            //
            // Do this before the ROWSPAN is applied as that applies to future rows
            // \MUtil\EchoOut\EchoOut::r((is_string($name) ? $name : 'array') . '-' . $this->currentColumn . '-' . $maxColumns);
            if ($this->currentColumn >= $maxColumns) {
                $this->table->tr();
                $this->currentColumn = 0;

                if ($this->columnCounts) {
                    array_shift($this->columnCounts);
                }
            }
        }
    }

    /**
     * @param string $name A field name (or fixed text).
     * @param mixed  $label The label. Default is set to model defined label, but a string or ann HtmlIntrface object can be passed here  
     * @param array  $attr Optional extra/special attributes
     * @return void
     * @throws \Zalt\Model\Exceptions\MetaModelException
     */
    public function addItem(string $name, mixed $label = null, array $attr = [])
    {
        list($attr, $hattr) = $this->_checkAttributesFor($name, $attr);

        $this->_checkColumnNewRow();

        $labelContent = $this->getHeaderFormatted($name, $label);
        if ($this->labelTh) {
            $this->table->tdh($labelContent, $hattr);
        } else {
            $this->table->td($labelContent, $hattr);
        }

        $this->table->td($this->$name, $attr);

        $this->_checkColumnAdded($attr);
    }

    public function setColumnCount($count)
    {
        $this->columnCount = $count;

        return $this;
    }
}