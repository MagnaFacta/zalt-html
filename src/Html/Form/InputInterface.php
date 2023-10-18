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
interface InputInterface extends \Zalt\Html\ElementInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return null|string|HtmlInterface
     */
    public function getLabel(): mixed;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * @param string $name
     * @return InputInterface (continuation pattern_
     */
    public function setName(string $name): InputInterface;

    /**
     * @param mixed $value
     * @return InputInterface (continuation pattern_
     */
    public function setValue(mixed $value): InputInterface;
}