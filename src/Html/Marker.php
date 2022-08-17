<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace Zalt\Html;

use Zalt\Late\Late;
use Zalt\Late\LateInterface;

/**
 * Class to mark text in HTML content, e.g. to nmark the result of a search statement
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Marker
{
    /**
     * Marker for tags in
     */
    const TAG_MARKER = "\0";

    /**
     *
     * @var string Any other attributes to add to the tag
     */
    private $_attributes = null;

    /**
     *
     * @var array Calculated array containing the texts to replace
     */
    private $_replaces;

    /**
     *
     * @var array Calculated array containing the texts to search for
     */
    private $_searches;

    /**
     *
     * @var string The element name for the tagged parts
     */
    private $_tag;

    /**
     * Create the marker
     *
     * @param array $searches The texts parts to mark
     * @param string $tag The element name for the tagged parts
     * @param string $attributes Any other attributes to add to the tag
     */
    public function __construct($searches, $tag, $attributes = 'class="marked"')
    {
        $this->_tag      = $tag;
        $this->_searches = (array) $searches;

        if ($attributes) {
            $this->_attributes = ' ' . trim($attributes) . ' ';
        }

    }

    /**
     * Replace the tag markers with the actual tag
     *
     * @param string $text
     * @return string
     */
    private function _fillTags($text)
    {
        return str_replace(
                array('<' . self::TAG_MARKER, '</' . self::TAG_MARKER),
                array('<' . $this->_tag . $this->_attributes, '</' . $this->_tag),
                $text);
    }

    /**
     * Find and replace the actual texts
     *
     * @param string $text
     * @return string
     */
    private function _findTags($text)
    {
        return str_ireplace($this->_searches, $this->_replaces, $text);
    }

    /**
     * Mark the searches in $value
     *
     * @param mixed $value Late, Html, Raw or string
     * @return HtmlInterface
     */
    public function mark($value)
    {
        if (! $this->_replaces) {
            // Late setting of search & replaces
            $searches = $this->_searches;
            $this->_searches = array();

            // Do not use the $tag itself here: str_replace will then replace
            // the text of tag itself on later finds
            $topen  = '<' . self::TAG_MARKER . '>';
            $tclose = '</' . self::TAG_MARKER . '>';

            foreach ((array) $searches as $search) {
                $searchHtml = Html::escape($search);
                $this->_searches[] = $searchHtml;
                $this->_replaces[] = $topen . $searchHtml . $tclose;
            }
        }

        if ($value instanceof LateInterface) {
            $value = Late::rise($value);
        }

        if ($value instanceof Raw) {
            $values = array();
            // Split into HTML Elements
            foreach ($value->getElements() as $element) {
                if (strlen($element)) {
                    switch ($element[0]) {
                        case '<':
                        case '&':
                            // No replace in element
                            $values[] = $element;
                            break;

                        default:
                            $values[] = $this->_findTags($element);
                    }
                }
            }
            // \Zalt\EchoOut\EchoOut::r($values);

            return $value->setValue($this->_fillTags(implode('', $values)));

        } elseif ($value instanceof HtmlElement) {
            foreach ($value as $key => $item) {
                // \Zalt\EchoOut\EchoOut::r($key);
                $value[$key] = $this->mark($item);
            }
            return $value;

        } elseif ($value || ($value === 0)) {
            // \Zalt\EchoOut\EchoOut::r($value);
            $valueHtml = Html::escape($value);

            $valueTemp = $this->_findTags($valueHtml);

            return new Raw($this->_fillTags($valueTemp));
        }
    }

    /**
     * Function to allow later setting of tag name.
     *
     * @param string $tagName Html element tag name
     * @return Marker (continuation pattern)
     */
    public function setTagName($tagName)
    {
        $this->_tag = $tagName;

        return $this;
    }
}

