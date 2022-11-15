<?php

declare(strict_types=1);

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use \Zalt\Model\Data\FullDataInterface;

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
trait FullDataGenericModelTrait
{
    /**
     *
     * @var \Zalt\Model\Data\FullDataInterface
     */
    protected $model;

    /**
     * Creates the model
     *
     * @return \Zalt\Model\Data\FullDataInterface
     */
    protected function createModel(): FullDataInterface
    {
        return $this->model;
    }
}