<?php

declare(strict_types=1);


/**
 * @package    Zalt
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Snippets;

use Zalt\Base\RequestInfo;
use Zalt\Html\Marker;
use Zalt\Model\MetaModelInterface;

/**
 * @package    Zalt
 * @subpackage Snippets
 * @since      Class available since version 1.0
 */
trait ModelTextFilterTrait
{
    /**
     * @var string The parameter name that contains the search text
     */
    protected string $textSearchField = 'search';

    public function cleanUpTextFilter(string $searchText) : array
    {
        return array_filter(explode(' ', preg_replace("/[^a-z0-9]/", " ", strtolower($searchText))));
    }

    public function getTextFilter(MetaModelInterface $metaModel, string $searchText): array
    {
        $output = [];
        $searches = $this->cleanUpTextFilter($searchText);
        if ($searches) {
            $fields = $metaModel->getCol('label');
            foreach ($metaModel->getCol('no_text_search') as $field => $value)  {
                if ($value) {
                    unset($fields[$field]);
                }
            }

            $marker = new Marker($searches, 'strong', 'UTF-8');
            $metaModel->setCol(array_keys($fields), ['markCallback' => [$marker, 'mark']]);

            $options = $metaModel->getCol('multiOptions');

            foreach ($searches as $search) {
                $current = [];
                foreach ($fields as $field => $label) {
                    if (isset($options[$field])) {
                        $inValues = [];
                        foreach ($options[$field] as $value => $label) {
                            if (!is_string($label)) {
                                continue;
                            }
                            if (str_contains(strtolower($label), $search)) {
                                $inValues[] = $value;
                            }
                        }
                        if ($inValues) {
                            $current[$field] = $inValues;
                        }
                    } else {
                        switch ($metaModel->get($field, 'type')) {
                            case MetaModelInterface::TYPE_DATE:
                            case MetaModelInterface::TYPE_DATETIME:
                            case MetaModelInterface::TYPE_TIME:
                            case MetaModelInterface::TYPE_NUMERIC:
                                if (intval($search)) {
                                    $current[$field] = [MetaModelInterface::FILTER_CONTAINS => $search];
                                }
                                break;
                            case MetaModelInterface::TYPE_CHILD_MODEL:
                                break;
                            default:
                                $current[$field] = [MetaModelInterface::FILTER_CONTAINS => $search];
                        }
                    }
                }
                if ($current) {
                    $output[] = $current;
                }
            }
        }
        return $output;
    }

    public function processTextFilter(array $filter, MetaModelInterface $metaModel, array|bool $searchData): array
    {
        if ($searchData && isset($searchData[$this->textSearchField])) {
            // Add generic text search filter and marker
            $searchFilter = $this->getTextFilter($metaModel, $searchData[$this->textSearchField]);
            if ($searchFilter) {
                return array_merge($filter, $searchFilter);
            }
        }

        return $filter;
    }
}
