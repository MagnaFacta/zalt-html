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
     *
     * @var \Zend_Form
     */
    protected $_form;

    /**
     *
     * @var null|\Zend_Form_Element_Submit
     */
    protected $_saveButton = null;

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
     * Simple function adding the actual crsf hidden field
     *
     * @param string $csrfName
     * @param string|null $csrfToken
     * @param mixed $form
     * @return void
     */
    protected function addCsrf(string $csrfName, ?string $csrfToken, mixed $form): void
    {
        if (($form instanceOf \Zend_Form) && $csrfName && $csrfToken && (! $form->getElement($csrfName))) {
            $csrf = $form->createElement('Hidden', $csrfName);
            $csrf->setValue($csrfToken);
            $csrf = $form->addElement($csrf);
        }
    }

    protected function addSaveButton(string $saveButtonId, ?string $saveLabel, string $buttonClass)
    {
        if ($saveButtonId && (! $this->_saveButton)) {
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
        $output = $this->_form->isValid($formData, $this->disableValidatorTranslation);

        // Make sure error messages by hidden elements are displayed
        foreach ($this->_form->getElements() as $name => $element) {
            if ($element instanceof \Zend_Form_Element_Hidden) {
                if (isset($formData[$name]) && ! $element->isValid($formData[$name], $formData)) {
                    foreach ($element->getMessages() as $message) {
                        $this->addMessage($name . ': ' . $message);
                    }
                }
            }
        }

        return $output;
    }
}