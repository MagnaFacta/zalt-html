<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Paginator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\TranslateableTrait;

/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @since      Class available since version 1.0
 */
trait TranslatablePaginatorTrait
{
    use TranslateableTrait;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translate = $translator;
        return $this;
    }
}