<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

/**
 * Displays multiple items from a model in a tabel by row using
 * the model set through the $model snippet parameter.
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.2
 */
class ModelTableSnippet extends ModelTableSnippetAbstract
{
    use DataReaderGenericModelTrait;
}
