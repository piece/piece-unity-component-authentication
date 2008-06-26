<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
 *               2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>,
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
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.13.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Authentication/State.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Interceptor_Authentication_AuthenticationStateSessionKey'] = '_authentication';

// }}}
// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * An interceptor to control the access to resources which can be accessed only by
 * authenticated users.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.13.0
 */
class Piece_Unity_Plugin_Interceptor_Authentication extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_scriptName;
    var $_authenticationState;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @return boolean
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function invoke()
    {
        $this->_prepareAuthenticationState();

        $url = $this->_getConfiguration('url');
        if (!$url) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ url ] on the plug-in [ {$this->_name} ] is required."
                                    );
            return;
        }

        $excludes = $this->_getConfiguration('excludes');
        if ($excludes) {
            if (!is_array($excludes)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The value of the configuration point [ excludes ] on the plug-in [ {$this->_name} ] should be an array."
                                        );
                return;
            }

            foreach ($excludes as $exclude) {
                if (preg_match("!$exclude!", $this->_scriptName)) {
                    return true;
                }
            }
        }

        $isProtectedResource = false;
        $includes = $this->_getConfiguration('includes');
        if ($includes) {
            if (!is_array($includes)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The value of the configuration point [ includes ] on the plug-in [ {$this->_name} ] should be an array."
                                        );
                return;
            }

            foreach ($includes as $include) {
                if (preg_match("!$include!", $this->_scriptName)) {
                    $isProtectedResource = true;
                    break;
                }
            }
        }

        if (!$isProtectedResource) {
            $resources = $this->_getConfiguration('resources');
            if ($resources) {
                if (!is_array($resources)) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                            "The value of the configuration point [ resources ] on the plug-in [ {$this->_name} ] should be an array."
                                            );
                    return;
                }

                $isProtectedResource = in_array($this->_scriptName, $resources);
            }
        }

        $session = &$this->_context->getSession();
        $session->setPreloadCallback('_Interceptor_Authentication_StateLoader',
                                     array(__CLASS__, 'loadAuthenticationState')
                                     );
        $session->addPreloadClass('_Interceptor_Authentication_StateLoader',
                                  'Piece_Unity_Service_Autentication_State'
                                  );

        if (!$isProtectedResource) {
            return true;
        }

        $realm = $this->_getConfiguration('realm');
        if ($this->_authenticationState->isAuthenticated($realm)) {
            if ($this->_authenticationState->hasCallbackURL($realm)) {
                $this->_authenticationState->removeCallbackURL($realm);
            }

            return true;
        } else {
            $this->_storeRequestedURL($realm);
            $this->_context->setView($url);

            return false;
        }
    }

    // }}}
    // {{{ loadAuthenticationState()

    /**
     * Loads Piece_Unity_Service_Authentication_State for preventing that
     * the instance become an incomplete class.
     *
     * @static
     */
    function loadAuthenticationState()
    {
        include_once 'Piece/Unity/Service/Authentication/State.php';
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('realm');
        $this->_addConfigurationPoint('resourcesMatch', array()); // deprecated
        $this->_addConfigurationPoint('resources', array());      // deprecated
        $this->_addConfigurationPoint('url');
        $this->_addConfigurationPoint('excludes', array());
        $this->_addConfigurationPoint('includes',
                                      $this->_getConfiguration('resourcesMatch')
                                      );

        $this->_scriptName = $this->_context->getScriptName();
        if ($this->_context->usingProxy()) {
            $proxyPath = $this->_context->getProxyPath();
            if (!is_null($proxyPath)) {
                $this->_scriptName =
                    preg_replace("!^$proxyPath!", '', $this->_scriptName);
            }
        }
    }
 
    // }}}
    // {{{ _storeRequestedURL()

    /**
     * Stores the requested URL with the given realm.
     *
     * @param string $realm
     */
    function _storeRequestedURL($realm)
    {
        if (!array_key_exists('QUERY_STRING', $_SERVER)
            || !strlen($_SERVER['QUERY_STRING'])
            ) {
            $query = '';
        } else {
            $query = "?{$_SERVER['QUERY_STRING']}";
        }

        if (!array_key_exists('PATH_INFO', $_SERVER)) {
            $pathInfo = '';
        } else {
            $pathInfo = str_replace('%2F', '/', rawurlencode($_SERVER['PATH_INFO']));
        }

        if ($_SERVER['SERVER_PORT'] != 443) {
            $protocol = 'http';
        } else {
            $protocol = 'https';
        }

        if ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) {
            $port = '';
        } else {
            $port = ":{$_SERVER['SERVER_PORT']}";
        }

        $this->_authenticationState->setCallbackURL($realm,
                                                    "$protocol://{$_SERVER['SERVER_NAME']}$port" .
                                                    str_replace('//', '/', $_SERVER['SCRIPT_NAME']) .
                                                    "$pathInfo$query"
                                                    );
    }

    // }}}
    // {{{ _prepareAuthenticationState()

    /**
     * Sets the Piece_Unity_Service_Authentication_State object to the session.
     */
    function _prepareAuthenticationState()
    {
        $session = &$this->_context->getSession();
        $authenticationState = &$session->getAttribute($GLOBALS['PIECE_UNITY_Interceptor_Authentication_AuthenticationStateSessionKey']);
        if (is_null($authenticationState)) {
            $authenticationState =
                &Piece_Unity_Service_Authentication_State::singleton();
            $session->setAttributeByRef($GLOBALS['PIECE_UNITY_Interceptor_Authentication_AuthenticationStateSessionKey'], $authenticationState);
            $session->setPreloadCallback('_Interceptor_Authentication',
                                         array('Piece_Unity_Plugin_Factory', 'factory')
                                         );
            $session->addPreloadClass('_Interceptor_Authentication',
                                      'Interceptor_Authentication'
                                      );
        } else {
            Piece_Unity_Service_Authentication_State::setInstance($authenticationState);
        }

        $this->_authenticationState = &$authenticationState;
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
