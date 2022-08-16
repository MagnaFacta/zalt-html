<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Html;

/**
 * An array attribute that forms url's using Zend framework routing
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class UrlArrayAttribute extends \MUtil\Html\ArrayAttribute
{
    /**
     *
     * @var boolean
     */
    protected $_routeReset = false;

    /**
     * Seperator used to separate multiple items
     *
     * @var string
     */
    protected $_separator = '&';

    /**
     * Specially treated types for a specific subclass
     *
     * @var array function name => class
     */
    protected $_specialTypes = array('setRouter' => 'Zend_Controller_Router_Route');

    /**
     *
     * @var \Zend_Controller_Router_Route
     */
    public $router;

    /**
     * Helper function thats fills a parameter from the request if it was not
     * already in the options array.
     *
     * This function ensures that e.g. the current controller is used instead
     * of the default 'index' controller.
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @param string $name
     * @param array $options
     */
    private static function _rerouteUrlOption(\Zend_Controller_Request_Abstract $request, $name, &$options)
    {
        if (! array_key_exists($name, $options)) {
            if ($value = $request->getParam($name)) {
                $options[$name] = $value;
            }
        }
    }

    /**
     * Get the scalar value of this attribute.
     *
     * @return string | int | null
     */
    public function get()
    {
        $url_string = '';
        $url_parameters = array();

        foreach ($this->_getArrayRendered() as $key => $value) {
            // $value = rawurlencode($value);
            if (is_numeric($key)) {
                $url_string .= $value;
            } elseif (strlen($value)) {
                // Prevent double escaping by using rawurlencode() instead
                // of urlencode() that is used by \Zend_Controller_Router_Route
                $url_parameters[$key] = rawurlencode($value);
            }
        }

        // If a string is defined we assume it is a full url
        if ($url_string) {
            if ($url_parameters) {
                foreach ($url_parameters as $key => $value) {
                    $params[] = $this->getKeyValue($key, $value);
                }
                return $url_string . '?' . implode($this->getSeparator(), $params);
            }

            return $url_string;
        }

        // Only when no string is defined we assume this is a Zend MVC url
        //
        // The array containing $url_parameters can be empty if all values
        // are default or null values.
        $request = $this->getRequest();

        // When the route is not reset, we add the request parameters here manually
        // as otherwise $router->assemble() will add the existing parameters without
        // escaping.
        if (! $this->getRouteReset()) {

            $params = [];
            if ($request instanceof \Zend_Controller_Request_Http) {
                // Make sure we don't get any POST's, but we do want the rest of the parameters
                $old = $request->getParamSources();
                $request->setParamSources(array('_GET'));
                $params = $request->getParams();
                $request->setParamSources($old);
            } elseif ($request instanceof \Zend_Controller_Request_Abstract) {
                $params = $request->getParams();
            }

            // Remove all (possibly empty) existing parameters from this object
            $params = array_diff_key($params, $this->getArray());

            foreach ($params as $key => $value) {
                // E.g. \Exceptions are stored as parameters
                // and posted arrays can be a problem as well
                if (is_scalar($value)) {
                    $url_parameters[$key] = rawurlencode($value);
                }
            }
        } elseif ($request instanceof \Zend_Controller_Request_Abstract) {
            // Make sure controllor, action, module are specified
            $url_parameters = self::rerouteUrl($request, $url_parameters);
        }
        return null;

        $router = $this->getRouter();
        return $router->assemble($url_parameters, null, true, false);
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
    public function getKeyValue($key, $value)
    {
        return $key . '=' . $value;
    }

    /**
     *
     * @return \Zend_Controller_Router_Route
     */
    public function getRouter()
    {
        if (! $this->router) {
            $this->router = \MUtil\Controller\Front::getRouter();
        }

        return $this->router;
    }

    /**
     * Whether or not to set route defaults with the paramter values
     *
     * @return type
     */
    public function getRouteReset()
    {
        return $this->_routeReset;
    }

    /**
     * Is this Url an Zend Framework Mvc url or a string with parameters.
     *
     * @return boolean
     */
    public function isMvcUrl()
    {
        foreach ($this->getArray() as $key => $value) {
            if (is_numeric($key)) {
                // Contains standalone string => not Zend
                return false;
            }
        }

        return true;
    }

    /**
     * Set the module, controller and action of an url parameter array to the current
     * module, controller and action, except when one of these items has already been
     * specified in the array.
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @param array $options An array of parameters (optionally including e.g. controller name) for the new url
     * @param boolean $addRouteReset Deprecated: add the 'RouteReset' parameter that is used by objects of this type to set RouteReset
     * @return array Url array with adapted utl's
     */
    public static function rerouteUrl(\Zend_Controller_Request_Abstract $request, $options, $addRouteReset = false)
    {
        self::_rerouteUrlOption($request, $request->getModuleKey(),     $options);
        self::_rerouteUrlOption($request, $request->getControllerKey(), $options);
        self::_rerouteUrlOption($request, $request->getActionKey(),     $options);

        if ($addRouteReset) {
            // Use of this paramter is deprecated
            $options['RouteReset'] = true;
        }

        return $options;
    }

    /**
     *
     * @param \Zend_Controller_Router_Route $router
     * @return \MUtil\Html\UrlArrayAttribute (continuation pattern)
     */
    public function setRouter(\Zend_Controller_Router_Route $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Whether or not to set route defaults with the paramter values
     *
     * @param boolean $routeReset
     * @return \MUtil\Html\UrlArrayAttribute (continuation pattern)
     */
    public function setRouteReset($routeReset = true)
    {
        $this->_routeReset = $routeReset;
        return $this;
    }

    /**
     * @deprecated
     * @param string $label
     * @return \Zend_Navigation_Page_Mvc
     */
    public function toPage($label)
    {
        if ($this->isMvcUrl()) {

            $options = $this->getArray();
            // Make sure controllor, action, module are specified
            $options = self::rerouteUrl($this->getRequest(), $options);
            $options['label'] = $label;

            return new \Zend_Navigation_Page_Mvc($options);

        } else {
            $options['url'] = $this;
            $options['label'] = $label;

            return new \Zend_Navigation_Page_Uri($options);
        }
    }

    /**
     * Returns relative url string using the current module, controller and action when
     * none where specified.
     *
     * This is url is encoded for url usage, but not for use as attribute values,
     * i.e. this helper function is used for generating url's for internal use.
     *
     * @param array $options Array of parameter values
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Router_Route $router
     * @return string
     */
    public static function toUrlString(array $options, \Zend_Controller_Request_Abstract $request = null,
            \Zend_Controller_Router_Route $router = null)
    {
        $base    = '';
        $encode  = true;
        $nobase  = false;
        $reset   = false;

        if (array_key_exists('Encode', $options)) {
            $encode = $options['Encode'];
            unset($options['Encode']);
        }
        if (array_key_exists('NoBase', $options)) {
            $nobase = $options['NoBase'];
            unset($options['NoBase']);
        }
        if (array_key_exists('RouteReset', $options)) {
            $reset = $options['RouteReset'];
            unset($options['RouteReset']);
        }

        if ($nobase || (null === $request) || (null === $router)) {
            $front = \Zend_Controller_Front::getInstance();

            if ($nobase) {
                $base = rtrim($front->getBaseUrl(), '/');
            }

            if (null === $request) {
                $request = $front->getRequest();
            }
            if (null === $router) {
                $router = $front->getRouter();
            }
         }

        $options = self::rerouteUrl($request, $options);
        $url     = $router->assemble($options, null, $reset, $encode);

        // Remove the base url that was specified
        if ($nobase && (0 === strncmp($url, $base . '/', strlen($base) + 1))) {
            return substr($url, strlen($base));
        }

        return $url;
    }
}
