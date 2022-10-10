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

use Zalt\Ra\Ra;
use Zalt\Snippets\SnippetInterface;
use Zalt\SnippetsLoader\SnippetLoader;
use Zalt\SnippetsLoader\SnippetLoaderInterface;
use Zalt\SnippetsLoader\SnippetLoaderMissingException;

/**
 * Collections of static function for using the Html subpackage.
 *
 * @package    Zalt
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Html
{
    /**
     *
     * @var Creator
     */
    private static $_creator;

    /**
     *
     * @var Renderer
     */
    private static $_renderer;

    /**
     *
     * @var \Zalt\SnippetsLoader\SnippetLoader
     */
    private static $_snippetLoader;

    /**
     * Static variable for debuggging purposes. Toggles the echoing of e.g. of sql
     * select statements, using \Zalt\EchoOut\EchoOut.
     *
     * Implemention classes can use this variable to determine whether to display
     * extra debugging information or not. Please be considerate in what you display:
     * be as succint as possible.
     *
     * Use:
     *     \Zalt\Html\Html::$verbose = true;
     * to enable.
     *
     * @var boolean $verbose If true echo retrieval statements.
     */
    public static $verbose = false;

    public static function attrib($attributeName, ...$args)
    {
        return self::getCreator()->createAttribute($attributeName, $args);
    }

    /**
     * A br element
     *
     * @return HtmlElement
     */
    public static function br()
    {
        return new HtmlElement('br');
    }

    /**
     * Check if the value can be rendered by the default renderer
     *
     * @param mixed $value
     * @return boolean True when the object can be rendered
     */
    public static function canRender($value)
    {
        return self::getRenderer()->canRender($value);
    }

    /**
     * Create an element or return an element creator
     *
     * @param string $tagName Optional tag to create
     * @param mixed $args Optional Ra::args processed settings
     * @return \Zalt\Html\HtmlElement or Creator
     */
    public static function create($tagName = null, ...$args)
    {
        if (null == $tagName) {
            return self::getCreator();
        }
        return self::getCreator()->create($tagName, $args);
    }

    public static function createAttribute($attributeName, array $args = [])
    {
        return self::getCreator()->createAttribute($attributeName, $args);
    }

    /**
     * Creates a new HtmlElement with the arguments specfied in a single array.
     *
     * @param string $tagName (or a Late object)
     * @param array $args
     * @return ElementInterface
     */
    public static function createArray($tagName, array $args = [])
    {
        return self::getCreator()->create($tagName, $args);
    }

    /**
     * Create an element bypassing the standard element creation function stored for certain tags.
     *
     * @param string $tagName Optional tag to create
     * @param mixed $args Optional Ra::args processed settings
     * @return HtmlElement Always, never another type
     */
    public static function createRaw($tagName, array $args = [])
    {
        return self::getCreator()->createRaw($tagName, $args);
    }

    /**
     * Creates a div element
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return HtmlElement (with div tagName)
     */
    public static function div(...$args)
    {
        return self::getCreator()->create('div', $args);
    }

    /**
     * @param \Zend_Form_Element $element
     * @return string
     * @deprecated 
     */
    public static function element2id(\Zend_Form_Element $element)
    {
        return self::name2id($element->getName(), $element->getBelongsTo());
    }

    public static function escape(string $content): string
    {
        // The fixed parameters can be made variable if required for a future version
        return htmlspecialchars($content, ENT_HTML5, 'UTF-8');
    }    
    
    /**
     * Helper function to access the core creator.
     *
     * @return Creator
     */
    public static function getCreator()
    {
        if (! self::$_creator) {
            self::$_creator = new Creator();
        }

        return self::$_creator;
    }

    /**
     * Returns the class used to perform the actual rendering
     * of objects and items into html.
     *
     * @return Renderer
     */
    public static function getRenderer()
    {
        if (! self::$_renderer) {
            self::$_renderer = new Renderer();
        }

        return self::$_renderer;
    }

    /**
     * Get the snippet loader for use by self::snippet().
     *
     * @return SnippetLoader
     */
    public static function getSnippetLoader()
    {
        if (! isset(self::$_snippetLoader)) {
            throw new SnippetLoaderMissingException("Html::getSnippetLoader called before the snippet loader was set.");
        }
        return self::$_snippetLoader;
    }

    /**
     * Is there a snippet loader?
     *
     * @return bool
     */
    public static function hasSnippetLoader(): bool
    {
        return isset(self::$_snippetLoader);
    }

    /**
     * Replaces the non html name characters in the name.
     *
     * Helper function for working with \Zend_Form_Element's
     *
     * @param string $name
     * @param string $belongsTo
     * @return string
     * @deprecated 
     */
    public static function name2id($name, $belongsTo = null)
    {
        return preg_replace('/\[([^\]]+)\]/', '-$1', $name . '-' . $belongsTo);
    }

    /**
     * String content that should be rendered without output escaping
     *
     * @param string $content
     * @return \Zalt\Html\Raw
     */
    public static function raw($content)
    {
        return self::getCreator()->create('raw', array($content));
    }

    /**
     * Mimics strip_tags but allows to strip the content of certain tags (like script) too
     *
     * function copied from a comment http://www.php.net/manual/en/function.strip-tags.php#97386
     *
     * @param string $s             The string to strip
     * @param string $keepTags      Pipe | separated tags to keep
     * @param string $removeContent Pipe | separated tags from which contect will be stripped
     * @return string
     */
    public static function removeMarkup($s, $keepTags = '' , $removeContent = 'script|style|noframes|select|option|link')
    {
        /**///prep the string
        $s = ' ' . $s;

        /**///initialize keep tag logic
        if(strlen($keepTags) > 0){
            $k = explode('|', $keepTags);
            for($i=0;$i<count($k);$i++){
                $s = str_replace('<' . $k[$i] . ' ', '[{(' . $k[$i] . ' ', $s); // Tag name followed by space
                $s = str_replace('<' . $k[$i] . '>', '[{(' . $k[$i] . '>', $s); // Tag name followed by clossing bracket
                $s = str_replace('<' . $k[$i] . '/>', '[{(' . $k[$i] . '/>', $s); // Stand alone tag
                $s = str_replace('</' . $k[$i],'[{(/' . $k[$i],$s);
            }
        }

        //begin removal
        /**///remove comment blocks
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }

        /**///remove tags with content between them
        if(strlen($removeContent) > 0){
            $e = explode('|', $removeContent);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }

        /**///remove remaining tags
        $start = 0;
        while(stripos($s,'<', $start) > 0){
            $pos[1] = stripos($s,'<', $start);
            $pos[2] = stripos($s,'>', $pos[1]);
            if (!$pos[2]) {
                //No closing tag! Skip this one
                $start = $pos[1]+1;
            } else {
                $len[1] = $pos[2] - $pos[1] + 1;
                $x = substr($s,$pos[1],$len[1]);
                $s = str_replace($x,'',$s);
            }
        }

        if (strlen($keepTags) > 0) {
            /**///finalize keep tag
            for($i=0;$i<count($k);$i++){
                $s = str_replace('[{(' . $k[$i],'<' . $k[$i],$s);
                $s = str_replace('[{(/' . $k[$i],'</' . $k[$i],$s);
            }
        }

        return trim($s);
    }

    /**
     * Renders the $content so that it can be used as output.
     *
     * @param mixed $content Anything number, string, array, Late, HtmlInterface, object with __toString
     * @return string Output to echo to the user
     */
    public static function renderAny($content)
    {
        return self::getRenderer()->renderAny($content);
    }

    public static function renderNew($tagName, ...$args)
    {
        $element = self::getCreator()->create($tagName, $args);

        return $element->render();
    }

    /**
     * Creates a table element
     *
     * @param mixed $args Optional Ra::args processed settings
     * @return TableElement
     */
    public static function table(...$args)
    {
        return self::getCreator()->create('table', $args);
    }
    
    public static function testReset()
    {
        self::$_creator = null;
        self::$_renderer = null;
        self::$_snippetLoader = null;
        
        ImgElement::setImageDirs(['/', '/images/']);
    }

    public static function setCreator(\Zalt\Html\Creator $creator)
    {
        self::$_creator = $creator;
        return self::$_creator;
    }

    public static function setRenderer(\Zalt\Html\Renderer $renderer)
    {
        self::$_renderer = $renderer;
        return self::$_renderer;
    }

    /**
     * Set the snippet loader for use by self::snippet().
     *
     * @param \Zalt\SnippetsLoader\SnippetLoaderInterface $snippetLoader
     * @return \Zalt\SnippetsLoader\SnippetLoader
     */
    public static function setSnippetLoader(SnippetLoaderInterface $snippetLoader): SnippetLoader
    {
        self::$_snippetLoader = $snippetLoader;
        return self::$_snippetLoader;
    }

    /**
     *
     * @param string $name Snippet name
     * @param Ra::pairs $parameter_value_pairs Optional extra snippets
     * @return \Zalt\Snippets\SnippetInterface
     */
    public static function snippet($name, ...$args): ?SnippetInterface
    {
        $extraSourceParameters = Ra::pairs($args);

        if (is_array($name)) {
            list($names, $params) = Ra::keySplit($name);

            if ($params) {
                $extraSourceParameters = $params + $extraSourceParameters;
            }
            if (isset($names[0])) {
                $name = $names[0];
            } else {
                throw new HtmlException('Missing snippet name in call to create snippet.');
            }
        }

        $loader = self::getSnippetLoader();

        $snippet = $loader->getSnippet($name, $extraSourceParameters);

        if ($snippet->hasHtmlOutput()) {
            return $snippet;
        }
        return null;
    }

    /**
     * Returns a href attribute
     *
     * @deprecated
     * @param mixed $args Optional Ra::args processed settings
     * @return \Zalt\Html\HrefArrayAttribute
     */
    public static function url(...$args)
    {
        return new HrefArrayAttribute($args);
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
     * @deprecated 
     */
    public static function urlString(array $options, \Zend_Controller_Request_Abstract $request = null, \Zend_Controller_Router_Route $router = null)
    {
        return UrlArrayAttribute::toUrlString($options, $request, $router);
    }
}
