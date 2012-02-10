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
 * @subpackage ControllerPlugin
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Webino_Resource_Events_Interface as Events;

/**
 * QueryPath
 */
require_once 'QueryPath/QueryPath.php';

/**
 * QueryPath templating engine
 */
require_once 'QueryPath/Extension/QPTPL.php';

/**
 * Plugin for rendering application error to response
 *
 * example of options:
 *
 * - class                                  = Webino_ControllerPlugin_Errors
 * - stackIndex                             = 999999999999
 * - options.title                          = "Application Error | Webino | Open Source Website System"
 * - options.data.header.0                  = "<h2>Application Error</h2>"
 * - options.scripts.content.0              = PEAR_PHP_DIR "/Webino/layouts/error/application.html"
 * - options.favicon.type                   = "image/vnd.microsoft.icon"
 * - options.favicon.href                   = "http://static.webino.org/favicon.ico"
 * - options.stylesheets.default            = "http://static.webino.org/project/default.css"
 * - options.javascripts.example            = "http://www.example.com/example.css"
 * - options.layout                         = PEAR_PHP_DIR "/Webino/layouts/bigboard.html"
 * - inject.bootstrap.pluginResource.events = events
 *
 * @category   Webino
 * @package    Errors
 * @subpackage ControllerPlugin
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_ControllerPlugin_Errors
    extends Zend_Controller_Plugin_Abstract
{
    /**
     * Name of application error event option
     */
    const APPERROR_EVENT = 'applicationError';

    /**
     * Name of doctype option
     */
    const DOCTYPE_KEYNAME = 'doctype';

    /**
     * Name of title option
     */
    const TITLE_KEYNAME = 'title';

    /**
     * Name of layout option
     */
    const LAYOUT_KEYNAME = 'layout';

    /**
     * Name of scripts option
     */
    const SCRIPTS_KEYNAME = 'scripts';

    /**
     * Name of data option
     */
    const DATA_KEYNAME = 'data';
    
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
     * Options
     *
     * @var array
     */
    private $_options;

    /**
     * Events resource
     *
     * @var Events
     */
    private $_events;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;
    }

    /**
     * Inject events resource
     *
     * @param Events $events
     *
     * @return Webino_ControllerPlugin_Errors
     */
    public function setEvents(Events $events)
    {
        $this->_events = $events;

        return $this;
    }

    /**
     * Render application error response
     *
     * If internal error occours error_log() is used.
     *
     * @return mixed Returns false if disabled, null if error occours
     */
    public function dispatchLoopShutdown()
    {
        parent::dispatchLoopShutdown();

        if ($this->getResponse()->isException()) {
            $exception = $this->getResponse()->getException();

            $this->_events->fire(
                self::APPERROR_EVENT, array($exception)
            );

            if (404 == $this->getResponse()->getHttpResponseCode()
                && 1 == count($exception)
            ) {
                return false;
            }

        } else {
            
            return false;
        }

        // rendering
        try{
            libxml_use_internal_errors(true);

            $queryPath = qp()->tpl(
                qp()->tpl(
                    $this->_options[self::LAYOUT_KEYNAME], $this->_scripts()
                )->document()->saveHTML(),
                $this->_data()
            );

        } catch(Exception $exception) {
            error_log($exception);

            return null;
        }

        $head = $queryPath->find(self::HEADNODE_SELECTOR);

        $this->_favicon($head)->_stylesheets($head)->_javascripts($head);

        $this->_title($head);

        $this->getResponse()->setHttpResponseCode(500)
            ->setBody(
                $this->_options[self::DOCTYPE_KEYNAME] . PHP_EOL
                . $queryPath->document()->saveHTML()
            );

        return true;
    }

    /**
     * Returns array with scripts contents
     *
     * If data isn't set returns self
     *
     * @return Webino_ControllerPlugin_Errors|array
     */
    private function _scripts()
    {
        $data = array();

        if (empty($this->_options[self::SCRIPTS_KEYNAME])) {

            return $data;
        }
        
        foreach (
            $this->_options[self::SCRIPTS_KEYNAME] as $key => $scripts
        ) {
            foreach ($scripts as $script) {
                if (is_file($script)) {
                    $script = file_get_contents($script);
                }
                $data[$key][] = $script;
            }
        }

        return $data;
    }

    /**
     * Return options data
     *
     * If data isn't set returns self
     *
     * @return Webino_ControllerPlugin_Errors |array
     */
    private function _data()
    {
        if (empty($this->_options[self::DATA_KEYNAME])) {

            return $this;
        }

        return $this->_options[self::DATA_KEYNAME];
    }

    /**
     * Append favicon to layout
     *
     * @param QueryPath $head
     * 
     * @return Webino_ControllerPlugin_Errors
     */
    private function _favicon(QueryPath $head)
    {
        if (empty($this->_options[self::FAVICON_KEYNAME])) {

            return $this;
        }

        $options = &$this->_options[self::FAVICON_KEYNAME];

        $head->append(
            sprintf(
                self::FAVICON_ELEMENTXHTML,
                $options[self::FAVICONTYPE_KEYNAME],
                $options[self::FAVICONHREF_KEYNAME]
            )
        );

        return $this;
    }

    /**
     * Append stylesheets to layout
     *
     * @param QueryPath $head
     *
     * @return Webino_ControllerPlugin_Errors
     */
    private function _stylesheets(QueryPath $head)
    {
        if (empty($this->_options[self::STYLES_KEYNAME])) {

            return $this;
        }

        foreach ($this->_options[self::STYLES_KEYNAME] as $link) {

            if (empty($link)) {
                continue;
            }

            $head->append(sprintf(self::STYLES_ELEMENTXHTML, $link));
        }

        return $this;
    }

    /**
     * Append javascripts to layout
     *
     * @param QueryPath $head
     *
     * @return Webino_ControllerPlugin_Errors
     */
    private function _javascripts(QueryPath $head)
    {
        if (empty($this->_options[self::JSCRIPTS_KEYNAME])) {

            return $this;
        }

        foreach ($this->_options[self::JSCRIPTS_KEYNAME] as $link) {

            if (empty($link)) {
                continue;
            }

            $head->append(sprintf(self::JSCRIPTS_ELEMENTXHTML, $link));
        }

        return $this;
    }

    /**
     * Change layout title
     *
     * @param QueryPath $head
     *
     * @return Webino_ControllerPlugin_Errors
     */
    private function _title(QueryPath $head)
    {
        if (empty($this->_options[self::TITLE_KEYNAME])) {

            return $this;
        }

        $head->find(self::TITLE_NODENAME)->text(
            $this->_options[self::TITLE_KEYNAME]
        );

        return $this;
    }
}
