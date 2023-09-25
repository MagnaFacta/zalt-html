<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage SnippetsActions
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\SnippetsActions;

/**
 * Marker Interface to skip Csrf checking
 * @package    Zalt
 * @subpackage SnippetsActions
 * @since      Class available since version 1.0
 */
interface NoCsrfInterface extends PostActionInterface
{ }