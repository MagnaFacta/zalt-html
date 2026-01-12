<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Psr\Cache\CacheItemPoolInterface;
use Zalt\Base\TranslateableTrait;
use Zalt\Html\AElement;
use Zalt\Html\Form\FormElement;
use Zalt\Html\Html;
use Zalt\Html\HtmlElement;
use Zalt\Message\MessageStatus;
use Zalt\Message\MessageTrait;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
trait ConfirmSnippetTrait
{
    use MessageTrait;
    use TranslateableTrait;

    /**
     * The action to go to when the user clicks 'No'.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected string $abortUrl = '';

    /**
     * @var string Optional message to show after the action
     */
    protected string $afterActionMessage = '';

    /**
     * @var string Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    protected string $afterActionUrl = '';

    public string $buttonBlockedClass = 'disabled';

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

    protected ? CacheItemPoolInterface $cache = null;

    /**
     * Variable to set tags for cache cleanup after changes
     *
     * @var array
     */
    protected array $cacheTags = [];

    /**
     * The request parameter used to store the confirmation
     *
     * @var string Required
     */
    protected $confirmParameter = 'confirmed';

    /**
     * Field name for crsf protection field.
     *
     * @var string
     */
    protected string $csrfName = '__csrf';

    /**
     * The csrf token.
     *
     * @var string
     */
    protected ?string $csrfToken = null;

    protected string $formClass = '';

    /**
     * The question to as the user.
     *
     * @var string Optional
     */
    protected string $question;

    protected string $questionTag = 'p';

    /**
     * @var array Strings describing what is edited / saved for 1 item and more than 1 item
     */
    protected array $subjects = ['item', 'items'];

    protected function getAbortUrl(): string
    {
        return $this->abortUrl;
    }

    protected function getAfterActionUrl(): string
    {
        return $this->afterActionUrl;
    }

    protected function getCurrentUrl(): string
    {
        // @phpstan-ignore property.notFound
        return $this->requestInfo->getBasePath();
    }

    protected function getHtmlQuestion(): HtmlElement
    {
        $p = Html::create($this->questionTag);

        $p->append($this->getQuestion());
        $p->append(' ');

        $yes = $this->getYesButton();
        $p->append($yes);
        $no = $this->getNoButton();
        if ($no) {
            if ($yes instanceof FormElement) {
                $yes->append(' ');
                $yes->append($no);
            } else {
                $p->append(' ');
                $p->append($no);
            }
        }

        return $p;
    }

    protected function getMessage(): string
    {
        return $this->afterActionMessage;
    }

    public function getNoButton():? HtmlElement
    {
        $url = $this->getAbortUrl();
        if ($url) {
            return new AElement(
                [$url],
                $this->getNoButtonLabel(),
                ['class' => $this->buttonNoClass]
            );
        }

        return null;
    }

    public function getNoButtonLabel(): String
    {
        return $this->_('No');
    }

    /**
     * The delete question.
     *
     * @return string
     */
    protected function getQuestion(): string
    {
        if (isset($this->question)) {
            return $this->question;
        } else {
            return $this->_('Do you really want to do this?');
        }
    }

    /**
     * Helper function to allow generalized statements about the items in the model to used specific item names.
     *
     * @param int $count
     * @return string
     */
    public function getTopic($count = 1)
    {
        return $this->plural($this->subjects[0], $this->subjects[1], $count);
    }

    public function getYesButton(): HtmlElement
    {
        if ($this->useForm()) {
            $form = new FormElement([
                'action' => $this->getCurrentUrl(),
                'class'  => $this->formClass,
                ]);
            $form->addHidden($this->csrfName, $this->csrfToken);
            $this->insertExtraElements($form);
            $form->addElement($this->confirmParameter, 'submit', $this->getYesButtonLabel(), [
                'class' => $this->buttonYesClass,
            ]);

            return $form;
        }

        return new AElement(
            [$this->getCurrentUrl(), $this->confirmParameter => 1],
            $this->getYesButtonLabel(),
            ['class' => $this->buttonYesClass]
        );
    }

    public function getYesButtonLabel(): string
    {
        return $this->_("Yes");
    }

    public function insertExtraElements(FormElement $form): void
    { }

    public function isActionConfirmed(): bool
    {
        $this->prepareHtml();

        if ($this->useForm()) {
            $postParams = $this->requestInfo->getRequestPostParams();
        } else {
            $postParams = $this->requestInfo->getRequestQueryParams();
        }
            // @xphpstan-ignore property.notFound
        if (isset($postParams[$this->confirmParameter])) {
            $performed = false;
            try {
                $performed = $this->performAction();
            } catch (\Exception $e) {
                // Poor man's solution to catch foreign key violations.
                // Report the constraint and foreign key to give an indication
                // which object is referencing.
                if (!preg_match('/(Integrity constraint violation|foreign key constraint fails).*CONSTRAINT (\S+) FOREIGN KEY (\S+)/', $e->getMessage(), $m)) {
                    throw($e);
                }
                $msg = sprintf($this->_('Could not delete, object is referenced by another object. Constraint: %s, foreign key: %s'), trim($m[2], '`()'), trim($m[3], '`()'));
                // @phpstan-ignore-next-line
                $this->messenger->addMessages([$msg], MessageStatus::Warning);
            }
            if ($performed) {
                if ($this->cacheTags && ($this->cache instanceof \Symfony\Contracts\Cache\TagAwareCacheInterface)) {
                    $this->cache->invalidateTags($this->cacheTags);
                }

                $msg = $this->getMessage();
                if ($msg) {
                    $this->addMessage($msg);
                }

                $this->setAfterActionRoute();
                return (bool) $this->afterActionUrl;
            }
        }
        return false;
    }

    /**
     * Tell what to do and set afterSaveRouteUrl
     */
    abstract protected function performAction(): bool;

    public function prepareHtml()
    {
        if ($this->buttonClass) {
            if ($this->buttonNoClass) {
                $this->buttonNoClass .= ' ' . $this->buttonClass;
            } else {
                $this->buttonNoClass = $this->buttonClass;
            }
            if ($this->buttonYesClass) {
                $this->buttonYesClass .= ' ' . $this->buttonClass;
            } else {
                $this->buttonYesClass = $this->buttonClass;
            }
        }

    }

    public function useForm(): bool
    {
        // Csrf exists => we use a form
        return $this->csrfName && $this->csrfToken;
    }
}
