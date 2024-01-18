<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html\Paginator;

use Zalt\Html\Html;
use Zalt\Html\HtmlElement;

/**
 * @package    Zalt
 * @subpackage Html\Paginator
 * @since      Class available since version 1.0
 */
trait CurrentUrlPaginatorTrait
{
    protected string $currentPageClass = 'active';

    /**
     * @var array Optional current url start for links on the page
     */
    protected array $currentUrl = [];

    protected string $itemsDisabledClass = '';

    protected string $itemsLinkClass = '';

    protected string $pageDisabledClass = '';

    protected string $pageLinkClass = '';

    protected string $pageLinkLabelSeparator = '';

    protected function getItemLink(int $itemCount, mixed $label): HtmlElement
    {
        if ($itemCount == $this->pageItems) {
            return Html::create()->span($label, ['class' => $this->itemsDisabledClass]);
        }
        return Html::create()->a(
            $this->getUrl([PaginatorInterface::REQUEST_ITEMS => $itemCount, PaginatorInterface::REQUEST_PAGE => $this->pageNumber]),
            $label,
            ['class' => $this->itemsLinkClass]
        );
    }

    protected function getPageLink(int $pageNumber, ?string $symbol, ?string $label, bool $isSpecialLink): ?HtmlElement
    {
        if (null === $label && null === $symbol) {
            return null;
        }

        if ($pageNumber == $this->pageNumber) {
            $class = $this->pageDisabledClass;
            if (! $isSpecialLink) {
                $class .= ' ' . $this->currentPageClass;
            }
            $container = Html::create()->span($symbol, ['class' => $class]);
            if ($label) {
                $container->append(Html::create('span', $label, ['class' => 'label']));
            }
            return $container;
        }

        $link = Html::create()->a(
            $this->getUrl([PaginatorInterface::REQUEST_PAGE => $pageNumber, PaginatorInterface::REQUEST_ITEMS => $this->pageItems]),
            $symbol,
            ['class' => $this->pageLinkClass, 'title' => $label]
        );
        if ($label) {
            $link->append($this->pageLinkLabelSeparator);
            $link->append(Html::create('span', $label, ['class' => 'label']));
        }
        return $link;
    }

    protected function getUrl(array $params = [])
    {
        return array_merge($this->currentUrl, $params);
    }

    public function setCurrentUrl(array $currentUrl)
    {
        $this->currentUrl = $currentUrl;
        return $this;
    }
}