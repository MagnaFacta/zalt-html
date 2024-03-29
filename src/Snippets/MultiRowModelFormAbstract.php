<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Zalt\Html\TableElement;
use Zalt\Model\Bridge\FormBridgeAbstract;
use Zalt\Model\Data\DataWriterInterface;
use Zalt\Model\Data\FullDataInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Jan 12, 2017 10:59:54 AM
 */
abstract class MultiRowModelFormAbstract extends \Zalt\Snippets\ModelFormSnippetAbstract
{
    protected $formTableElement;

    /**
     *
     * @var string
     */
    protected $editTableClass;

    /**
     * Creates from the model a \Zend_Form using createForm and adds elements
     * using addFormElements().
     *
     * @return \Zend_Form
     */
    protected function getModelForm()
    {
        $model     = $this->getModel();
        $baseform  = $this->createForm();

        /**
         * @var FormBridgeAbstract $bridge
         */
        $bridge    = $model->getBridgeFor('form', new \Zend_Form_SubForm());
        $newData   = $this->addFormElements($bridge);

        $this->formTableElement = new TableElement(
                $bridge->getForm(),
                $model->getName(),
                array('class' => $this->editTableClass)
                );

        $baseform->setMethod('post')
            ->addElement($this->formTableElement);

        return $baseform;
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
        $mname = $model->getName();

        // \Zalt\EchoOut\EchoOut::track($model->getFilter());

        if ($this->requestInfo->isPost()) {
            $formData = $this->requestInfo->getRequestPostParams();

            foreach ($formData[$mname] as $id => $row) {
                if (isset($this->formData[$mname], $this->formData[$mname][$id])) {
                    $row = $row + $this->formData[$mname][$id];
                }
                $this->formData[$mname][$id] = $model->loadPostData($row, $this->createData);
            }
            unset($formData[$mname]);
            $this->formData = $this->formData + $formData; // Add post, etc..

        } else {
            // Assume that if formData is set it is the correct formData
            if (!$this->formData) {
                if ($this->createData) {
                    $this->formData[$mname][] = $model->loadNew();
                    $this->formData[$mname][] = $model->loadNew();
                } else {
                    $this->formData[$mname] = $model->load();

                    if (! $this->formData[$mname]) {
                        throw new \Zend_Exception($this->_('Unknown edit data requested'));
                    }
                }
            }
        }

        // \Zalt\EchoOut\EchoOut::track($this->formData);
        return $this->formData;
    }

    /**
     * Hook containing the actual save code.
     *
     * Calls afterSave() for user interaction.
     *
     * @see afterSave()
     */
    protected function saveData(): int
    {
        $this->beforeSave();

        unset($this->formData[$this->csrfName]);

        // Perform the save
        /**
         * @var FullDataInterface $model
         */
        $model = $this->getModel();
        $mname = $model->getName();

        // \Zalt\EchoOut\EchoOut::track($this->formData[$mname]);
        foreach ($this->formData[$mname] as $key => $row) {
            $this->formData[$mname] = $model->save($row);
        }

        $changed = $model->getChanged();

        // Message the save
        return $changed;
    }
}
