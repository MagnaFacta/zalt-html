<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Model\Bridge\FormBridgeInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Exception\MetaModelException;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\MetaModellerInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
abstract class ModelFormSnippetAbstract extends FormSnippetAbstract
{
    use ModelSnippetTrait;

    /**
     * Array of item names still to be added to the form
     *
     * @var ?array
     */
    protected ?array $_items = null;

    /**
     * Output only those elements actually used by the form.
     *
     * When false all fields without a label or elementClass are hidden,
     * when true those are left out, unless they happened to be a key field or
     * needed for a dependency.
     *
     * @var boolean
     */
    protected bool $onlyUsedElements = true;

    /**
     * Adds elements from the model to the bridge that creates the form.
     *
     * Overrule this function to add different elements to the browse table, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $dataModel
     */
    protected function addBridgeElements(FormBridgeInterface $bridge, FullDataInterface $dataModel)
    {
        //Get all elements in the model if not already done
        $this->initItems($dataModel->getMetaModel());

        //And any remaining item
        $this->addItems($bridge, $this->_items);
    }

    protected function addFormElements(mixed $form)
    { // Not used in this class
    }

    /**
     * Add items to the bridge, and remove them from the items array
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param array $elements
     *
     * @return void
     */
    protected function addItems(FormBridgeInterface $bridge, array $elements)
    {
        $metaModel = $this->getModel()->getMetaModel();

        //Remove the elements from the _items variable
        $this->_items = array_diff($this->_items, $elements);

        //And add them to the bridge
        foreach($elements as $name) {
            if ($metaModel->has($name, 'label') || $metaModel->has($name, 'elementClass')) {
                $bridge->add($name);
            } else {
                $bridge->addHidden($name);
            }
        }
    }

    /**
     * Hook that allows actions when data was saved
     *
     * When not rerouted, the form will be populated afterwards
     *
     * @param int $changed The number of changed rows (0 or 1 usually, but can be more)
     */
    protected function afterSave($changed)
    {
        parent::afterSave($changed);

        // Communicate to user
        if ($changed) {
            $this->addMessage($this->getChangedMessage($changed));
        } else {
            $this->addMessage($this->_('No changes to save!'));
        }
    }

    /**
     * Remove all non-used elements from a form by setting the elementClasses to None.
     *
     * Checks for dependencies and keys to be included
     */
    public function clearElementClasses(MetaModelInterface $metaModel)
    {
        $labels  = $metaModel->getColNames('label');
        $options = array_intersect($metaModel->getColNames('multiOptions'), $labels);

        // Set element class to text for those with labels without an element class
        $metaModel->setDefault($options, 'elementClass', 'Select');

        // Set element class to text for those with labels without an element class
        $metaModel->setDefault($labels, 'elementClass', 'Text');

        // Hide al dependencies plus the keys
        $elems   = $metaModel->getColNames('elementClass');
        $depends = $metaModel->getDependentOn($elems) + $metaModel->getKeys();

        $metaModel->setDefault(array_keys($this->extraFilter), 'elementClass', 'None');

        if ($depends) {
            $metaModel->setDefault($depends, 'elementClass', 'Hidden');
        }

        // Leave out the rest
        $metaModel->setDefault('elementClass', 'None');

        // Cascade
        foreach ($metaModel->getCol('model') as $subModel) {
            if ($subModel instanceof MetaModellerInterface) {
                $this->clearElementClasses($subModel->getMetaModel());
            }
        }
    }

    /**
     * @param int $changed
     * @return string
     */
    public function getChangedMessage(int $changed): string
    {
        return sprintf($this->_('%2$u %1$s saved'), $this->getTopic($changed), $changed);
    }

    /**
     * Initialize the _items variable to hold all items from the model
     */
    protected function initItems(MetaModelInterface $metaModel)
    {
        if (is_null($this->_items)) {
            $this->_items = $metaModel->getItemsOrdered();

            if ($this->onlyUsedElements) {
                $this->clearElementClasses($metaModel);
            }
        }
    }

    /**
     * Makes sure there is a form.
     */
    protected function loadForm()
    {
        /**
         * @var FullDataInterface $dataModel
         */
        $dataModel = $this->getModel();
        $baseform  = $this->createForm();

        /**
         * @var FormBridgeInterface $bridge
         */
        $bridge = $dataModel->getBridgeFor('form');
        if ($bridge instanceof FormBridgeInterface) {
            $bridge->setForm($baseform);
        }

        $this->addBridgeElements($bridge, $dataModel);

        return $bridge->getForm();
    }

    /**
     * Hook that loads the form data from $_POST or the model
     *
     * Or from whatever other source you specify here.
     */
    protected function loadFormData(): array
    {
        /**
         * @var FullDataInterface $model
         */
        $model = $this->getModel();

        if ($this->requestInfo->isPost()) {
            $metaModel = $model->getMetaModel();
            $matchedNonOverwrittenParams = array_diff_key($this->requestInfo->getRequestMatchedParams(), $metaModel->getCol('label'), $metaModel->getCol('hidden'));

            $this->formData = $model->loadPostData($matchedNonOverwrittenParams, $this->requestInfo->getRequestPostParams() + $this->formData + $this->requestInfo->getRequestMatchedParams(), $this->createData);

        } else {
            // Assume that if formData is set it is the correct formData
            if (! $this->formData)  {
                if ($this->createData) {
                    $this->formData = $model->loadNew();
                } else {
                    $this->formData = $model->loadFirst();

                    if (! $this->formData) {
                        throw new MetaModelException($this->_('Unknown edit data requested'));
                    }
                }
            }
        }
        $this->formData = $this->loadCsrfData() + $this->formData + $this->requestInfo->getRequestMatchedParams();

        return $this->formData;
    }

    /**
     * Hook containing the actual save code.
     *
     * @return int The number of "row level" items changed
     */
    protected function saveData(): int
    {
        // Perform the save
        /**
         * @var FullDataInterface $model
         */
        $model          = $this->getModel();
        $this->formData = $model->save($this->formData, $this->requestInfo->getRequestMatchedParams());
        return $model->getChanged();
    }
}