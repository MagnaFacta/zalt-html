<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\Zend;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\Zend
 * @since      Class available since version 1.0
 */
trait ZendFormSnippetTrait
{

    /**
     * Optional csrf element
     *
     * @var \Zend_Form_Element_Hash
     */
    protected $_csrf;

    /**
     *
     * @var \Zend_Form
     */
    protected $_form;

    /**
     *
     * @var \Zend_Form_Element_Submit
     */
    protected $_saveButton;

    /**
     * As it is better for translation utilities to set the labels etc. translated,
     * the \MUtil default is to disable translation.
     *
     * However, this also disables the translation of validation messages, which we
     * cannot set translated. The \MUtil form is extended so it can make this switch.
     *
     * @var boolean True
     */
    protected bool $disableValidatorTranslation = false;

    /**
     * Automatically calculate and set the width of the labels
     *
     * @var int
     */
    protected int $layoutAutoWidthFactor = 1;

    /**
     * Set the (fixed) width of the labels, if zero: is calculated 
     *
     * @var int
     */
    protected int $layoutFixedWidth = 0;

    /**
     * Simple default function for making sure there is a $this->_saveButton.
     *
     * As the save button is not part of the model - but of the interface - it
     * does deserve it's own function.
     *
     * @param string $csrfId
     * @param int    $csrfTimeout
     * @return void
     */
    protected function addCsrf(string $csrfId, int $csrfTimeout)
    {
        if (! $this->_csrf) {
            $this->_form->addElement('hash', $csrfId, array(
                'salt' => 'mutil_' . $this->requestInfo->getCurrentController() . '_' . $this->requestInfo->getCurrentAction(),
                'timeout' => $csrfTimeout,
            ));
            $this->_csrf = $this->_form->getElement($this->csrfId);
        }
    }

    protected function addSaveButton(string $saveButtonId, string $saveLabel, string $buttonClass)
    {
        if (! $this->_saveButton) {
            //If not already there, add a save button
            $this->_saveButton = $this->_form->getElement($saveButtonId);

            if (! $this->_saveButton) {
                if (null === $saveLabel) {
                    $saveLabel = $this->_('Save');
                }

                $options = array('label' => $saveLabel);
                if ($buttonClass) {
                    $options['class'] = $buttonClass;
                }

                $this->_saveButton = $this->_form->createElement('submit', $saveButtonId, $options);

                $this->_form->addElement($this->_saveButton);
            }
        }
    }

    /**
     * Perform some actions on the form, right before it is displayed but already populated
     *
     * Here we add the table display to the form.
     */
    public function beforeDisplay()
    {
        parent::beforeDisplay();

        if ($this->layoutAutoWidthFactor || $this->layoutFixedWidth) {
            $div = new \Zalt\Html\Zend\ZendDivFormElement();

            if ($this->layoutFixedWidth) {
                $div->setAsFormLayout($this->_form, $this->layoutFixedWidth);
            } else {
                $div->setAutoWidthFormLayout($this->_form, $this->layoutAutoWidthFactor);
            }
        }
    }

    /**
     * After validation we clean the form data to remove all
     * entries that do not have elements in the form (and
     * this filters the data as well).
     */
    public function cleanFormData()
    {
        $this->formData = $this->_form->getValues();
    }

    /**
     * Creates an empty form. Allows overruling in sub-classes.
     *
     * @param array $options
     * @return mixed
     */
    protected function createForm(array $options = [])
    {
        $this->_form = new \Zend_Form($options);

        return $this->_form;
    }

    public function getFormOutput(): mixed
    {
        return $this->_form;
    }

    public function isSaveClicked(): bool
    {
        return (! $this->_saveButton) || $this->_saveButton->isChecked();
    }

    /**
     * Hook that allows actions when the input is invalid
     *
     * When not rerouted, the form will be populated afterwards
     */
    protected function onInValid()
    {
        parent::onInValid();

        if ($this->_csrf) {
            if ($this->_csrf->getMessages()) {
                $this->addMessage($this->_('The form was open for too long or was opened in multiple windows.'));
            }
        }
    }

    /**
     * Hook for setting the data on the form.
     */
    protected function populateForm()
    {
        $this->_form->populate($this->formData);
    }

    /**
     * Performs the validation.
     *
     * @return boolean True if validation was OK and data should be saved.
     */
    protected function validateForm(array $formData): bool
    {
        // Note we use an \Zalt\Form
        return $this->_form->isValid($formData, $this->disableValidatorTranslation);
    }
}