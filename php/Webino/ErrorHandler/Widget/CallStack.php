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

use WebinoX_Text_Highlight as Highlight;

/**
 * Call stack widget for ErrorHandler
 *
 * example of options:
 *
 * - script  = PEAR_PHP_DIR "/Webino/layouts/error-handler/widgets/dev-bar.html"
 * - appTimeConstant = "APPLICATION_TIME"
 * - options.applicationPath = APPLICATION_PATH
 * - options.stackFunction   = xdebug_get_function_stack
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
class Webino_ErrorHandler_Widget_CallStack
    extends    Webino_ErrorHandler_Widget_Abstract
    implements Webino_ErrorHandler_Widget_CallStack_Interface
{
    /**
     * Name of stack function option key
     */
    const STACKFUNCTION_KEYNAME = 'stackFunction';

    /**
     * Name of application path option key
     */
    const APPLICATIONPATH_KEYNAME = 'applicationPath';

    /**
     * Name of call stack code placeholder
     */
    const CALLSTACK_PLACEHOLDER = '{CALLSTACK}';

    /**
     * Call stack trace list
     *
     * @var array
     */
    private $_trace;

    /**
     * Call stack code
     *
     * @var string
     */
    private $_callStack;

    /**
     * Absolute application path
     *
     * @var string
     */
    private $_applicationPath;

    /**
     * If stack trace is not available returns true
     *
     * If error was not caused by exception tries to get function stack
     * from debug function, if exists.
     *
     * @param array $options
     *
     * @return bool
     */
    public function preprocess(array $options)
    {
        $this->_applicationPath = $options[self::APPLICATIONPATH_KEYNAME];

        if ($this->_errorException) {
            $this->_trace = $this->_errorException->getTrace();
        } else {

            if (!function_exists($options[self::STACKFUNCTION_KEYNAME])) {
                
                return true;
            }

            $this->_trace = $options[self::STACKFUNCTION_KEYNAME]();
            
            array_shift($this->_trace);
            krsort($this->_trace);

            // unset error handler files from trace
            foreach ($this->_trace as $itemIndex => $item) {
                if (strstr($item['file'], 'Webino/Resource/Errors')
                    || strstr($item['file'], 'Webino/ErrorHandler')
                    || strstr($item['file'], 'QueryPath/')
                ) {
                    unset($this->_trace[$itemIndex]);
                }
            }
        }

        if (!count($this->_trace)) {
            
            return true;
        }
    }

    /**
     * Return HTML code with call stack placeholder replaced by call stack code
     *
     * @param string $html
     *
     * @return string
     */
    public function postprocess($html)
    {
        return str_replace(
            self::CALLSTACK_PLACEHOLDER, $this->_callStack, $html
        );
    }

    /**
     * Render call stack code and return placeholder
     *
     * @return string Call stack placeholder
     */
    public function getCallStack()
    {
        $html      = '';
        $lineIndex = 1;
        
        foreach ($this->_trace as $item) {

            $filepath = '';
            if (isset($item['file'])) {
                $filepath = str_replace(
                    dirname($this->_applicationPath), '.', $item['file']
                );
            }

            $line = '';
            if (isset($item['line'])) {
                $line = $item['line'];
            }

            $html.= sprintf(
                '<li>%s. <em>%s/</em><strong>%s</strong>:%s %s ', $lineIndex,
                dirname($filepath), basename($filepath), $line,
                '<a href="#" class="show-source">source <abbr>►</abbr></a>'
            );

            $function = '';

            if (isset($item['function'])) {
                $function = $item['function'];
            }

            if (isset($item['class'])) {

                $type = '->';
                if (isset($item['type'])) {
                    $type = $item['type'];
                }

                $function = $item['class'] . $type . $item['function'];
            }

            if (isset($item['args']) && count($item['args'])) {

                $html.= sprintf(
                    '<span class="side">%s(%s)</span>', $function,
                    '<a href="#" class="show-args">arguments <abbr>►</abbr></a>'
                );

                $html.= '<div class="table args"><table>';

                foreach ($item['args'] as $argIndex=>$arg) {

                    if (is_object($arg)) {
                        $arg = get_class($arg);

                    }

                    $html.= '<tr>' . sprintf('<td>#%s</td>', $argIndex)
                         . sprintf('<td>%s</td>', $arg) . '</tr>';
                }

                $html.= '</table></div>';

            } else {
                $html.= sprintf(
                    '<span class="side">%s()</span>', $function
                );
            }

            if (isset($item['file'])) {
                $html.= sprintf(
                    '<div class="code source">%s</div>',
                    Highlight::php($item['file'], $item['line'], 6, 6)
                );
            }

            $html.= '</li>';

            $lineIndex++;
        }

        $this->_callStack =  $html . '</ul>';

        return self::CALLSTACK_PLACEHOLDER;
    }
}
