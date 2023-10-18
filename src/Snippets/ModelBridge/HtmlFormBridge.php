<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\ModelBridge;

use Zalt\Html\Form\FormElement;
use Zalt\Html\Form\InputElement;
use Zalt\Model\Exception\MetaModelException;

/**
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @since      Class available since version 1.0
 */
class HtmlFormBridge extends \Zalt\Model\Bridge\FormBridgeAbstract
{
    protected FormElement $form;

    /**
     * @inheritDoc
     */
    public function __get(string $name): mixed
    {
        return $this->form->getElement($name)
            ?? $this->add($name);
    }

    /**
     * @inheritDoc
     */
    protected function _addToForm($name, $element, $options = null, $addFilters = true, $addValidators = true): mixed
    {
        if ($element instanceof InputElement) {
            $element->setName($name);
        } else {
            // If type set in options they will overrule the default type
            $element = new InputElement($name, 'text', $options);
        }

        if ($addFilters) {
            foreach ($this->filterBridge->getFiltersFor($name) as $filter) {
                $this->addFilter($name, $filter);
            }
        }
        if ($addValidators) {
            foreach ($this->validatorBridge->getValidatorsFor($name) as $validator) {
                $this->addFilter($name, $validator);
            }
        }

        return $this->addToForm($element);
    }

    public function addFilter($name, $filter, $options = array())
    {
        // TODO: Implement addFilter() method.
    }

    /**
     * Overrule this function to add decoration
     * @param InputElement $element
     * @return InputElement (continuation pattern)
     */
    public function addToForm(InputElement $element): InputElement
    {
        $this->form->append($element->getLabel(), $element);
        return $element;
    }

    /**
     * @inheritDoc
     */
    public function addValidator($elementName, $validator, $breakChainOnFailure = false, $options = array())
    {
        // TODO: Implement addValidator() method.
    }

    /**
     * @inheritDoc
     */
    public function format($name, $value)
    {
        $output = $this->form->getElement($name);

        if ($output) {
            $output->setValue($value);
            return $output->render();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function getTab($name)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setForm(mixed $form): void
    {
        if ($form instanceof FormElement) {
            $this->form = $form;

            $this->form->setName($this->metaModel->getName());
        } else {
            throw new MetaModelException(sprintf("Form parameter must be an instance of \Zend_Form in %s->setForm(). Object of '%s' class given instead.", __CLASS__, get_class($form)));
        }
    }
}