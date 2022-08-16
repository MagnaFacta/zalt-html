<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

use MUtil\Controller\Action;
use Psr\Http\Message\ResponseInterface;

/**
 * An abstract class for building snippets. Sub classes should override at least
 * getHtmlOutput() or render() to generate output.
 *
 * This class add's to the interface helper variables and functions for:
 * - attribute use: $this->attributes, $this->class & applyHtmlAttributes()
 * - Html creation: $this->getHtmlSequence()
 * - messaging:     $this->_messenger, addMessage() & getMessenger()
 * - rerouting:     $this->resetRoute & redirectRoute()
 * - translation:   $this->translate, _() & plural()
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
abstract class SnippetAbstract extends \MUtil\Translate\TranslateableAbstract
    implements \MUtil\Snippets\SnippetInterface
{
    /**
     *
     * @var \Mezzio\Flash\FlashMessagesInterface
     */
    private $messenger;

    /**
     * Attributes (e.g. class) for the main html element
     *
     * @var array
     */
    protected $attributes;

    /**
     * Shortfix to add class attribute
     *
     * @var string
     */
    protected $class;

    /**
     * @var \MUtil\Controller\Action\Helper\Redirector
     */
    protected $redirector;

    /**
     * Variable to either keep or throw away the request data
     * not specified in the route.
     *
     * @var boolean True then the route is reset
     */
    public $resetRoute = false;

    /**
     * Adds one or more messages to the session based message store.
     *
     * @param mixed $message_args Can be an array or multiple argemuents. Each sub element is a single message string
     * @return self (continuation pattern)
     */
    public function addMessage(mixed $message, string $status = 'warning')
    {
        if ($this->messenger) {
            $messages = $this->messenger->getFlash(Action::$messengerKey, []);
            $messages[] = [$message, $status];
            $this->messenger->flash(Action::$messengerKey, $messages);
        }

        return $this;
    }

    /**
     * Applies the $this=>attributes and $this->class snippet parameters to the
     * $html element.
     *
     * @param \MUtil\Html\HtmlElement $html Element to apply the snippet parameters to.
     */
    protected function applyHtmlAttributes(\MUtil\Html\HtmlElement $html)
    {
        if ($this->attributes) {
            foreach ($this->attributes as $name => $value) {
                if (! is_numeric($name)) {
                    $html->appendAttrib($name, $value);
                }
            }
        }
        if ($this->class) {
            $html->appendAttrib('class', $this->class);
        }
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil\Html\HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        return null;
    }

    /**
     * Helper function for snippets returning a sequence of Html items.
     *
     * @return \MUtil\Html\Sequence
     */
    protected function getHtmlSequence()
    {
        return new \MUtil\Html\Sequence();
    }

    /**
     * @return \MUtil\Controller\Action\Helper\Redirector
     */
    protected function getRedirector()
    {
        if (!$this->redirector) {
            $this->redirector = new MUtil\Controller\Action\Helper\Redirector();
        }
        return $this->redirector;
    }

    /**
     * When hasHtmlOutput() is false a snippet code user should check
     * for a redirectRoute. Otherwise the redirect calling render() will
     * execute the redirect.
     *
     * This function should never return a value when the snippet does
     * not redirect.
     *
     * Also when hasHtmlOutput() is true this function should not be
     * called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute()
    { }

    public function getResponse(): ?ResponseInterface
    {
        return null;
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
        return true;
    }

    /**
     * When there is a redirectRoute this function will execute it.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     */
    public function redirectRoute()
    {
        if ($url = $this->getRedirectRoute()) {
            //\MUtil\EchoOut\EchoOut::track($url);

            $router = $this->getRedirector();
            $router->setExit($router->getExit() && ! (\MUtil\Console::isConsole() || \Zend_Session::$_unitTestEnabled));
            $router->gotoRoute($url, null, $this->resetRoute);
        }
    }

    /**
     * Render a string that becomes part of the HtmlOutput of the view
     *
     * You should override either getHtmlOutput() or this function to generate output
     *
     * @param \Zend_View_Abstract $view
     * @return string Html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        // \MUtil\EchoOut\EchoOut::r(sprintf('Rendering snippet %s.', get_class($this)));
        //
        // TODO: Change snippet workings.
        // All forms are processed twice if hasHtmlOutput() is called here. This is
        // a problem when downloading files.
        // However: not being able to call hasHtmlOutput() twice is not part of the original plan
        // so I gotta rework the forms. :(
        //
        // if ((!$this->hasHtmlOutput()) && $this->getRedirectRoute()) {
        if ($this->getRedirectRoute()) {
            $this->redirectRoute();

        } else {
            $html = $this->getHtmlOutput($view);

            if ($html) {
                if ($html instanceof \MUtil\Html\HtmlInterface) {
                    if ($html instanceof \MUtil\Html\HtmlElement) {
                        $this->applyHtmlAttributes($html);
                    }
                    return $html->render($view);
                } else {
                    return \MUtil\Html::renderAny($view, $html);
                }
            }
        }
    }
}
