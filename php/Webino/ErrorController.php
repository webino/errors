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
 * @subpackage Controller
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Webino_Resource_Events_Interface    as Events;
use Zend_Controller_Plugin_ErrorHandler as ErrorHandlerPlugin;

/**
 * Webino error controller, for public error page, it fires events.
 *
 * @category   Webino
 * @package    Errors
 * @subpackage Controller
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_ErrorController
    extends Zend_Controller_Action
{
    /**
     * Name of action error event
     */
    const ACTIONERROR_EVENT = 'actionError';

    /**
     * Name of page not found event
     */
    const PAGENOTFOUND_EVENT = 'pageNotFound';

    /**
     * Events resource
     *
     * @var Events
     */
    private $_events;

    /**
     * Assign error details to view, log message.
     */
    public function errorAction()
    {
        $errors = $this->_getParam(
            'error_handler', (object) array(
                'exception' => null,
                'type'      => ErrorHandlerPlugin::EXCEPTION_NO_ACTION)
        );

        $this->_events->fire(
            self::ACTIONERROR_EVENT, array($errors)
        );

        switch ($errors->type) {
            case ErrorHandlerPlugin::EXCEPTION_NO_ROUTE:
            case ErrorHandlerPlugin::EXCEPTION_NO_CONTROLLER:
            case ErrorHandlerPlugin::EXCEPTION_NO_ACTION:
                $this->_response->setHttpResponseCode(404);
                $this->_events->fire(
                    self::PAGENOTFOUND_EVENT, array($errors)
                );
                break;
        }
    }

    /**
     * Inject events resource
     *
     * @param Events $events
     *
     * @return Webino_ErrorController
     */
    public function setEvents(Events $events)
    {
        $this->_events = $events;

        return $this;
    }
}
