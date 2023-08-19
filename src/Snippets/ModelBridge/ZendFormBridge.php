<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets\ModelBridge;

use Zalt\Model\Bridge\Laminas\LaminasValidatorBridge;
use Zalt\Model\Bridge\ValidatorBridgeInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Model\Exception\MetaModelException;
use Zalt\Ra\Ra;

/**
 *
 * @package    Zalt
 * @subpackage Snippets\ModelBridge
 * @since      Class available since version 1.0
 */
class ZendFormBridge extends \Zalt\Model\Bridge\FormBridgeAbstract
{
    protected \Zend_Form $form;

    public function __get(string $name) : mixed
    {
        $element = $this->form->getElement($name);
        if ($element) {
            return $element;
        }

        return $this->add($name);
    }

    /**
     * Add the element to the form and apply any filters & validators
     *
     * @param string $name
     * @param string|\Zend_Form_Element $element Element or element class name
     * @param array $options Element creation options
     * @param boolean $addFilters When true filters are added
     * @param boolean $addValidators When true validators are added
     * @return mixed
     */
    protected function _addToForm($name, $element, $options = null, $addFilters = true, $addValidators = true): mixed
    {
        $this->form->addElement($element, $name, $options);
        if (is_string($element)) {
            $element = $this->form->getElement($name);
        }
        if (isset($options['escapeDescription']))  {
            $description = $element->getDecorator('Description');
            if ($description instanceof \Zend_Form_Decorator_Description) {
                $description->setEscape($options['escapeDescription']);
            }
        }
        if ($addFilters) {
            $this->_applyFilters($name, $element);
        }
        if ($addValidators) {
            $this->_applyValidators($name, $element);
        }
        // \\EchoOut\EchoOut::r($element->getOrder(), $element->getName());

        return $element;
    }

    /**
     * Apply the filters for element $name to the element
     *
     * @param string $name
     * @param \Zend_Form_Element $element
     */
    protected function _applyFilters($name, \Zend_Form_Element $element)
    {
        $filters = $this->metaModel->get($name, 'filters');

        if ($filter = $this->metaModel->get($name, 'filter')) {
            if ($filters) {
                array_unshift($filters, $filter);
            } else {
                $filters = array($filter);
            }
        }

        if ($filters) {
            foreach ($filters as $filter) {
                if (is_array($filter)) {
                    call_user_func_array(array($element, 'addFilter'), $filter);
                } else {
                    $element->addFilter($filter);
                }
            }
        }
    }

    /**
     * Apply the validators for element $name to the element
     *
     * @param string $name
     * @param \Zend_Form_Element $element
     */
    protected function _applyValidators($name, \Zend_Form_Element $element)
    {
        $validators = $this->validatorBridge->getValidatorsFor($name);

        if ($validators) {
            $element->addValidators($validators);
        }
    }

    protected function _mergeOptions($name, array $options, ...$allowedOptionKeys)
    {
        $options = parent::_mergeOptions($name, $options, ...$allowedOptionKeys);

        //If not explicitly set, use the form value for translatorDisabled, since we
        //create the element outside the form scope and later add it
        if (! isset($options['disableTranslator'])) {
            $options['disableTranslator'] = $this->form->translatorIsDisabled();
        }

        return $options;
    }


    /**
     * Adds a displayGroup to the bridge
     *
     * Use a description to set a label for the group. All elements should be added to the bridge before adding
     * them to the group. Use the special option showLabels to display the labels of the individual fields
     * in front of them. This option is only available in tabbed forms, to display multiple fields in one tablecell.
     *
     * Without labels:
     * usage: $this->addDisplayGroup('mygroup', array('element1', 'element2'), 'description', 'Pretty name for the group');
     *
     * With labels:
     * usage: $this->addDisplayGroup('mygroup', array('element1', 'element2'), 'description', 'Pretty name for the group', 'showLabels', true);
     *
     * Or specify using the 'elements' option:
     * usage: $this->addDisplayGroup('mygroup', array('elements', array('element1', 'element2'), 'description', 'Pretty name for the group'));
     *
     * @param string $name Name of element
     * @param array $elements or \Zalt\Ra\Ra::pairs() name => value array with 'elements' item in it
     * @param mixed $arrayOrKey1 \Zalt\Ra\Ra::pairs() name => value array
     * @return \Zend_Form_Displaygroup
     */
    public function addDisplayGroup($name, $elements, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 2);

