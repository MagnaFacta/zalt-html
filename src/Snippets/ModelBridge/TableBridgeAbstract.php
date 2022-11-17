<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\ModelBridge;

use Zalt\Late\Late;
use Zalt\Model\Bridge\BridgeInterface;
use Zalt\Model\Data\DataReaderInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @since      Class available since version 1.0
 */
abstract class TableBridgeAbstract extends \Zalt\Model\Bridge\BridgeAbstract
{
    /**
     * @var array $name => [displayFunctions]
     */
    protected $_headerCompilations = [];

    /**
     * The actual table
     *
     * @var \Zalt\Html\TableElement
     */
    protected $table;

    /**
     * Cascades calls to the underlying table
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array(array($this->table, $name), $arguments);
    }

    public function __construct(DataReaderInterface $dataModel, $elementArgs = null)
    {
        parent::__construct($dataModel);

        $this->_chainedBridge = $this->metaModel->getBridgeFor('display');
        
        if ($elementArgs instanceof \Zalt\Html\ElementInterface) {
            $this->table = $elementArgs;
        } else {
            $args = \Zalt\Ra\Ra::args(func_get_args(), 1);

            $this->table = \Zalt\Html\Html::table($args);
        }
    }

    /**
     * @inheritDoc
     */
    protected function _compile(string $name) : array
    {
        $output = [];
        foreach (['itemDisplay', 'tableDisplay'] as $functionName) {
            $output[$functionName] = $this->_compileFunction($name, $functionName);
        }
        return array_filter($output);
    }

    protected function _compileFunction(string $name, string $functionName):? callable
    {
        $function = $this->metaModel->get($name, $functionName);

        if (! $function) {
            return null;
        }

        if (is_callable($function)) {
            return $function;
        }

        if (is_object($function)) {
            if (($function instanceof \Zalt\Html\ElementInterface)
                || method_exists($function, 'append')) {
                return [clone $function, 'append'];
            }
        }

        // Assume it is a html tag when a string
        if (is_string($function)) {
            return [\Zalt\Html\Html::create($function), 'append'];
        }

        return $function;
    }

    /**
     * @inheritDoc
     */
    protected function _compileHeader(string $name) : array
    {
        $output = [];
        foreach (['tableHeaderDisplay', 'tableDisplay'] as $functionName) {
            $output[$functionName] = $this->_compileFunction($name, $functionName);
        }
        return array_filter($output);
    }

    /**
     * Format a value using the rules for the specified name.
     *
     * This is the workhouse function for the foematter and can
     * also be used with data not loaded from the model.
     *
     * To add the raw value to the called function as raw parameter, use an array callback for function,
     * and add a temporary third value of true.
     *
     * @param string $name The real name and not e.g. the key id
     * @param mixed $value
     * @return mixed
     */
    public function formatHeader(string $name, $label)
    {
        if (! array_key_exists($name, $this->_headerCompilations)) {
            $this->_headerCompilations[$name] = $this->_compileHeader($name);
        }

        return $this->_executeCompilation($this->_headerCompilations[$name], $label);
    }

    /**
     * Returns a formatted value or a late call to that function,
     * depending on the mode.
     *
     * @param string $name The field name or key name
     * @param mixed $label The label to display
     * @return mixed Late unless in single row mode
     * @throws \Zalt\Model\Exceptions\MetaModelException
     */
    public function getHeaderFormatted(string $name, mixed $label): mixed
    {
        if (BridgeInterface::MODE_LAZY == $this->mode) {
            return Late::call([$this, 'formatHeader'], $name, $label);
        }
        return $this->formatHeader($name, $label);
    }

    /**
     * Get the actual table
     *
     * @return \Zalt\Html\TableElement
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Is there a repeater
     *
     * @return bool
     */
    public function hasRepeater(): bool
    {
        return $this->table->hasRepeater() || parent::hasRepeater();
    }

    /**
     * Set the repeater source for the late data
     *
     * @param mixed $repeater \Zalt\Late\RepeatableInterface or something that can be made into one.
     * @return BridgeInterface (continuation pattern)
     */
    public function setRepeater($repeater): BridgeInterface
    {
        parent::setRepeater($repeater);

        $this->table->setRepeater($this->_repeater);

        return $this;
    }

    public function setSortData(DataReaderInterface $model): TableBridgeAbstract
    {
        $sort = $model->getSort();

        $this->sortAscParam  = $model->getSortParamAsc();
        $this->sortDescParam = $model->getSortParamDesc();

        $this->sortAsc = reset($sort) !== SORT_DESC;
        $this->sortKey = key($sort) ?: '';

        return $this;
    }
}