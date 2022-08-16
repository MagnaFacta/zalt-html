<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 * Outputs the data supplied through the $data or $repeater parameter
 * in a simple standard Html table.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class TableSnippetAbstract extends \MUtil\Snippets\SnippetAbstract
{
    /**
     * Optional, instead of repeater array containing the data to show
     *
     * @var array Nested array
     */
    protected $data;

    /**
     * @var mixed $content Content that can be rendered when the table body is empty
     */
    protected $onEmpty;

    /**
     * REQUIRED, but can be derived from $this->data
     *
     * @var \MUtil\Lazy\RepeatableInterface
     */
    protected $repeater;

    /**
     * Add the columns to the table
     *
     * This is a default implementation, overrule at will
     *
     * @param \MUtil\Html\TableElement $table
     */
    protected function addColumns(\MUtil\Html\TableElement $table)
    {
        if ($this->data) {
            $row = reset($this->data);
        } else {
            $this->repeater->__start();
            $row = $this->repeater->__current();
        }

        foreach ($row as $name => $value) {
            $table->addColumn($this->repeater->$name, $name);
        }
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $table = new \MUtil\Html\TableElement($this->repeater);

        if ($this->onEmpty) {
            $table->setOnEmpty($this->onEmpty);
        }

        $this->addColumns($table);
        
        // We wrap the table in a div, but the tables needs it's own class attributes
        if ($this->class) {
            $table->appendAttrib('class', $this->class);
        }

        $container = \MUtil\Html::create()->div(array('class' => 'table-container'));
        $container[] = $table;

        return $container;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
    {
        if (! $this->repeater) {
            $this->repeater = \MUtil\Lazy::repeat($this->data);
        } else {
            // We do not know whether there is any link between
            // the data and the repeater, so do not use the data
            $this->data = null;
        }

        // If onEmpty is set, we alwars have output
        if ($this->onEmpty) {
            return true;
        }

        // Is there any data in the repeater?
        return $this->repeater->__start();
    }
}
