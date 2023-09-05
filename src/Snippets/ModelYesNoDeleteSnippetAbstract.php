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

use Zalt\Model\Data\DataReaderInterface;
use Zalt\Snippets\ModelBridge\DetailTableBridge;

/**
 * Ask Yes/No conformation for deletion and deletes item when confirmed.
 *
 * Can be used for other uses than delete by overriding performAction().
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4.4
 */
abstract class ModelYesNoDeleteSnippetAbstract extends ModelDetailTableSnippetAbstract
{
    /**
     * The action to go to when the user clicks 'No'.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected string $abortUrl = '';

    /**
     * @var string AFter action actual return url 
     */
    protected string $afterActionRouteUrl = '';
    
    /**
     * @var string Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    protected string $afterDeleteUrl = '';

    /**
     * Optional class for use on buttons, overruled by $buttonNoClass and $buttonYesClass
     *
     * @var ?string
     */
    protected ?string $buttonClass;

    /**
     * Optional class for use on No button
     *
     * @var ?string
     */
    protected ?string $buttonNoClass = null;

    /**
     * Optional class for use on Yes button
     *
     * @var ?string
     */
    protected ?string $buttonYesClass = null;

    /**
     * The request parameter used to store the confirmation
     *
     */
    protected string $confirmParameter = 'confirmed';

    /**
     * @var ?string Optional The question to ask the user.
     */
    protected ?string $deleteQuestion;

    /**
     * @return mixed|null|\Zalt\Html\HtmlElement
     * /
    public function getHtmlOutput()
    {
        if (isset($this->buttonClass)) {
            if (! $this->buttonNoClass) {
                $this->buttonNoClass = $this->buttonClass;
            }
            if (! $this->buttonYesClass) {
                $this->buttonYesClass = $this->buttonClass;
            }
        }

        $footer = Html::div();
        $startUrl = $this->requestInfo->getBasePath();

        $footer[] = $this->getQuestion();
        $footer[] = ' ';
        $footer->a(
            [$startUrl, $this->confirmParameter => 1],
            $this->_('Yes'),
            ['class' => $this->buttonYesClass]
        );
        if ($this->abortUrl) {
            $footer[] = ' ';
            $footer->a(
                [$this->abortUrl],
                $this->_('No'),
                ['class' => $this->buttonNoClass]
            );
        }
        return $footer;
    }

    /**
     * The delete question.
     *
     * @return string
     */
    protected function getQuestion()
    {
        if (isset($this->deleteQuestion)) {
            return $this->deleteQuestion;
        } else {
            return $this->_('Do you really want to delete this item?');
        }
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @return string|null Nothing or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute(): ?string
    {
        return $this->afterActionRouteUrl;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     */
    public function hasHtmlOutput(): bool
    {
        $queryParams = $this->requestInfo->getRequestQueryParams();
        if (isset($queryParams[$this->confirmParameter])) {
            $this->performAction();

            return false;

        } else {
            return parent::hasHtmlOutput();
        }
    }

    /**
     * Overrule this function if you want to perform a different
     * action than deleting when the user choose 'yes'.
     */
    protected function performAction()
    {
        $model = $this->getModel();
        $model->delete();

        $this->setAfterDeleteRoute();
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterDeleteRoute()
    {
        // Default is just go to the index
        if ($this->afterDeleteUrl) {
            $this->afterActionRouteUrl = $this->afterDeleteUrl;
        }
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     */
    protected function setShowTableFooter(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    {
        if (isset($this->buttonClass)) {
            if (! $this->buttonNoClass) {
                $this->buttonNoClass = $this->buttonClass;
            }
            if (! $this->buttonYesClass) {
                $this->buttonYesClass = $this->buttonClass;
            }
        }

        $footer = $bridge->tfrow();
        $startUrl = $this->requestInfo->getBasePath();

        $footer[] = $this->getQuestion();
        $footer[] = ' ';
        $footer->a(
                [$startUrl, $this->confirmParameter => 1],
                $this->_('Yes'),
                ['class' => $this->buttonYesClass]
                );
        if ($this->abortUrl) {
            $footer[] = ' ';
            $footer->a(
                [$this->abortUrl],
                $this->_('No'),
                ['class' => $this->buttonNoClass]
            );
        }
    }
}
