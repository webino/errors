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
 * @subpackage Resource
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Webino_Resource_Errors_Exception       as ErrorsException;
use Webino_ErrorHandler_Renderer_Interface as RendererInterface;

/**
 * Resource for setup error handling
 *
 * example of options:
 *
 * - rendererClass     = Webino_ErrorHandler_Renderer
 * - handler           = Webino_ErrorHandler
 * - shutdownCallback  = register_shutdown_function
 * - exceptionCallback = set_exception_handler
 * - errorCallback     = set_error_handler
 *
 * Handler must implement specific interface, see class constants.
 *
 * @category   Webino
 * @package    Errors
 * @subpackage Resource
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_Resource_Errors
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Interface to implement by error renderer
     */
    const RENDERER_INTERFACE = 'Webino_ErrorHandler_Renderer_Interface';

    /**
     * Interface to implement by error handler
     */
    const HANDLER_INTERFACE = 'Webino_ErrorHandler_Interface';

    /**
     * Default class for error handling
     */
    const DEFAULT_ERRORHANDLER_CLASS = 'Webino_ErrorHandler';

    /**
     * Name of renderer class option
     */
    const RENDERERCLASS_KEYNAME = 'rendererClass';

    /**
     * Name of handler option
     */
    const HANDLER_KEYNAME = 'handler';

    /**
     * Name of shutdownCallback option
     */
    const SHUTDOWNCALLBACK_KEYNAME = 'shutdownCallback';

    /**
     * Name of exceptionCallback option
     */
    const EXCEPTIONCALLBACK_KEYNAME = 'exceptionCallback';

    /**
     * Name of errorCallback option
     */
    const ERRORCALLBACK_KEYNAME = 'errorCallback';

    /**
     * Name of method to write errors
     */
    const WRITER_METHODNAME = 'writeError';

    /**
     * Last error for shutdown
     *
     * @var array
     */
    private $_lastError = null;

    /**
     * Check options and initialize error handler
     */
    public function init()
    {
        $options = $this->getOptions();

        if (!isset($options[self::HANDLER_KEYNAME])) {
            $options[self::HANDLER_KEYNAME] = self::DEFAULT_ERRORHANDLER_CLASS;
        }

        $handler = $this->_handlerObject($options[self::HANDLER_KEYNAME]);

        if (isset($options[self::SHUTDOWNCALLBACK_KEYNAME])) {

            $this->_registerShutdown(
                $options[self::SHUTDOWNCALLBACK_KEYNAME],
                $handler, $options, $this->_lastError
            );
        }

        if (isset($options[self::EXCEPTIONCALLBACK_KEYNAME])) {

            $this->_registerException(
                $options[self::EXCEPTIONCALLBACK_KEYNAME], $handler, $options
            );
        }

        if (isset($options[self::ERRORCALLBACK_KEYNAME])) {

            $this->_registerError(
                $options[self::ERRORCALLBACK_KEYNAME], $handler, $options
            );
        }

        if (is_array($handler)) {
            $handler = current($handler);
        }

        return $handler;
    }

    /**
     * Inject last error
     *
     * @param array $error
     *
     * @return Webino_Resource_Errors
     */
    public function setLastError($error)
    {
        $this->_lastError = $error;

        return $this;
    }

    /**
     * Returns ErrorHandler object
     *
     * @param mixed $handler Object, Closure or callback array
     *
     * @throws ErrorsException
     *
     * @return Webino_ErrorHandler_Interface
     */
    private function _handlerObject($handler)
    {
        $handler = $this->_instantiateHandler($handler);

        if ((is_object($handler)
            && 'Closure' != get_class($handler)
            && !($handler
                    instanceof Webino_ErrorHandler_Interface
                )
            ) || (is_array($handler)
                && !($handler[0]
                    instanceof Webino_ErrorHandler_Interface)
            )
        ) {
            if (is_array($handler)) {
                $handler = $handler[0];
            }

            throw new ErrorsException(
                sprintf(
                    'ErrorHandler %s must implement %s, but it implements %s.',
                    get_class($handler), self::HANDLER_INTERFACE,
                    join(', ', class_implements($handler))
                )
            );
        }

        return $handler;
    }

    /**
     * If handler argument is class name, returns its object
     *
     * @param string $handler
     *
     * @throws ErrorsException
     *
     * @return handler
     */
    private function _instantiateHandler($handler)
    {
        if (is_string($handler)
            && !@class_exists($handler)
        ) {
            throw new ErrorsException(
                sprintf(
                    'ErrorHandler class %s not found in include path: %s',
                    $handler, get_include_path()
                )
            );
        } elseif (is_string($handler)) {
            $handler = array(
                new $handler(
                    $this->_renderer(
                        $this->_options[self::RENDERERCLASS_KEYNAME]
                    )
                ), self::WRITER_METHODNAME
            );
        }

        return $handler;
    }

    /**
     * Make renderer object from string
     *
     * @param string/RendererInterface $renderer
     * 
     * @return RendererInterface
     */
    private function _renderer($renderer)
    {
        if (is_string($renderer)
            && !@class_exists($renderer)
        ) {
            throw new ErrorsException(
                sprintf(
                    'Error Renderer class %s not found in include path: %s',
                    $renderer, get_include_path()
                )
            );
        } elseif (is_string($renderer)) {

            $renderer = new $renderer;
        }

        if (!($renderer instanceof RendererInterface)) {

            throw new ErrorsException(
                sprintf(
                    'Error Renderer %s must implement %s, '
                    . 'but it implements %s.',
                    get_class($renderer), self::RENDERER_INTERFACE,
                    join(', ', class_implements($renderer))
                )
            );
        }

        return $renderer;
    }
    
    /**
     * Registers exception handler
     *
     * @param string|closure|array $callback eg. set_error_handler
     * @param string               $handler  eg. Webino_ErrorHandler
     * @param array                $options  Handler ptions
     * @param array                $error    Last error
     *
     * @throws ErrorsException
     *
     * @return void
     */
    private function _registerShutdown(
        $callback, $handler, array $options, $error
    )
    {
        if (!is_callable($callback)) {

            throw new ErrorsException(
                sprintf(
                    'ErrorHandler shutdownCallback %s is not callable.',
                    $callback
                )
            );
        }

        $callback(
            function() use ($handler, $options, $error)
            {
                if (null === $error) {
                    $error = error_get_last();
                }

                if ( !$error ) {
                    
                    return;
                }

                call_user_func_array(
                    $handler, array(
                        $error['type'], $error['message'],
                        $error['file'], $error['line'],
                        array(),  $options, null
                    )
                );
            }

        );
    }

    /**
     * Registers exception handler
     *
     * @param string|closure|array $callback eg. set_error_handler
     * @param string               $handler  eg. Webino_ErrorHandler
     * @param array                $options  Handler ptions
     *
     * @throws ErrorsException
     */
    private function _registerException($callback, $handler, array $options)
    {
        if (!is_callable($callback)) {

            throw new ErrorsException(
                sprintf(
                    'ErrorHandler exceptionCallback %s is not callable.',
                    $callback
                )
            );
        }

        $callback(
            function($exception) use ($handler, $options) {

                call_user_func_array(
                    $handler, array(
                        $exception->getCode(), $exception->getMessage(),
                        $exception->getFile(), $exception->getLine(),
                        array(), $options, $exception
                    )
                );
            }
        );
    }

    /**
     * Registers error handler
     *
     * @param string|closure|array $callback eg. set_error_handler
     * @param string               $handler  eg. Webino_ErrorHandler
     * @param array                $options  Handler ptions
     *
     * @throws ErrorsException
     */
    private function _registerError($callback, $handler, array $options)
    {
        if (!is_callable($callback)) {

            throw new ErrorsException(
                sprintf(
                    'ErrorHandler errorCallback %s is not callable.',
                    $callback
                )
            );
        }

        $callback(
            function($code, $msg, $file, $line, $context)
                use ($handler, $options)
            {
                call_user_func_array(
                    $handler, array(
                        $code, $msg, $file, $line, $context, $options, null
                    )
                );
            }
        );
    }
}
