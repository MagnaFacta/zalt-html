<?php

/**
 *
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Snippets;

use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Html\Html;
use Zalt\Html\HtmlElement;
use Zalt\Html\HtmlInterface;
use Zalt\Html\Sequence;
use Zalt\Base\MessageTrait;
use Zalt\Base\TranslateableTrait;

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
 * @package    Zalt
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
abstract class SnippetAbstract implements SnippetInterface
{
    use MessageTrait;
    use TranslateableTrait;
    
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

    public function __construct(array $config, ServerRequestInterface $request, TranslatorInterface $translate)
    {
        // We're setting trait variables so no constructor promotion
        $this->translate = $translate;
        $this->request   = $request;
        $this->messenger = $request->getAttribute('flash');
        
        // Set variables from config
        foreach (get_object_vars($this) as $property => $object) {
            if (isset($config[$property]) && ('_' != substr($property, 0, 1))) {
                $this->$property = $config[$property]; 
            }
        } 
    }
    
    /**
     * Applies the $this=>attributes and $this->class snippet parameters to the
     * $html element.
     *
     * @param \Zalt\Html\HtmlElement $html Element to apply the snippet parameters to.
     */
    protected function applyHtmlAttributes(HtmlElement $html)
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
     * @return mixed Something that can be rendered
     */
    public function getHtmlOutput()
    {
        return null;
    }

    /**
     * Helper function for snippets returning a sequence of Html items.
     *
     * @return \Zalt\Html\Sequence
     */
    protected function getHtmlSequence()
    {
        return new Sequence();
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
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute(): string
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
     *
     * @return bool
     */
    public function hasHtmlOutput(): bool
    {
        return true;
    }

    /**
     * When there is a redirectRoute this function will execute it.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     */
    public function redirectRoute()
    {
        if ($url = $this->getRedirectRoute()) {
            //\Zalt\EchoOut\EchoOut::track($url);

            $router = $this->getRedirector();
            $router->setExit($router->getExit() && ! (\Zalt\Console::isConsole() || \Zend_Session::$_unitTestEnabled));
            $router->gotoRoute($url, null, $this->resetRoute);
        }
    }

    /**
     * Render a string that becomes part of the HtmlOutput of the view
     *
     * You should override either getHtmlOutput() or this function to generate output
     *
     * @return string Html output
     */
    public function render()
    {
        if ($this->getRedirectRoute()) {
            $this->redirectRoute();

        } else {
            $html = $this->getHtmlOutput();

            if ($html) {
                if ($html instanceof HtmlInterface) {
                    if ($html instanceof HtmlElement) {
                        $this->applyHtmlAttributes($html);
                    }
                    return $html->render();
                } else {
                    return Html::renderAny($html);
                }
            }
        }
    }
}
