<?php
/**
 * Webino
 *
 * PHP version 5.3
 *
 * LICENSE: This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt. It is also available through the
 * world-wide-web at this URL: http://www.webino.org/license/
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@webino.org
 * so we can send you a copy immediately.
 *
 * @category   Webino
 * @package    Errors
 * @subpackage ErrorHandler
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Webino_ErrorHandler_Interface as HandlerInterface;

/**
 * QueryPath
 */
require_once 'QueryPath/QueryPath.php';

/**
 * QueryPath templating engine
 */
require_once 'QueryPath/Extension/QPTPL.php';

/**
 * ErrorHandler renderer, powered by QueryPath.
 *
 * example of options:
 *
 * - doctype          = '&lt;!DOCTYPE html&gt;'
 * - layout           = layouts/layout.html
 * - scripts.test     = layouts/scripts/test/test.html
 * - favicon.type     = image/png
 * - favicon.href     = favicon.png
 * - stylesheets.test = skin/test/style.css
 * - javascripts.test = js/test.js
 *
 * @category   Webino
 * @package    Errors
 * @subpackage ErrorHandler
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_ErrorHandler_Renderer
    implements Webino_ErrorHandler_Renderer_Interface
{
    /**
     * Name of doctype option
     */
    const DOCTYPE_KEYNAME = 'doctype';

    /**
     * Name of layout option
     */
    const LAYOUT_KEYNAME = 'layout';

    /**
     * Name of scripts option
     */
    const SCRIPTS_KEYNAME = 'scripts';

    /**
     * Name of favicon option
     */
    const FAVICON_KEYNAME = 'favicon';

    /**
     * Name of favicon type option
     */
    const FAVICONTYPE_KEYNAME = 'type';

    /**
     * Name of favicon name option
     */
    const FAVICONHREF_KEYNAME = 'href';

    /**
     * XHTML of favicon element
     */
    const FAVICON_ELEMENTXHTML = '<link rel="icon" type="%s" href="%s"/>';

    /**
     * Name of stylesheets option
     */
    const STYLES_KEYNAME = 'stylesheets';

    /**
     * XHTML of stylesheet element
     */
    const STYLES_ELEMENTXHTML = '<link rel="stylesheet" href="%s"/>';

    /**
     * Name of javascripts option
     */
    const JSCRIPTS_KEYNAME = 'javascripts';

    /**
     * XHTML of javascript element
     */
    const JSCRIPTS_ELEMENTXHTML = '<script src="%s"></script>';

    /**
     * Name of title node
     */
    const TITLE_NODENAME = 'title';

    /**
     * Selector to find head node
     */
    const HEADNODE_SELECTOR = ':root head';

    /**
     * Selector to find body node
     */
    const BODYNODE_SELECTOR = 'body';

    /**
     * Name of widget script option
     */
    const WIDGETSCRIPT_KEYNAME = 'script';

    /**
     * Name of widget object option
     */
    const WIDGETOBJECT_KEYNAME = 'object';

    /**
     * Constructor with no arguments
     */
    public function __construct()
    {

    }
    
    /**
     * Returns ErrorHandler widgets HTML code
     *
     * example of options:
     * 
     * - object = Webino_ErrorHandler_Widget_Test
     * - script = layouts/scripts/test/test.html
     *
     * @param array $widgets
     *
     * @return string
     */
    public function renderWidgets(array $widgets)
    {
        $code = '';
        foreach ($widgets as $widget) {
            
            if (!isset($widget[self::WIDGETSCRIPT_KEYNAME])) {
                
                continue;
            }

            libxml_use_internal_errors(true);

            $code.= qp()->tpl(
                $widget[self::WIDGETSCRIPT_KEYNAME],
                $widget[self::WIDGETOBJECT_KEYNAME]
            )->find(self::BODYNODE_SELECTOR)->innerHTML();
        }
        
        return $code;
    }

    /**
     * Returns ErrorHandler HTML page
     *
     * @param HandlerInterface $errorHandler
     * @param array            $options
     *
     * @return string Renderer error page with doctype
     */
    public function render(HandlerInterface $errorHandler, array $options)    
    {
        $data = array();

        // inserting scripts
        foreach (
            $options[self::SCRIPTS_KEYNAME] as $scriptIndex=>$script
        ) {
            if (is_file($script)) {
                $script = file_get_contents($script);
            }
            $data[$scriptIndex][] = $script;
        }

        // rendering
        try{
            libxml_use_internal_errors(true);

            $queryPath = qp()->tpl(
                qp()->tpl($options[self::LAYOUT_KEYNAME], $data), $errorHandler
            );
        } catch(Exception $exception) {
            error_log($exception);
            
            return null;
        }

        // favicon
        $head = $queryPath->find(self::HEADNODE_SELECTOR);
        $head->append(
            sprintf(
                self::FAVICON_ELEMENTXHTML,
                $options[self::FAVICON_KEYNAME][self::FAVICONTYPE_KEYNAME],
                $options[self::FAVICON_KEYNAME][self::FAVICONHREF_KEYNAME]
            )
        );

        // stylesheets
        foreach ($options[self::STYLES_KEYNAME] as $link) {
            $head->append(sprintf(self::STYLES_ELEMENTXHTML, $link));
        }

        // javascripts
        foreach ($options[self::JSCRIPTS_KEYNAME] as $src) {
            $head->append(sprintf(self::JSCRIPTS_ELEMENTXHTML, $src));
        }

        // title
        $head->find(self::TITLE_NODENAME)->text($errorHandler->getTitle());

        return $options[self::DOCTYPE_KEYNAME] . PHP_EOL
            . $queryPath->document()->saveHTML();
    }
}
