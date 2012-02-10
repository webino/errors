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
 * @subpackage EventHandler
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Zend_Log                       as Log;
use Zend_Controller_Front          as FrontController;
use Webino_Resource_Draw_Interface as DrawResource;

/**
 * Errors event handler
 *
 * example of options:
 *
 * - draw.404.common     = PEAR_PHP_DIR "/Webino/configs/draw/errors.ini"
 * - draw.404.errors     = PEAR_PHP_DIR "/Webino/configs/draw/404.ini"
 * - draw.default.common = PEAR_PHP_DIR "/Webino/configs/draw/errors.ini"
 *
 * @category   Webino
 * @package    Errors
 * @subpackage EventHandler
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_EventHandler_Errors
{
    /**
     * Name of error controller plugin option
     */
    const ERRPLUGIN_KEYNAME = 'errorPlugin';

    /**
     * Name of draw map option
     */
    const DRAW_KEYNAME = 'draw';

    /**
     * Name of draw 404 map option
     */
    const DRAW_404_KEYNAME = '404';

    /**
     * Name of application stop time option key
     */
    const APPSTOPTIME_KEYNAME = 'applicationStopTime';

    /**
     * Name of memory peak option key
     */
    const MEMPEAK_KEYNAME = 'memoryPeak';

    /**
     * Options array
     *
     * @var array
     */
    private $_options;

    /**
     * Log resource
     *
     * @var Log
     */
    private $_log;

    /**
     * Zend front controller
     *
     * @var FrontController
     */
    private $_frontController;

    /**
     * Draw resource
     *
     * @var DrawResource
     */
    private $_drawResource;

    /**
     * Inject errors event handler options
     *
     * @param array $options
     *
     * @return Webino_EventHandler_Errors
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Inject log
     *
     * @param Log $log
     *
     * @return Webino_EventHandler_Errors
     */
    public function setLog(Log $log)
    {
        $this->_log = $log;

        return $this;
    }

    /**
     * Inject front controller
     * 
     * @param FrontController $frontController
     *
     * @return Webino_EventHandler_Errors
     */
    public function setFrontController(FrontController $frontController)
    {
        $this->_frontController = $frontController;

        return $this;
    }

    /**
     * Inject draw resource
     *
     * @param DrawResource $resource
     *
     * @return Webino_EventHandler_Errors
     */
    public function setDrawResource(DrawResource $resource)
    {
        $this->_drawResource = $resource;

        return $this;
    }

    /**
     * Setup debug stop time and memory peak
     *
     * @param int    $code
     * @param string $msg
     * @param string $file
     * @param int    $line
     * @param array  $context
     * @param array  $options
     * 
     * @return Webino_EventHandler_Errors
     */
    public function initDebug($code, $msg, $file, $line, $context, &$options)
    {
        $options[self::APPSTOPTIME_KEYNAME] = microtime(true);
        $options[self::MEMPEAK_KEYNAME]     = memory_get_peak_usage(true);

        return $this;
    }

    /**
     * Set public application error html
     *
     * @param string $html
     *
     * @throws UnexpectedValueException If plugin class isn't set
     *
     * @return Webino_EventHandler_Errors
     */
    public function publicShutDown(&$html)
    {
        if (!isset($this->_options[self::ERRPLUGIN_KEYNAME])) {
            throw new UnexpectedValueException(
                sprintf(
                    'Option "%s" is missing for "%s"',
                    self::ERRPLUGIN_KEYNAME, __CLASS__
                )
            );
        }

        $plugin = $this->_frontController->getPlugin(
            $this->_options[self::ERRPLUGIN_KEYNAME]
        );

        $response = $this->_frontController->getResponse();

        $response->setException(
            new RuntimeException
        );

        $plugin->dispatchLoopShutdown();

        $response->sendHeaders();

        $html = $response->getBody();

        return $this;
    }

    /**
     * Log error message
     * 
     * @param int    $code    Error code
     * @param string $msg     Error message
     * @param string $file    Error file path
     * @param int    $line    Error line
     * @param array  $context Error context
     * @param array  $options
     *
     * @return Webino_EventHandler_Errors
     */
    public function logError($code, $msg, $file, $line, $context, $options)
    {
        $this->_log->errorHandler($code, $msg, $file, $line, $context);

        return $this;
    }
    
    /**
     * Handle page not found error event
     *
     * @param object $errors
     *
     * @throws UnexpectedValueException If options was not set
     *
     * @return Webino_EventHandler_Errors
     */
    public function pageNotFound($errors)
    {
        if (!isset(
            $this->_options[self::DRAW_KEYNAME][self::DRAW_404_KEYNAME]
        )) {
            throw new UnexpectedValueException(
                sprintf(
                    'Option "%s.%s" is missing for "%s"',
                    self::DRAW_KEYNAME, self::DRAW_404_KEYNAME, __CLASS__
                )
            );
        }

        $this->_drawResource->addMaps(
            $this->_options[self::DRAW_KEYNAME][self::DRAW_404_KEYNAME]
        );

        return $this;
    }
    
    /**
     * Handle public application error event
     *
     * @param array $exceptions
     *
     * @return Webino_EventHandler_Errors
     */
    public function applicationError(array $exceptions)
    {
        foreach ($exceptions as $exception) {
            $this->_log->crit('Application error', $exception->getMessage());
        }
        
        return $this;
    }
}
