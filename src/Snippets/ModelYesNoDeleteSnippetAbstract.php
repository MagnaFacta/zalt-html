<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 * Ask Yes/No conformation for deletion and deletes item when confirmed.
 *
 * Can be used for other uses than delete by overriding performAction().
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4.4
 */
abstract class ModelYesNoDeleteSnippetAbstract extends \MUtil\Snippets\ModelVerticalTableSnippetAbstract
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
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    public $cacheTags;

    /**
     * The request parameter used to store the confirmation
     *
     * @var string Required
     */
    protected $confirmParameter = 'confirmed';

    /**
     * The action to go to when the user clicks 'Yes' and the data is deleted.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected $deleteAction = 'index';

    /**
     * The question to as the user.
     *
     * @var sting Optional
     */
    protected $deleteQuestion;

    /**
     * Variable to either keep or throw away the request data
     * not specified in the route.
     *
     * @var boolean True then the route is reset
     */
    public $resetRoute = true;

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry()
    {
        parent::afterRegistry();

        if ($this->buttonClass) {
            if (! $this->buttonNoClass) {
                $this->buttonNoClass = $this->buttonClass;
            }
            if (! $this->buttonYesClass) {
                $this->buttonYesClass = $this->buttonClass;
            }
        }
    }

    /**
     * The delete question.
     *
     * @return string
     */
    protected function getQuestion()
    {
        if ($this->deleteQuestion) {
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
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute()
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
     * {@see \MUtil\Registry\TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
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
        $model = $this->getModel();
        // \MUtil\EchoOut\EchoOut::track($model->getFilter());
        $model->delete();

        $this->setAfterDeleteRoute();

        if ($this->cacheTags && ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface)) {
            $this->cache->invalidateTags((array) $this->cacheTags);
        }
    }

    /**
     * Set what to do when the form is 'finished'.
     *
     * @return \MUtil\Snippets\ModelYesNoDeleteSnippetAbstract
     */
    protected function setAfterDeleteRoute()
    {
        // Default is just go to the index
        /*if ($this->deleteAction && ($this->request->getActionName() !== $this->deleteAction)) {
            $this->afterSaveRouteUrl = array(
                $this->request->getControllerKey() => $this->request->getControllerName(),
                $this->request->getActionKey() => $this->deleteAction,
                );
        }*/
    }

    /**
     * Set the footer of the browse table.
     *
     * Overrule this function to set the header differently, without
     * having to recode the core table building code.
     *
     * @param \MUtil\Model\Bridge\VerticalTableBridge $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @return void
     */
    protected function setShowTableFooter(\MUtil\Model\Bridge\VerticalTableBridge $bridge, \MUtil\Model\ModelAbstract $model)
    {
        $footer = $bridge->tfrow();

        $footer[] = $this->getQuestion();
        $footer[] = ' ';
        $footer->a(
                array($this->confirmParameter => 1),
                $this->_('Yes'),
                array('class' => $this->buttonYesClass)
                );
        $footer[] = ' ';
        $footer->a(
                array('action' => $this->abortAction),
                $this->_('No'),
                array('class' => $this->buttonNoClass)
                );
    }
}
