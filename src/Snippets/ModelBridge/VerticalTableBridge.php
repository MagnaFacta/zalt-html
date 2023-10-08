<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets\ModelBridge;

use Zalt\Late\Late;

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class VerticalTableBridge extends DetailTableBridge
{
    protected $columnCount   = 1;
    protected $columnCounts  = [];
    protected $currentColumn = 0;

    /**
     *
     * @var boolean True if th's should be used for label class.
     */
    protected $labelTh = true;

    private function _checkAttributesFor($name, array $attr)
    {
        if (is_string($name) && $this->metaModel->has($name)) {
            $attr = $attr + $this->metaModel->get($name, ['colspan', 'rowspan', 'tdClass', 'thClass']);
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

        return array($attr, $hattr);
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

                // echo '[' . ($rowspan + 1) . '] ';

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

    public function addItem($name, $label = null, array $attr = array())
    {
        list($attr, $hattr) = $this->_checkAttributesFor($name, $attr);

        $this->_checkColumnNewRow();

        if (is_string($name) && $this->metaModel->has($name, 'description') && !isset($hattr['title'])) {
            $hattr['title'] = $this->metaModel->get($name, 'description');
        }
        if ($this->labelTh) {
            $this->table->tdh($label, $hattr);
        } else {
            $this->table->td($label, $hattr);
        }

        $this->table->td($this->getLate($name), $attr);

        $this->_checkColumnAdded($attr);

        return $this;
    }

    public function addItemWhen($condition, $name = null, $label = null, array $attr = array())
    {
        $attr['renderWithoutContent'] = false;

        if (null === $name) {
            $name = $condition;
        }
        if (is_string($condition)) {
            $condition = $this->$condition;
        }

        list($attr, $hattr) = $this->_checkAttributesFor($name, $attr);

        $this->_checkColumnNewRow();

        if ($this->labelTh) {
            $this->table->tdh(Late::iif($condition, $label), $hattr);
        } else {
            $this->table->td(Late::iif($condition, $label), $hattr);
        }

        $this->table->td(Late::iif($condition, $this->getLate($name)), $attr);

        $this->_checkColumnAdded($attr);

        return $this;
    }

    public function getColumnCount()
    {
        return $this->columnCount;
    }

    public function getTable()
    {
        if ($this->columnCount) {
            // Check for end of rows
            //
            // First get the number of columns that should be in the current row
            if ($this->columnCounts) {
                // \MUtil\EchoOut\EchoOut::r($this->columnCounts);
                $maxColumns = $this->columnCounts;
            } else {
                $maxColumns = array($this->columnCount);
            }

            // Pad the table for as long as it takes
            foreach ($maxColumns as $maxColumn) {
                while ($this->currentColumn < $maxColumn) {
                    $this->table->tdh();
                    $this->table->td();
                    $this->currentColumn++;
                }
            }
        }

        return $this->table;
    }

    /**
     * Add an item based of a lazy if
     *
     * @param mixed $if
     * @param mixed $item
     * @param mixed $else
     * @return array
     */
    public function itemIf($if, $item, $else = null)
    {
        if (is_string($if)) {
            $if = $this->$if;
        }

        return Late::iff($if, $item, $else);
    }

    public function setColumnCount($count)
    {
        $this->columnCount = $count;

        return $this;
    }

    public function setColumnCountOff()
    {
        return $this->setColumnCount(false);
    }

    public function setLabelTh($value)
    {
        $this->labelTh = $value;
    }
}