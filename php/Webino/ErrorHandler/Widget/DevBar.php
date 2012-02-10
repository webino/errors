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
 * @subpackage ErrorHandlerWidget
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Zend_Controller_Request_Http as HttpRequest;

/**
 * QueryPath
 */
require_once 'QueryPath/QueryPath.php';

/**
 * QueryPath templating engine
 */
require_once 'QueryPath/Extension/QPTPL.php';

/**
 * Developer bar widget for ErrorHandler
 *
 * example of options:
 *
 * - script  = PEAR_PHP_DIR "/Webino/layouts/error-handler/widgets/dev-bar.html"
 * - appTimeConstant = "APPLICATION_TIME"
 *
 * @category   Webino
 * @package    Errors
 * @subpackage ErrorHandlerWidget
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_ErrorHandler_Widget_DevBar
    extends    Webino_ErrorHandler_Widget_Abstract
    implements Webino_ErrorHandler_Widget_DevBar_Interface
{
    /**
     * Name of script option
     */
    const SCRIPT_KEYNAME = 'script';

    /**
     * Name of application time constant option
     */
    const APPTIMECONST_KEYNAME = 'appTimeConstant';

    /**
     * Name of body tag to get widget code
     */
    const BODY_TAGNAME = 'body';

    /**
     * End tag of error page body element
     */
    const BODY_CLOSETAG = '</body>';

    /**
     * Name of memory peak value option
     */
    const MEMPEAK_KEYNAME = 'memoryPeak';

    /**
     * Unit of memory peak
     */
    const MEMPEAK_UNIT = ' MB';

    /**
     * Divisor to scale memory peak value (1024*1024)
     */
    const MEMPEAK_UNITDIVISOR = 1048576;
        
    /**
     * Name of application stop time value option
     */
    const APPSTOPTIME_KEYNAME = 'applicationStopTime';

    /**
     * Unit of application stop time
     */
    const APPSTOPTIME_UNIT = ' ms';

    /**
     * Multiplier to scale application stop time
     */
    const APPSTOPTIME_UNITMULTIPLIER = 1000;

    /**
     * Pattern for module : controller : action info
     */
    const ROUTERINFO_PATTERN = '%s : %s : %s';

    /**
     * Path to widget template
     *
     * @var string
     */
    private $_script;

    /**
     * Controller Request to get module : controller : action info
     *
     * @var HttpRequest
     */
    private $_request;

    /**
     * Application initialization time
     *
     * @var float
     */
    private $_applicationTime;

    /**
     * Setup options
     *
     * @param array $options
     *
     * @return bool Widget is not rendered
     */
    public function preprocess(array $options)
    {
        $this->_script          = $options[self::SCRIPT_KEYNAME];
        $this->_applicationTime = constant(
            $options[self::APPTIMECONST_KEYNAME]
        );

        return false;
    }

    /**
     * Widget is rendered into an error page HTML code
     *
     * @param string $html
     *
     * @return string
     */
    public function postprocess($html)
    {
        if (!$this->_request) {

            return $html;
        }

        libxml_use_internal_errors(true);

        return str_replace(
            self::BODY_CLOSETAG,
            qp()->tpl(
                $this->_script, $this
            )->find(self::BODY_TAGNAME)->innerHTML() . self::BODY_CLOSETAG,
            $html
        );
    }

    /**
     * Return memory peak info eg. 6.5 MB
     *
     * @return string
     */
    public function getMemoryPeak()
    {
        if (!isset($this->_options[self::MEMPEAK_KEYNAME])) {
            $this->_options[self::MEMPEAK_KEYNAME]
                = memory_get_peak_usage(true);
        }

        return round(
            $this->_options[self::MEMPEAK_KEYNAME] / self::MEMPEAK_UNITDIVISOR,
            2
        ) . self::MEMPEAK_UNIT;
    }

    /**
     * Return execution time info eg. 75.26 ms
     *
     * @return string
     */
    public function getExecutionTime()
    {
        if (!isset($this->_options[self::APPSTOPTIME_KEYNAME])) {
            $this->_options[self::APPSTOPTIME_KEYNAME] = microtime(true);
        }

        return round(
            ($this->_options[
                self::APPSTOPTIME_KEYNAME
            ] - $this->_applicationTime) * self::APPSTOPTIME_UNITMULTIPLIER,
            2
        ) . self::APPSTOPTIME_UNIT;
    }

    /**
     * Return router info by pattern module : controller : action
     *
     * @return string
     */
    public function getModuleControllerAction()
    {
        return sprintf(
            self::ROUTERINFO_PATTERN, $this->_request->getModuleName(),
            $this->_request->getControllerName(),
            $this->_request->getActionName()
        );
    }

    /**
     * Inject HTTP request
     *
     * @param HttpRequest $request
     * 
     * @return Webino_ErrorHandler_Widget_DevBar 
     */
    public function setRequest(HttpRequest $request = null)
    {
        $this->_request = $request;
        
        return $this;
    }
}