        if (isset($elements['elements'])) {
            $tmpElements = $elements['elements'];
            unset($elements['elements']);
            $options = $elements + $options;
            $elements = $tmpElements;
        }

        $options = $this->_mergeOptions($name, $options,self::DISPLAY_OPTIONS, self::MULTI_OPTIONS);

        $this->form->addDisplayGroup($elements, $name, $options);

        return $this->form->getDisplayGroup($name);
    }

    /**
     * Add an element of your choice to the form
     *
     * @param \Zend_Form_Element $element
     * @return \Zend_Form_Element
     */
    public function addElement(\Zend_Form_Element $element)
    {
        return $this->_addToForm($element->getName(), $element);
    }

    public function addFile($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 1);

        $options = $this->_mergeOptions($name, $options,self::DISPLAY_OPTIONS, self::FILE_OPTIONS, self::TEXT_OPTIONS);

        $filename  = $this->_moveOption('filename',  $options);
        $count     = $this->_moveOption('count',     $options);
        $size      = $this->_moveOption('size',      $options);
        $extension = $this->_moveOption('extension', $options);

        $element = new \Zend_Form_Element_File($name, $options);
        $element->addPrefixPath('Zend_Validate',      'Zend/Validate/',       \Zend_Form_Element::VALIDATE);

        if ($filename) {
            $count = 1;
            // When
            // 1) an extension filter was defined,
            // 2) it concerns a single extension and
            // 3) $filename does not have an extension
            // then add the extension to the name.
            if ($extension &&
                (false === strpos($extension, ',')) &&
                (false === strpos($name, '.'))) {
                $filename .= '.' . $extension;
            }
            $element->addFilter(new \Zend_Filter_File_Rename(array('target' => $filename, 'overwrite' => true)));
        }
        if ($count) {
            $element->addValidator('Count', false, $count);
        }
        if ($size) {
            $element->addValidator('Size', false, $size);
        }
        if ($extension) {
            $element->addValidator('Extension', false, $extension);
            // Now set a custom validation message telling what extensions are allowed
            $validator = $element->getValidator('Extension');
            $validator->setMessage('Only %extension% files are accepted.', \Laminas\Validator\File\Extension::FALSE_EXTENSION);
        }

        return $this->_addToForm($name, $element, [], false, false);
    }

    /**
     *
     * @param string $name Element to add the filter to
     * @param mixed $filter Filter object or classname
     * @param array $options Filter options
     * @return \Zalt\Model\Bridge\FormBridgeInterface
     */
    public function addFilter($name, $filter, $options = array())
    {
        $element = $this->form->getElement($name);
        $element->addFilter($filter, $options);

        return $this;
    }

    /**
     * Adds a form multiple times in a table
     *
     * You can add your own 'form' either to the model or here in the parameters.
     * Otherwise a form of the same class as the parent form will be created.
     *
     * All elements not yet added to the form are added using a new FormBridge
     * instance using the default label / non-label distinction.
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 Ra::pairs() name => value array
     * @return mixed
     */
    public function addFormTable($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 1);

        $options = $this->_mergeOptions($name, $options,self::SUBFORM_OPTIONS);

        if (isset($options['form'])) {
            $form = $options['form'];
            unset($options['form']);
        } else {
            $formClass = get_class($this->form);
            $form = new $formClass();
        }

        $submodel = $this->metaModel->get($name, 'model');
        if ($submodel instanceof FullDataInterface) {
            $bridge = new self($submodel);
            $bridge->setForm($form);

            foreach ($submodel->getItemsOrdered() as $itemName) {
                if (! $form->getElement($itemName)) {
                    if ($submodel->has($itemName, 'label') || $submodel->has($itemName, 'elementClass')) {
                        $bridge->add($itemName);
                    } else {
                        $bridge->addHidden($itemName);
                    }
                }
            }
        }

        $prefixPaths['decorator'] = $this->form->getPluginLoader('decorator')->getPaths();
        $options['prefixPath'] = $prefixPaths;

        $element = new \MUtil\Form\Element\Table($form, $name, $options);

        $this->form->addElement($element);

        return $element;
    }

    public function addPassword($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 1);

        $options = $this->_mergeOptions($name, $options,self::DISPLAY_OPTIONS, self::PASSWORD_OPTIONS, self::TEXT_OPTIONS);
        $repeatLabel = $this->_moveOption('repeatLabel', $options);

        if ($repeatLabel) {
            $repeatOptions = $options;

            $this->_moveOption('description', $repeatOptions);

            $repeatOptions['label'] = $repeatLabel;
            $repeatName = $name . '__repeat';

            $repeatOptions['confirmWith'] = $name;
            $options['confirmWith'] = $repeatName;

            $this->metaModel->set($name, $options);
            $this->metaModel->set($repeatName, $repeatOptions);
        }

        $element = $this->_addToForm($name, 'password', $options);

        if (isset($repeatLabel, $repeatName, $repeatOptions)) {
            $repeatElement = $this->_addToForm($repeatName, 'password', $repeatOptions);
        }

        return $element;
    }

    /**
     * Adds a form multiple times in a table
     *
     * You can add your own 'form' either to the model or here in the parameters.
     * Otherwise a form of the same class as the parent form will be created.
     *
     * All elements not yet added to the form are added using a new FormBridge
     * instance using the default label / non-label distinction.
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 Ra::pairs() name => value array
     * @return mixed
     */
    public function addSubForm($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 1);

        $options = $this->_mergeOptions($name, $options,self::SUBFORM_OPTIONS);

        if (isset($options['form'])) {
            $form = $options['form'];
            unset($options['form']);
        } else {
            $formClass = get_class($this->form);
            $form = new $formClass();
        }

        $submodel = $this->metaModel->get($name, 'model');
        if ($submodel instanceof FullDataInterface) {
            $bridge = new self($submodel);
            $bridge->setForm($this->form);

            foreach ($submodel->getItemsOrdered() as $itemName) {
                if (! $form->getElement($itemName)) {
                    if ($submodel->has($itemName, 'label') || $submodel->has($itemName, 'elementClass')) {
                        $bridge->add($itemName);
                    } else {
                        $bridge->addHidden($itemName);
                    }
                }
            }
        }

        $element = new \MUtil\Form\Element\SubForms($form, $name, $options);

        $this->form->addElement($element);

        return $element;
    }

    /**
     * Start a tab after this element, with the given name / title
     *
     * Can ofcourse only be used in tabbed forms.
     *
     * Usage:
     * <code>
     * $this->addTab('tab1')->h3('First tab');
     * </code>
     * or
     * <code>
     * $this->addTab('tab1', 'value', 'First tab');
     * </code>
     *
     * @param string $name Name of element
     * @param mixed $arrayOrKey1 Ra::pairs() name => value array
     * @return mixed
     */
    public function addTab($name, $arrayOrKey1 = null, $value1 = null, $key2 = null, $value2 = null)
    {
        $options = func_get_args();
        $options = Ra::pairs($options, 1);

        $options = $this->_mergeOptions($name, $options,self::DISPLAY_OPTIONS, self::TAB_OPTIONS);

        if (method_exists($this->form, 'addTab')) {
            return $this->form->addTab($name, isset($options['value']) ? $options['value'] : null);
        } else {
            $element = new \MUtil\Form\Element\Tab($name, $options);
            $this->form->addElement($element);
        }

        return $element;
    }

    /**
     *
     * @param string $elementName
     * @param mixed $validator
     * @param boolean $breakChainOnFailure
     * @param mixed $options
     * @return mixed
     */
    public function addValidator($elementName, $validator, $breakChainOnFailure = false, $options = array())
    {
        $element = $this->form->getElement($elementName);
        $element->addValidator($validator, $breakChainOnFailure, $options);

        return $this;
    }

    public function format($name, $value)
    {
        $element = $this->$name;

        if ($element instanceof \Zend_Form_Element) {
            $element->setValue($value);
            return $element->render(\Zalt\Html\Html::getRenderer()->getView());
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
     * Retrieve a tab from a \Gems\TabForm to add extra content to it
     *
     * @param string $name
     * @return mixed
     */
    public function getTab($name)
    {
        if (method_exists($this->form, 'getTab')) {
            return $this->form->getTab($name);
        }
    }

    public function getValidatorBridge(): ValidatorBridgeInterface
    {
        if (! isset($this->validatorBridge)) {
            $this->validatorBridge = $this->dataModel->getBridgeFor(LaminasValidatorBridge::class);
        }
        return $this->validatorBridge;
    }

    public function setForm(mixed $form): void
    {
        if ($form instanceof \Zend_Form) {
            $this->form = $form;

            $this->form->setName($this->metaModel->getName());
        } else {
            throw new MetaModelException(sprintf("Form parameter must be an instance of \Zend_Form in %s->setForm(). Object of '%s' class given instead.", __CLASS__, get_class($form)));
        }
    }
}