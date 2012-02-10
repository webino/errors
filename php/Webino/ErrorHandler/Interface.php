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

use Webino_ErrorHandler_Renderer_Interface as Renderer;

/**
 * Interface for ErrorHandler class
 * 
 * ErrorHandler basically has getters of its properties
 * and write method to write error page to output.
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
interface Webino_ErrorHandler_Interface
{
    /**
     * Constructor
     *
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer);

    /**
     * Depends on options, generates error page and sends error to user.
     *
     * @param int          $code      Error code
     * @param string       $msg       Error message
     * @param string       $file      Error file path
     * @param int          $line      Error line number
     * @param array        $context   Error context
     * @param array        $options   
     * @param Exception    $exception
     */
    public function writeError(
        $code, $msg, $file, $line, array $context,
        array $options, Exception $exception=null
    );

    /**
     * Error title
     *
     * @return string
     */
    public function getErrorTitle();

    /**
     * Error code number
     *
     * @return string
     */
    public function getErrorCode();

    /**
     * Error message text
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * Exception class in brackets is returned
     * 
     * If error was caused by exception.
     *
     * @return string
     */
    public function getErrorNote();

    /**
     * Error file path
     *
     * @return string
     */
    public function getErrorFile();

    /**
     * Error line number
     *
     * @return string
     */
    public function getErrorLine();

    /**
     * Request time
     *
     * @return string
     */
    public function getErrorGeneratedTime();

    /**
     * Hyperlink of error page
     *
     * @return string
     */
    public function getErrorPageUrl();

    /**
     * Current PHP version
     *
     * @return string
     */
    public function getPHPVersion();

    /**
     * Server info text
     *
     * @return string
     */
    public function getServerInfo();

    /**
     * Current Webino version
     *
     * @return string
     */
    public function getWebinoVersion();

    /**
     * Used in copyright
     *
     * @return string
     */
    public function getCurrentYear();

    /**
     * Returns title for error page
     *
     * @return string
     */
    public function getTitle();

    /**
     * Fragment of error file code
     *
     * @return string
     */
    public function getErrorFileCode();

    /**
     * Returns HTML code of ErrorHandler widgets
     *
     * @return string
     */
    public function getWidgets();
}
