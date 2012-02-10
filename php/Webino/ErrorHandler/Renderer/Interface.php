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

use Webino_ErrorHandler_Interface as HandlerInterface;

/**
 * Interface for error handler renderer
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
interface Webino_ErrorHandler_Renderer_Interface
{
    /**
     * Constructor with no arguments
     */
    public function __construct();

    /**
     * Returns HTML code of ErrorHandler widgets
     *
     * @param array $widgets
     */
    public function renderWidgets(array $widgets);

    /**
     * Returns HTML code of rendered ErrorHandler
     *
     * @param HandlerInterface $errorHandler
     * @param array            $options
     */
    public function render(HandlerInterface $errorHandler, array $options);   
}
