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
    protected bool $appendLabel = false;

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
        if ($this->label instanceof HtmlInterface) {
            $label = $this->label->render();
        } else {
            $label = (string) $this->label;
        }

        if ($this->appendLabel) {
            $output = parent::render() . ' ' . $label;
        } else {
            $output = $label . ' ' . parent::render();
        }

        return $output;
    }

    public function setappendLabel(bool $appendLabel)
    {
        $this->appendLabel = $appendLabel;
    }

    public function setAttrib($name, $value)
    {
        if ('label' == $name) {
            $this->setLabel($value);
            return $this;
        }
        if ('appendLabel' == $name) {
            $this->setappendLabel((bool) $value);
            return $this;
        }
        return parent::setAttrib($name, $value);
    }

    public function setLabel(null|string|HtmlInterface $label): void
    {
        $this->label = $label;
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