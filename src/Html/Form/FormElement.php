<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Html\Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Form;

use Zalt\Html\ElementInterface;

/**
 * A simple form without any special markup
 *
 * @package    Zalt
 * @subpackage Html\Form
 * @since      Class available since version 1.0
 */
class FormElement extends \Zalt\Html\HtmlElement
{
    /**
     * @var array The actual storage of the attributes.
     */
    protected $_attribs = ['method' => 'POST'];

    /**
     * @var InputInterface[]
     */
    private array $_elements = [];

    public function __construct(...$args)
    {
        parent::__construct('form', $args);
    }

    private function _getElementFor(ElementInterface $element, string $id):? InputInterface
    {
        foreach ($element as $key => $contentItem) {
            if ($contentItem instanceof InputInterface) {
                if ($id === $contentItem->getId()) {
                    return $contentItem;
                }
                // Fill array while we are at it
                $this->_elements[$contentItem->getId()] = $contentItem;
            }
            if ($contentItem instanceof FormElement) {
                $output = $this->getElement($id);
                if ($output) {
                    return $output;
                }
            } elseif ($contentItem instanceof ElementInterface) {
                $output = $this->_getElementFor($contentItem, $id);
                if ($output) {
                    return $output;
                }
            }
        }
        return null;
    }

    public function addElement(string $name, string $type = 'text', ?string $value = null, array $options = []): InputElement
    {
        $element = new InputElement($name, $type, $options);
        $this->append($element);

        if ($value) {
            $element->setValue($value);
        }

        return $element;
    }

    public function addHidden(string $name, ?string $value = null): InputElement
    {
        return $this->addElement($name, 'hidden', $value);
    }

    public function getElement($id):? InputInterface
    {
        if (isset($this->_elements[$id])) {
            return $this->_elements[$id];
        }

        $contentItem = $this->_getElementFor($this, $id);

        if ($contentItem) {
            $this->_elements[$id] = $contentItem;
            return $this->_elements[$id];
        }

        // Keep on searching as long as the id does not exist
        return null;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_attribs['name'] ?? '';
    }

    /**
     * @param string $name
     * @return FormElement (continuation pattern)
     */
    public function setName(string $name): FormElement
    {
        $this->_attribs['name'] = $name;
        return $this;
    }
}