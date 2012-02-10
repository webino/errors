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

/**
 * Abstract class for ErrorHandler widget
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
abstract class Webino_ErrorHandler_Widget_Abstract
    implements Webino_ErrorHandler_Widget_Interface
{
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
     * Error file
     *
     * @var string
     */
    protected $_errorFile;

    /**
     * Error line
     *
     * @var int
     */
    protected $_errorLine;

    /**
     * Error context
     *
     * @var array
     */
    protected $_errorContext = array();

    /**
     * Options
     *
     * @var array
     */
    protected $_options = array();
    
    /**
     * Error exception
     *
     * @var Exception
     */
    protected $_errorException = null;

    /**
     * Creates new ErrorHandler widget object
     *
     * @param int          $code      Error code
     * @param string       $msg       Error message
     * @param string       $file      Error file path
     * @param int          $line      Error line number
     * @param array        $context   Error environment variables
     * @param array        $options   Application options
     * @param Exception    $exception
     */
    public function __construct(
        $code, $msg, $file, $line, array $context,
        array $options, $exception = null
    )
    {
        $this->_errorCode      = $code;
        $this->_errorMsg       = $msg;
        $this->_errorFile      = $file;
        $this->_errorLine      = $line;
        $this->_errorContext   = $context;
        $this->_options        = $options;
        $this->_errorException = $exception;
    }

    /**
     * It's called when widget is created
     *
     * If it returns true widget is not rendered.
     *
     * @param array $options Widget options
     *
     * @return bool
     */
    public function preprocess(array $options)
    {
        return false;
    }

    /**
     * It's called after error page render, may modify html code.
     *
     * @param string $html Error page html code
     * 
     * @return string
     */
    public function postprocess($html)
    {
        return $html;
    }
}
