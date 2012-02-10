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

use Zend_Application_Bootstrap_Bootstrapper     as Bootstrapper;
use Webino_ErrorHandler_Widget_DevBar_Interface as DevBar;

/**
 * ErrorHandler controller plugin for developer bar widget
 *
 * example of options:
 *
 * - object       = Webino_ErrorHandler_Widget_DevBar
 * - script       = PHP_DIR "/Webino/layouts/error-handler/widgets/dev-bar.html"
 * - appTimeConstant = "APPLICATION_TIME"
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
class Webino_ControllerPlugin_DevBar
    extends    Zend_Controller_Plugin_Abstract
    implements Webino_ControllerPlugin_DevBar_Interface
{
    /**
     * Option name for object class
     */
    const OBJECT_KEYNAME = 'object';

    /**
     * Name of application stop time option for DevBar widget
     */
    const APPSTOPTIME_KEYNAME = 'applicationStopTime';

    /**
     * Name of memory peak option for DevBar widget
     */
    const MEMPEAK_KEYNAME = 'memoryPeak';

    /**
     * An given options
     * 
     * @var array
     */
    private $_options = array();

    /**
     * ErrorHandler developer bar widget
     *
     * @var DevBar
     */
    private $_devBar;

    /**
     * Set options via constructor
     *
     * @param array $options 
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }
    
    /**
     * The default developer bar object
     *
     * @return DevBar
     */
    public function getDevBar()
    {
        if (!$this->_devBar) {       
            $this->setDevBar(
                new $this->_options[self::OBJECT_KEYNAME](
                    null, null, null, null, array(),
                    array(
                        self::APPSTOPTIME_KEYNAME => microtime(true),
                        self::MEMPEAK_KEYNAME  => memory_get_peak_usage(true),
                    ), null
                )
            );
        }
        
        return $this->_devBar;
    }

    /**
     * Inject developer bar widget object
     *
     * @param DevBar $devBar
     *
     * @return Webino_ControllerPlugin_DevBar
     */
    public function setDevBar(DevBar $devBar=null)
    {
        $this->_devBar = $devBar;

        return $this;
    }

    /**
     * Return execution time and memory peak together
     *
     * @return string
     */
    public function getOverallPerformance()
    {
        $devBar = $this->getDevBar();

        return str_replace(' ', '', $devBar->getExecutionTime())
            . ' ' . str_replace(' ', '', $devBar->getMemoryPeak())
            . ' | ' . str_replace(
                ' ', '', $this->getDevBar()->getModuleControllerAction()
            );
    }

    /**
     * Render developer bar widget into response body
     */
    public function dispatchLoopShutdown()
    {
        $this->getDevBar()->setRequest($this->getRequest())
            ->preprocess($this->_options);

        $this->getResponse()->setBody(
            $this->getDevBar()->postprocess(
                $this->getResponse()->getBody()
            )
        );
    }
}
