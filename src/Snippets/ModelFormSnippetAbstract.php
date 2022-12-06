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
use Zalt\Model\Exceptions\MetaModelException;
use Zalt\Model\MetaModelInterface;

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
     * @var string[] Array describing what is saved 
     */
    protected $subjects = ['item', 'items'];
    
    /**
     * Output only those elements actually used by the form.
     *  
     * When false all fields without a label or elementClass are hidden,
     * when true those are left out, unless they happend to be a key field or
     * needed for a dependency.
     *
     * @var boolean
     */
    protected bool $onlyUsedElements = false;

    /**
     * Adds elements from the model to the bridge that creates the form.
     *
     * Overrule this function to add different elements to the browse table, without
     * having to recode the core table building code.
     *
     * @param \Zalt\Model\Bridge\FormBridgeInterface $bridge
     * @param \Zalt\Model\Data\FullDataInterface $model
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
     * @param int $changed
     * @return string
     */
    public function getChangedMessage(int $changed): string
    {
        return sprintf($this->_('%2$u %1$s saved'), $this->getTopic($changed), $changed);
    }

    /**
     * Helper function to allow generalized statements about the items in the model to used specific item names.
     *
     * @param int $count
     * @return $string
     */
    public function getTopic($count = 1)
    {
        return $this->plural($this->subjects[0], $this->subjects[1], $count);
    }

    /**
     * Initialize the _items variable to hold all items from the model
     */
    protected function initItems(MetaModelInterface $metaModel)
    {
        if (is_null($this->_items)) {
            $this->_items = $metaModel->getItemsOrdered();

            if ($this->onlyUsedElements) {
                $metaModel->clearElementClasses();
            }
        }
    }

    /**
     * Makes sure there is a form.
     */
    protected function loadForm()
    {
        $dataModel = $this->getModel();
        $baseform  = $this->createForm();
        $bridge    = $dataModel->getBridgeFor('form');
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
        $model = $this->getModel();

        if ($this->requestInfo->isPost()) {
            $this->formData = $model->loadPostData($this->requestInfo->getRequestPostParams() + $this->formData, $this->createData);

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
        $model          = $this->getModel();
        $this->formData = $model->save($this->formData);
        $changed        = $model->getChanged();

        // Message the save
        return $changed;
    }
}