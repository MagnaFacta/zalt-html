<?php

declare(strict_types=1);

/**
 * @package    Zalt
 * @subpackage Html\Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Form;

use Zalt\Html\HtmlInterface;

/**
 * @package    Zalt
 * @subpackage Html\Form
 * @since      Class available since version 1.0
 */
class InputElement extends \Zalt\Html\HtmlElement implements InputInterface
{
    /**
     * @var null|string|HtmlInterface
     */
    protected mixed $label = null;

    public function __construct(string $name, string $typeName = 'text', ...$args)
    {
        if (! isset($args['type'])) {
            $args['type'] = $typeName;
        }

        parent::__construct('input', $args);

        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->_attribs['id'] ?? $this->getName();
    }

    /**
     * @return null|string|HtmlInterface
     */
    public function getLabel(): mixed
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_attribs['name'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->_attribs['value'] ?? '';
    }

    public function render()
    {
        if (! isset($this->_attribs['id'])) {
            $this->_attribs['id'] = $this->getId();
        }
        unset($this->_attribs['label']);

        $output = parent::render();

        $this->_attribs['label'] = $this->label;

        return $output;
    }

    public function setAttrib($name, $value)
    {
        if ('label' == $name) {
            $this->label = $value;
        }
        return parent::setAttrib($name, $value);
    }

    /**
     * @param string $name
     * @return InputInterface (continuation pattern_
     */
    public function setName(string $name): InputInterface
    {
        $this->_attribs['name'] = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return InputInterface (continuation pattern_
     */
    public function setValue(mixed $value): InputInterface
    {
        $this->_attribs['value'] = $value;
        return $this;
    }
}