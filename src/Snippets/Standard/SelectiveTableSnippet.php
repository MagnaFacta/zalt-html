<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Michiel Rooks <info@touchdownconsulting.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets\Standard;

/**
 * Extends the TableSnippet so we can select which fields from the data we want to show
 * and what labels to use instead of using the column name as a label
 *
 * Usage:
 * $this->setDisplayColumns(array('id_reception_code'=>$this->_('Reception code'),
 *                                'lastname'         =>$this->_('Lastname'));
 *
 * Or set the $columns snippet parameter.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.3
 */
class SelectiveTableSnippet extends \MUtil\Snippets\TableSnippetAbstract
{
    /**
     *
     * @var array of name => label columns
     */
    protected $columns = array();

    /**
     * Add the columns to the table
     *
     * @param \MUtil\Html\TableElement $table
     */
    protected function addColumns(\MUtil\Html\TableElement $table)
    {
        foreach ($this->columns as $name => $label) {
            $table->addColumn($this->repeater->$name, $label);
        }
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
        return parent::hasHtmlOutput() && $this->columns;
    }

    /**
     * Set the columns to display with their labels
     *
     * @param array $columns name => label columns
     */
    public function setDisplayColumns(array $columns) {
        $this->columns = $columns;
    }
}