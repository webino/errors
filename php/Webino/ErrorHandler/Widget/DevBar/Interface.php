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
 * Interface for ErrorHandler developer bar widget
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
interface Webino_ErrorHandler_Widget_DevBar_Interface
{
    /**
     * Return memory peak info eg. 6.5 MB
     *
     * @return string
     */
    public function getMemoryPeak();

    /**
     * Return execution time info eg. 75.26 ms
     *
     * @return string
     */
    public function getExecutionTime();

    /**
     * Return router info by pattern module : controller : action
     *
     * @return string
     */
    public function getModuleControllerAction();

    /**
     * Inject Request
     *
     * @param HttpRequest $request
     *
     * @return Webino_ErrorHandler_Widget_DevBar_Interface
     */
    public function setRequest(HttpRequest $request);
}
