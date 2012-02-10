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
 * @subpackage ProfilerPlugin
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    GIT: $Id$
 * @link       http://pear.webino.org/errors/
 */

use Webino_ControllerPlugin_DevBar_Interface as DevBar;
use Webino_Resource_Profiler_Interface       as Profiler;

/**
 * Overall Performance plugin for profiler
 *
 * Add performance info to profiler log.
 *
 * @category   Webino
 * @package    Errors
 * @subpackage ProfilerPlugin
 * @author     Peter Bačinský <peter@bacinsky.sk>
 * @copyright  2012 Peter Bačinský (http://www.bacinsky.sk/)
 * @license    http://www.webino.org/license/ New BSD License
 * @version    Release: @@PACKAGE_VERSION@@
 * @link       http://pear.webino.org/errors/
 */
class Webino_ProfilerPlugin_OverallPerformance
    implements Webino_ProfilerPlugin_Interface
{
    /**
     * Developer bar controller plugin
     *
     * @var DevBar
     */
    private $_devBar;

    /**
     * Inject developer bar controller plugin
     *
     * @param DevBar $devBar
     *
     * @return Webino_ProfilerPlugin_OverallPerformance
     */
    public function setDevBar(DevBar $devBar)
    {
        $this->_devBar = $devBar;

        return $this;
    }

    /**
     * Initialize profiler plugin
     *
     * @param Profiler $profiler
     * @param array    $options
     */
    public function init(Profiler $profiler, array $options)
    {
        $profiler->write(
            'send', $this->_devBar->getOverallPerformance(),
            'Overall Performance', 'INFO'
        );
    }
}
