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

use Zend_Log                                      as Log;
use Zend_Controller_Front                         as FrontController;
use Webino_ErrorHandler_Exception                 as HandlerException;
use Webino_ErrorHandler_Renderer_Interface        as Renderer;
use Webino_Resource_Dependency_Injector_Interface as Dependency;
use Webino_Resource_Dependency_Interface          as DependencyResource;
use Webino_Resource_Events_Interface              as Events;

/**
 * ErrorHandler class for Webino errors resource
 *
 * example of options:
 *
 * - disable       = 0
 * - writeFunction = Webino_ErrorHandler_Renderer_Writer
 * - terminator    = exit
 * - errorTitle.1  = Fatal error
 * - urlConstant   = 'URL'
 * - errorFileCode.callback = Webino_Text_Highlight::php
 * - errorFileCode.from     = 6
 * - errorFileCode.to       = 6
 * - exceptionTraceIndex.Exception = 0
 * - widgets.testWidget.object       = Webino_ErrorHandler_Widget_TestWidget
 * - widgets.testWidget.script       = error-handler/widgets/testWidget.html
 * - widgets.testWidget.options.test = testOptionValue
 *
 * Other options depends on renderer.                                     
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
class Webino_ErrorHandler
    implements Webino_ErrorHandler_Interface
{
    /**
     * Name of disable option
     */
    const DISABLE_KEYNAME = 'disable';

    /**
     * Name of start up event option key
     */
    const STARTUP_KEYNAME = 'errorStartUp';

    /**
     * Name of shut down event option key
     */
    const SHUTDOWN_KEYNAME = 'errorShutDown';

    /**
     * Name of view renderer helper
     */
    const VIEWRENDERER_HELPERNAME = 'ViewRenderer';

    /**
     * Name of Log resource
     */
    const LOG_RESOURCENAME = 'log';

    /**
     * Date format
     */
    const DATE_FORMAT = 'Y/m/d H:i:s';

    /**
     * Default class for error rendering
     */
    const DEFAULT_RENDERER_CLASS = 'Webino_ErrorHandler_Renderer';

    /**
     * Interface to implement by error handler widget
     */
    const WIDGET_INTERFACE = 'Webino_ErrorHandler_Widget_Interface';
    
    /**
     * Name of renderer option
     */
    const RENDERER_KEYNAME = 'renderer';

    /**
     * Name of write function option
     */
    const WRITEFC_KEYNAME = 'writeFunction';

    /**
     * Name of terminator option
     */
    const TERMINATOR_KEYNAME = 'terminator';

    /**
     * Name of widget object option
     */
    const WIDGETOBJECT_KEYNAME = 'object';

    /**
     * Name of options option key
     */
    const OPTIONS_KEYNAME = 'options';

    /**
     * Name of inject option key
     */
    const INJECT_KEYNAME = 'inject';

    /**
     * Name of error title option
     */
    const ERRORTITLE_KEYNAME = 'errorTitle';

    /**
     * Name of URL constant option
     */
    const URLCONSTANT_KEYNAME = 'urlConstant';

    /**
     * Name of error file code option
     */
    const ERRORFILECODE_KEYNAME = 'errorFileCode';

    /**
     * Name of error file code callback option
     */
    const ERRORFILECODE_CALLBACK_KEYNAME = 'callback';

    /**
     * Name of error file code from option
     */
    const ERRORFILECODE_FROM_KEYNAME = 'from';

    /**
     * Name of error file code to option
     */
    const ERRORFILECODE_TO_KEYNAME = 'to';

    /**
     * Name of widgets option
     */
    const WIDGETS_KEYNAME = 'widgets';

    /**
     * Name of exception trace index option
     */
    const EXCEPTIONTRACEINDEX_KEYNAME = 'exceptionTraceIndex';

    /**
     * Error page title pattern
     */
    const TITLE_PATTERN = '%s... | %s | Webino | Open Source Website System';

    /**
     * Error code
     *
     * @var int
     */
    protected $_errorCode;

    /**
     * Error message
     *
     * @var string
     */
    protected $_errorMsg;

    /**
     * Error file path
     *
     * @var string
     */
    protected $_errorFile;

    /**
     * Error line number
     *
     * @var int
     */
    protected $_errorLine;

    /**
     * Error context array
     *
     * @var array
     */
    protected $_errorContext = array();

    /**
     * Options array
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Error exception
     *
     * @var Exception
     */
    protected $_errorException;

    /**
     * Error handler renderer
     *
     * @var Renderer
     */
    private $_renderer;

    /**
     * Array of widgets
     *
     * @var array
     */
    private $_widgets = array();

    /**
     * Log resource
     *
     * @var Log
     */
    private $_log;

    /**
     * Events resource
     *
     * @var Events
     */
    private $_events;

    /**
     * Dependency injector
     *
     * @var Dependency
     */
    private $_dependency;

    /**
     * Dependency resource
     *
     * @var DependencyResource
     */
    private $_dependencyResource;

    /**
     * Constructor
     *
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->_renderer = $renderer;
    }

    /**
     * Sets exception, if trace index option is set roll to relevant exception.
     *
     * example of options:
     *
     * - exceptionTraceIndex.Exception = 0
     *
     * @param Exception $exception
     * @param array     $options
     *
     * @return Webino_ErrorHandler
     */
    private function _traceException(
        Exception $exception = null, array $options = array()
    )
    {
        if (!$exception) {

            return null;
        }

        if ($exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        $this->_errorFile = $exception->getFile();
        $this->_errorLine = $exception->getLine();

        $exceptionClass = get_class($exception);

        $traceIndex = 0;

        if (isset(
            $options[self::EXCEPTIONTRACEINDEX_KEYNAME][$exceptionClass]
        )) {
            $traceIndex = $options[
                self::EXCEPTIONTRACEINDEX_KEYNAME
            ][$exceptionClass];
        }

        if ($traceIndex) {

            $skip = 0;

            foreach ($exception->getTrace() as $item) {

                if (isset($item['file']) ) {

                    if (!$skip) {
                        $skip = 1;

                    } elseif ($traceIndex <= $skip) {

                        $this->_errorFile = $item['file'];
                        $this->_errorLine = $item['line'];
                        unset($skip);
                        break;
                    }
                    $skip++;
                }
            }
        }

        return $exception;
    }

    /**
     * Render developer error page
     *
     * @param string $html
     *
     * @return Webino_ErrorHandler
     */
    private function _render(&$html)
    {
        if (empty($this->_options[self::DISABLE_KEYNAME])) {

            $html = $this->_renderer->render($this, $this->_options);

            if (!empty($this->_widgets)) {
                foreach ($this->_widgets as $widget) {
                    $html = $widget[self::WIDGETOBJECT_KEYNAME]->postprocess(
                        $html
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Write error page to user
     *
     * @param string $html
     *
     * @return Webino_ErrorHandler 
     */
    private function _write($html)
    {
        if (!empty($this->_options[self::WRITEFC_KEYNAME])) {
            call_user_func_array(
                $this->_options[self::WRITEFC_KEYNAME], array($html)
            );
        }

        return $this;
    }

    /**
     * Terminate the application
     *
     * @return Webino_ErrorHandler 
     */
    private function _terminate()
    {
        if (!empty($this->_options[self::TERMINATOR_KEYNAME])) {
            eval($this->_options[self::TERMINATOR_KEYNAME] . ';');
        }

        return $this;
    }

    /**
     * Called when error handled
     *
     * @param int          $code      Error code
     * @param string       $msg       Error message
     * @param string       $file      Error file name
     * @param int          $line      Error line
     * @param array        $context   Error context
     * @param array        $options
     * @param Exception    $exception
     */
    public function writeError(
        $code, $msg, $file, $line, array $context,
        array $options, Exception $exception = null
    )
    {
        $this->_errorCode      = $code;
        $this->_errorMsg       = strip_tags($msg);
        $this->_errorFile      = $file;
        $this->_errorLine      = $line;
        $this->_errorContext   = $context;
        $this->_options        = $options;
        $this->_errorException = $this->_traceException($exception, $options);

        $this->_events->fire(
            self::STARTUP_KEYNAME, array(
                &$this->_errorCode, &$this->_errorMsg,
                &$this->_errorFile, &$this->_errorLine,
                &$this->_errorContext, &$this->_options,
            )
        );

        $html = '';

        $this->_render($html);

        $this->_events->fire(
            self::SHUTDOWN_KEYNAME, array(&$html)
        );

        $this->_write($html)->_terminate();
    }

    /**
     * Dependency injector
     *
     * @param Dependency $dependency
     *
     * @return Webino_ErrorHandler
     */
    public function setDependency(Dependency $dependency)
    {
        $this->_dependency = $dependency;

        return $this;
    }

    /**
     * Dependency resource injector
     *
     * @param DependencyResource $resource
     *
     * @return Webino_ErrorHandler 
     */
    public function setDependencyResource(DependencyResource $resource)
    {
        $this->_dependencyResource = $resource;

        return $this;
    }

    /**
     * Returns HTML code of ErrorHandler widgets
     *
     * example of options:
     *
     * - widgets.example.object = Webino_ErrorHandler_Widget_Example
     *
     * example of options depends on widget:
     *
     * - widgets.example.options.01 = value01
     * - widgets.example.options.02 = value02
     *
     * example of options depends on renderer:
     *
     * - widgets.example.script = APPLICATION_LAYOUTS "/scripts/error-handler/
     *   widget/example.html"
     *
     * @throws HandlerException
     *
     * @return string
     */
    public function getWidgets()
    {
        if (!isset($this->_options[self::WIDGETS_KEYNAME])) {

            return '';
        }

        $this->_widgets = array();

        foreach ($this->_options[self::WIDGETS_KEYNAME] as $widget) {

            if (is_string($widget[self::WIDGETOBJECT_KEYNAME])) {

                if (!@class_exists($widget[self::WIDGETOBJECT_KEYNAME])) {

                    throw new HandlerException(
                        sprintf(
                            'ErrorHandler Widget class %s not found '
                            . 'in include path: %s',
                            $widget[self::WIDGETOBJECT_KEYNAME],
                            get_include_path()
                        )
                    );

                } elseif (!in_array(
                    self::WIDGET_INTERFACE,
                    class_implements($widget[self::WIDGETOBJECT_KEYNAME])
                )) {

                    throw new HandlerException(
                        sprintf(
                            'ErrorHandler Widget %s must implement %s, '
                            . 'but it implements %s.',
                            $widget[self::WIDGETOBJECT_KEYNAME],
                            self::WIDGET_INTERFACE,
                            join(
                                ', ',
                                class_implements(
                                    $widget[self::WIDGETOBJECT_KEYNAME]
                                )
                            )
                        )
                    );
                }

                $widgetClass = $widget[self::WIDGETOBJECT_KEYNAME];

                $widget[self::WIDGETOBJECT_KEYNAME]
                    = new $widgetClass(
                        $this->_errorCode, $this->_errorMsg,
                        $this->_errorFile, $this->_errorLine,
                        $this->_errorContext, $this->_options,
                        $this->_errorException
                    );

                if (isset($widget[self::INJECT_KEYNAME])) {
                    $this->_dependency->inject(
                        $widget[self::WIDGETOBJECT_KEYNAME],
                        $this->_dependencyResource,
                        $widget[self::INJECT_KEYNAME]
                    );
                }
            }

            if (!isset($widget[self::OPTIONS_KEYNAME])) {
                $widget[self::OPTIONS_KEYNAME] = array();
            }

            if ( !$widget[self::WIDGETOBJECT_KEYNAME]
                    ->preprocess($widget[self::OPTIONS_KEYNAME])
            ) {
                $this->_widgets[] = $widget;
            }
        }

        return $this->_renderer->renderWidgets($this->_widgets);
    }

    /**
     * Inject log resource
     *
     * @param Log $log
     *
     * @return Webino_ErrorHandler
     */
    public function setLog(Log $log)
    {
        $this->_log = $log;

        return $this;
    }

    /**
     * Inject events resource
     *
     * @param Events $events
     *
     * @return Webino_ErrorHandler 
     */
    public function setEvents(Events $events)
    {
        $this->_events = $events;

        return $this;
    }

    /**
     * Returns error title string
     *
     * If errorTitle options for particular code is set returns that title.
     *
     * example of options:
     *
     * - errorTitle.1 = Fatal error
     *
     * @return string
     */
    public function getErrorTitle()
    {
        $title = 'Error';
        if (
            isset($this->_options[self::ERRORTITLE_KEYNAME][$this->_errorCode])
        ) {
            
            $title = $this->_options[
                self::ERRORTITLE_KEYNAME
            ][$this->_errorCode];
        }
        
        return htmlspecialchars($title);
    }

    /**
     * Returns error code number
     *
     * @return string
     */
    public function getErrorCode()
    {
        return htmlspecialchars($this->_errorCode);
    }

    /**
     * Returns error message text
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return htmlspecialchars($this->_errorMsg);
    }

    /**
     * Exception class in brackets is returned
     * 
     * If error was caused by exception.
     *
     * @return string
     */
    public function getErrorNote()
    {
        if (!$this->_errorException) {
            
            return '';
        }
        
        return sprintf('(%s)', get_class($this->_errorException));
    }

    /**
     * Returns error file path
     *
     * @return string
     */
    public function getErrorFile()
    {
        return htmlspecialchars($this->_errorFile);
    }

    /**
     * Returns error line number
     *
     * @return string
     */
    public function getErrorLine()
    {
        return htmlspecialchars($this->_errorLine);
    }

    /**
     * Returns generated date time
     *
     * @return string
     */
    public function getErrorGeneratedTime()
    {
        if (!isset($_SERVER['REQUEST_TIME'])) {
            
            return '';
        }
        
        return @date(
            self::DATE_FORMAT, $_SERVER['REQUEST_TIME']
        );
    }

    /**
     * Returns hyperlink to error page
     *
     * example of options:
     * 
     * - htmlLink = URL
     *
     * @return string
     */
    public function getErrorPageUrl()
    {
        $url = constant($this->_options[self::URLCONSTANT_KEYNAME]);

        return sprintf('<a href="%s">%s</a>', $url, $url);
    }

    /**
     * Returns current PHP version
     *
     * @return string
     */
    public function getPHPVersion()
    {
        return htmlspecialchars(PHP_VERSION);
    }

    /**
     * Returns server info text
     *
     * @return string
     */
    public function getServerInfo()
    {
        if (!isset($_SERVER['SERVER_SOFTWARE'])) {
            
            return '';
        }
        
        return htmlspecialchars($_SERVER['SERVER_SOFTWARE']);
    }

    /**
     * Returns current version of Webino
     *
     * @return string
     */
    public function getWebinoVersion()
    {
        return htmlspecialchars(Webino_Version::VERSION);
    }

    /**
     * Used for copyright
     *
     * @return string
     */
    public function getCurrentYear()
    {
        return @date('Y');
    }

    /**
     * Returns title for error page
     *
     * @return string
     */
    public function getTitle()
    {
        return sprintf(
            self::TITLE_PATTERN,
            substr(
                strip_tags($this->_errorMsg), 0, 40
            ), strtoupper(
                $this->getErrorTitle()
            )
        );
    }

    /**
     * Depends on callback returns highlighted error file code fragment
     * 
     * example of options:
     * 
     * - errorFileCode.callback = Webino_Text_Highlight::php
     * - errorFileCode.from     = 6
     * - errorFileCode.to       = 6
     *
     * @return string
     */
    public function getErrorFileCode()
    {
        return str_replace(
            '&nbsp;', '<![CDATA[&nbsp;]]>',
            call_user_func_array(
                $this->_options[self::ERRORFILECODE_KEYNAME]
                [self::ERRORFILECODE_CALLBACK_KEYNAME],
                array(
                    $this->_errorFile,
                    $this->_errorLine,
                    $this->_options[self::ERRORFILECODE_KEYNAME]
                        [self::ERRORFILECODE_FROM_KEYNAME],
                    $this->_options[self::ERRORFILECODE_KEYNAME]
                        [self::ERRORFILECODE_TO_KEYNAME],
                )
            )
        );
    }
}
