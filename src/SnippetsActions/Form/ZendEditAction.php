<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions\Form;

use Zalt\Snippets\Zend\ZendModelFormSnippet;

/**
 *
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
class ZendEditAction extends EditActionAbstract
{
    /**
     * @var array Of snippet class names
     */
    protected array $_snippets = [
        ZendModelFormSnippet::class,
        ];

    /**
     * Automatically calculate and set the width of the labels
     * @var int
     */
    public int $layoutAutoWidthFactor = 0;

    /**
     * Set the (fixed) width of the labels, if zero: is calculated
     * @var int
     */
    public int $layoutFixedWidth = 0;
}