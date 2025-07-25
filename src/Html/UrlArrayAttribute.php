<?php

/**
 *
 * @package    Zalt
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace Zalt\Html;

/**
 * An array attribute that forms url's using Zend framework routing
 *
 * @package    Zalt
 * @subpackage Html
 * @since      Class available since \Zalt version 1.0
 */
class UrlArrayAttribute extends ArrayAttribute
{
    /**
     * Seperator used to separate multiple items
     *
     * @var string
     */
    protected $_separator = '&';

    /**
     * Get the scalar value of this attribute.
     *
     * @return string | int | null
     */
    public function get()
    {
        return self::toUrlString($this->getArray());
    }

    /**
     * Function that allows subclasses to define their own
     * mechanism for redering the key/value combination.
     *
     * E.g. key=value instead of just the value.
     *
     * @param scalar $key
     * @param string $value Output escaped value
     * @return string
     */
    public function getKeyValue($key, $value): string
    {
        return $key . '=' . $value;
    }

    /**
     * Returns relative url string using the current module, controller and action when
     * none where specified.
     *
     * This is url is encoded for url usage, but not for use as attribute values,
     * i.e. this helper function is used for generating url's for internal use.
     *
     * @param array $url
     * @return string
     */
    public static function toUrlString(array $url): string
    {
        $urlString = '';
        $urlParameters = [];

        try {
            if (isset($url[0]) && (1 == count($url))) {
                $parts = explode('?', $url[0]);
                if (isset($parts[1])) {
                    parse_str($parts[1], $params);
                    unset($parts[1]);
                    $parts = array_merge($parts, $params);
                }
                $url = $parts;
            }
            $attribs = Html::getRenderer()->renderArray($url, false);
            if (is_array($attribs)) {
                foreach ($attribs as $key => $value) {
                    if (strlen($value)) {
                        if (is_int($key)) {
                            $urlString .= $value;
                        } elseif ($key) {
                            // Prevent double escaping by using rawurlencode() instead
                            // of urlencode()
                            $urlParameters[$key] = rawurlencode($value);
                        }
                    }
                }
            } else {
                $urlString = $attribs;
            }

            if (str_contains($urlString, '//')) {
                $urlString = preg_replace('!([^:])//!', '\1/', $urlString);
            }
            $urlString = rtrim($urlString, '/');

            if ($urlParameters) {
                $params = [];
                foreach ($urlParameters as $key => $value) {
                    $params[] = $key . '=' . $value;
                }
                if (str_contains($urlString, '?')) {
                    return $urlString . '&' . implode('&', $params);
                } else {
                    return $urlString . '?' . implode('&', $params);
                }
            } else {
                return $urlString;
            }
        } catch (\Throwable $exception) {
            return $exception->getMessage();
        }
        
    }
}
