<?php

/**
 *
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Psr\Cache\CacheItemPoolInterface;
use Zalt\Model\Data\DataReaderInterface;
use Zalt\Model\Data\FullDataInterface;
use Zalt\Snippets\ModelBridge\DetailTableBridge;

/**
 * A snippet asking for confirmation before performing a save of predertemined data
 *
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \Zalt version 1.7.2 30-sep-2015 18:49:24
 * @deprecated Replaced by ModelConfirmSnippetAbstract
 */
abstract class ModelConfirmDataChangeSnippetAbstract extends \Zalt\Snippets\ModelDetailTableSnippetAbstract
{
    /**
     * The action to go to when the user clicks 'No'.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected $abortAction = 'show';

    /**
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @var mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    protected $afterSaveRouteUrl;

    /**
     * Optional class for use on buttons, overruled by $buttonNoClass and $buttonYesClass
     *
     * @var string
     */
    protected $buttonClass;

    /**
     * Optional class for use on No button
     *
     * @var string
     */
    protected $buttonNoClass;

    /**
     * Optional class for use on Yes button
     *
     * @var string
     */
    protected $buttonYesClass;

    /**
     *
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    public $cacheTags;

    /**
     * The action to go to when the user clicks 'Yes' and the data is changed.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected $confirmAction = 'show';

    /**
     * The question to ask the user.
     *
     * @var string Required
     */
    protected $confirmQuestion;

    /**
     * The request parameter used to store the confirmation
     *
     * @var string Required
     */
    protected $confirmParameter = 'confirmed';

    /**
     * Variable to either keep or throw away the request data
     * not specified in the route.
     *
     * @var boolean True then the route is reset
     */
    public $resetRoute = false;

    /**
     * Required: the data to save to the model when saving
     *
     * @var array
     */
    protected $saveData;

    /**
     * Creates the model
     *
     * @return \Zalt\Model\ModelAbstract
     */
    // protected function createModel()

    /**
     * The question.
     *
     * @return string
     */
    protected function getQuestion()
    {
        if ($this->confirmQuestion) {
            return $this->confirmQuestion;
        } else {
            return $this->_('Are you sure?');
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
        return $this->afterSaveRouteUrl;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \Zalt\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput(): bool
    {
        $queryParams = $this->requestInfo->getRequestQueryParams();
        if (isset($queryParams[$this->confirmParameter])) {
            $this->performAction();

            $redirectRoute = $this->getRedirectRoute();
            return empty($redirectRoute);

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
        /**
         * @var FullDataInterface $model
         */
        $model = $this->getModel();

        $model->save($this->saveData + $model->getFilter());

        if ($this->cacheTags && ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface)) {
            $this->cache->invalidateTags((array) $this->cacheTags);
        }

        $this->setAfterDeleteRoute();
    }

    /**
     * Set what to do when the form is 'finished'.
     */
    protected function setAfterDeleteRoute()
    {
        // Default is just go to the index
        /*if ($this->confirmAction && ($this->request->getActionName() !== $this->confirmAction)) {
            $this->afterSaveRouteUrl = array(
                $this->request->getControllerKey() => $this->request->getControllerName(),
                $this->request->getActionKey() => $this->confirmAction,
                $this->confirmParameter => null, // make empty
                );
        }*/
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param DetailTableBridge $bridge
     * @param DataReaderInterface $dataModel
     * @return void
     */
    protected function setShowTableFooter(DetailTableBridge $bridge, DataReaderInterface $dataModel)
    {
        $footer = $bridge->getTable()->tfrow();

        $footer->append($this->getQuestion());
        $footer->append(' ');
        $footer->a(
                array($this->confirmParameter => 1),
                $this->_('Yes'),
                array('class' => $this->buttonYesClass)
                );
        $footer->append(' ');
        $footer->a(
                array('action' => $this->abortAction),
                $this->_('No'),
                array('class' => $this->buttonNoClass)
                );
    }
}
