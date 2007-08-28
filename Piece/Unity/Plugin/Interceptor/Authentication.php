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
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.13.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Authentication.php';
require_once 'Piece/Unity/ClassLoader.php';

// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * An interceptor to control the access to resources which can be accessed
 * only by authenticated users.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
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
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_NOT_READABLE
     * @throws PIECE_UNITY_ERROR_CANNOT_READ
     */
    function invoke()
    {
        $forceAuthentication = $this->_getConfiguration('forceAuthentication');
        if (!$forceAuthentication) {
            $resourcesMatch = $this->_getConfiguration('resourcesMatch');
            $resources = $this->_getConfiguration('resources');
            if (!$resourcesMatch && !$resources) {
                return true;
            }

            $isProtectedResource = false;
            if ($resourcesMatch) {
                if (!is_array($resourcesMatch)) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                            "The value of the configuration point [ resourcesMatch ] on the plug-in [ {$this->_name} ] should be an array."
                                            );
                    return;
                }
                
                $isProtectedResource = $this->_isProtectedResourceByRegex($resourcesMatch);
            }

            if (!$isProtectedResource) {
                if ($resources) {
                    if (!is_array($resources)) {
                        Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                                "The value of the configuration point [ resources ] on the plug-in [ {$this->_name} ] should be an array."
                                                );
                        return;
                    }

                    $isProtectedResource = $this->_isProtectedResource($resources);
                }
            }

            if (!$isProtectedResource) {
                return true;
            }


        }

        $url = $this->_getConfiguration('url');
        if (!$url) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ url ] on the plug-in [ {$this->_name} ] is required."
                                    );
            return;
        }

        $guard = $this->_getConfiguration('guard');
        if ($guard) {
            $guardDirectory = $this->_getConfiguration('guardDirectory');
            if (is_null($guardDirectory)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The value of the configuration point [ guardDirectory ] on the plug-in [ {$this->_name} ] is required."
                                        );
                return;
            }

            if (!is_array($guard)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The value of the configuration point [ guard ] on the plug-in [ {$this->_name} ] should be an array."
                                        );
                return;
            }

            if (!array_key_exists('class', $guard)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The \"class\" element is required for the configuration point [ guard ] on the plug-in [ {$this->_name} ]."
                                        );
                return;
            }

            if (is_null($guard['class']) || !strlen($guard['class'])) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The \"class\" element is required for the configuration point [ guard ] on the plug-in [ {$this->_name} ]."
                                        );
                return;
            }

            if (!array_key_exists('method', $guard)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The \"method\" element is required for the configuration point [ guard ] on the plug-in [ {$this->_name} ]."
                                        );
                return;
            }

            if (is_null($guard['method']) || !strlen($guard['method'])) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The \"method\" element is required for the configuration point [ guard ] on the plug-in [ {$this->_name} ]."
                                        );
                return;
            }

            if (!Piece_Unity_ClassLoader::loaded($guard['class'])) {
                Piece_Unity_ClassLoader::load($guard['class'], $guardDirectory);
                if (Piece_Unity_Error::hasErrors('exception')) {
                    return;
                }

                if (!Piece_Unity_ClassLoader::loaded($guard['class'])) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                            "The class [ {$guard['class']} ] not found in the loaded file."
                                            );
                    return;
                }
            }

            $guardClass = &new $guard['class']();
            if (!method_exists($guardClass, $guard['method'])) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The method {$guard['method']} not found in the class [ {$guard['class']} ]."
                                        );
                return;
            }

            if (!$guardClass->$guard['method']($this->_context)) {
                $this->_setForwardingURL($url);
                return false;
            }
        } else {
            $name = $this->_getConfiguration('name');
            $authenticationService = &new Piece_Unity_Service_Authentication($name);
            if (!$authenticationService->isAuthenticated()) {
                $this->_setForwardingURL($url);
                return false;
            }
        }

        return true;
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
        $this->_addConfigurationPoint('forceAuthentication', false);
        $this->_addConfigurationPoint('name', $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME']);
        $this->_addConfigurationPoint('resourcesMatch', array());
        $this->_addConfigurationPoint('resources', array());
        $this->_addConfigurationPoint('url');
        $this->_addConfigurationPoint('guard', array());
        $this->_addConfigurationPoint('guardDirectory');
        $this->_addConfigurationPoint('useCallback', false);
        $this->_addConfigurationPoint('callbackKey', $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_CALLBACK_KEY']);

        $this->_scriptName = $this->_context->getScriptName();
        if ($this->_context->usingProxy()) {
            $proxyPath = $this->_context->getProxyPath();
            if (!is_null($proxyPath)) {
                $this->_scriptName = preg_replace("!^$proxyPath!", '', $this->_scriptName);
            }
        }
    }
 
    // }}}
    // {{{ _isProtectedResource()

    /**
     * Returns whether the current resource is protected or not.
     *
     * @param array $resources
     * @return boolean
     */
    function _isProtectedResource($resources)
    {
        return in_array($this->_scriptName, $resources);
    }

    // }}}
    // {{{ _setForwardingURL()

    /**
     * Returns whether the current resource is protected or not by regex.
     *
     * @param array $resources
     * @return boolean
     */
    function _setForwardingURL($url)
    {
        $useCallback = $this->_getConfiguration('useCallback');
        if ($useCallback) {
            $callbackKey = $this->_getConfiguration('callbackKey');
            if (is_null($callbackKey) || !strlen($callbackKey)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        "The value of the configuration point [ callbackKey ] on the plug-in [ {$this->_name} ] is required."
                                        );
                return;
            }

            $view = $this->_createServiceURL($url, $callbackKey);
        } else {
            $view = $url;
        }

        $this->_context->setView($view);
    }

    // }}}
    // {{{ _createServiceURL()

    /**
     * Createss the appropriate URL for an authentication service.
     *
     * @param string $url
     * @param string $callbackKey
     * @return string
     */
    function _createServiceURL($url, $callbackKey)
    {
        if (!array_key_exists('QUERY_STRING', $_SERVER) || !strlen($_SERVER['QUERY_STRING'])) {
            $query = '';
        } else {
            $query = "?{$_SERVER['QUERY_STRING']}";
        }

        if (!array_key_exists('PATH_INFO', $_SERVER)) {
            $pathInfo = '';
        } else {
            $pathInfo = $_SERVER['PATH_INFO'];
        }

        return "$url?$callbackKey=" . htmlentities(rawurlencode($this->_context->getScriptName() . "$pathInfo$query"));
    }

    // }}}
    // {{{ _isProtectedResourceByRegex()

    /**
     * Returns whether the current resource is protected or not by regex.
     *
     * @param array $resources
     * @return boolean
     */
    function _isProtectedResourceByRegex($resources)
    {
        foreach ($resources as $resource) {
            if (preg_match("!$resource!", $this->_scriptName)) {
                return true;
            }
        }

        return false;
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
