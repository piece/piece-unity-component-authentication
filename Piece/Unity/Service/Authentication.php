<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Service_Authentication
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.0.0
 */

require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/URL.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'] = '__service';
$GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_CALLBACK_KEY'] = 'callback';

// {{{ Piece_Unity_Service_Authentication

/**
 * A helper class of authentication for the Interceptor_Authentication plug-in.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Service_Authentication
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.0.0
 */
class Piece_Unity_Service_Authentication
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_serviceName;
    var $_callback;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Sets the Piece_Unity_ViewElement object.
     */
    function Piece_Unity_Service_Authentication($serviceName = null)
    {
        if (!$serviceName) {
            $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];
        }
        $this->_serviceName = $serviceName;

        $this->_authenticationsSessionKey  = sprintf('_%s_Services', __CLASS__ );
        $this->_callbacksSessionKey = sprintf('_%s_Callbacks', __CLASS__ );

        $callback = $this->_getServicesSessionParameter($this->_callbacksSessionKey,
                                                        $this->_serviceName
                                                        );
        if ($callback) {
            $this->_callback = $callback;
        } else {
            $this->_callback = null;
        }
    }

    // }}}
    // {{{ login()

    /**
     * Permits the current service.
     *
     * @param array  $serviceName
     */
    function login()
    {
        $this->_setServicesSessionParameter($this->_authenticationsSessionKey,
                                            $this->_serviceName, true
                                            );
    }

    // }}}
    // {{{ logout()

    /**
     * Denies the current service.
     *
     * @param array  $serviceName
     */
    function logout()
    {
        $this->_setServicesSessionParameter($this->_authenticationsSessionKey,
                                            $this->_serviceName, false
                                            );
    }

    // }}}
    // {{{ isAuthenticated()

    /**
     * Returns whether the current service is authenticated.
     *
     * @param array  $serviceName
     */
    function isAuthenticated()
    {
        return $this->_getServicesSessionParameter($this->_authenticationsSessionKey,
                                                   $this->_serviceName
                                                   );
    }

    // }}}
    // {{{ catchCallback

    /**
     * Catches the callback parameter from request parameter.
     *
     */
    function catchCallback($callbackKey = null)
    {
        if (!$callbackKey) {
            $callbackKey = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_CALLBACK_KEY'];
        }

        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();

        if ($request->hasParameter($callbackKey)) {
            $this->_callback = $request->getParameter($callbackKey);
            $this->_setServicesSessionParameter($this->_callbacksSessionKey,
                                                $this->_serviceName,
                                                $this->_callback
                                                );
        }
    }

    // }}}
    // {{{ getCallback

    /**
     * Returns the catches callback parameter.
     *
     * @return mixed
     */
    function getCallback()
    {
        return $this->_callback;
    }

    // }}}
    // {{{ removeCallback

    /**
     * Removes the catches callback parameter.
     *
     * @return mixed
     */
    function removeCallback()
    {
        $this->_callback = null;
        $this->_setServicesSessionParameter($this->_callbacksSessionKey,
                                            $this->_serviceName, null
                                            );
    }

    // }}}
    // {{{ clearCallbacks

    /**
     * Clears all callback parameters.
     *
     * @return mixed
     */
    function clearCallback()
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();
        $session->removeAttribute($this->_callbacksSessionKey);
    }

    // }}}
    // {{{ forwardByCallback()

    /**
     * 
     *
     * @return mixed
     */
    function forwardByCallback()
    {
        if (!$this->_callback) {
            return;
        }

        $path = rawurldecode(html_entity_decode($this->_callback));
        $url = Piece_Unity_URL::create('http://example.org' . $path);

        $context = &Piece_Unity_Context::singleton();
        $config = &$context->getConfiguration();
        $config->setConfiguration('View', 'forcedView', $url);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _setServicesSessionParameter()

    /**
     * Sets the services authentication status.
     */
    function _setServicesSessionParameter($sessionKey, $serviceName, $parameter)
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();

        if ($session->hasAttribute($sessionKey)) {
            $services = $session->getAttribute($sessionKey);
        } else {
            $services = array();
        }

        $services[$serviceName] = $parameter;
        $session->setAttribute($sessionKey, $services);
    }

    // }}}
    // {{{ _getServicesSessionParameter()

    /**
     * Returns the services authentication status.
     */
    function _getServicesSessionParameter($sessionKey, $serviceName)
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();

        if (!$session->hasAttribute($sessionKey)) {
            return false;
        }

        $services = $session->getAttribute($sessionKey);
        if (!array_key_exists($serviceName, $services)) {
            return false;
        }

        return $services[$serviceName];
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
?>
