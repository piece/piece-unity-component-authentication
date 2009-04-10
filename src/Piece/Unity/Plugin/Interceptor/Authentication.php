<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.13.0
 */

// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * An interceptor to control the access to resources which can be accessed only by
 * authenticated users.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.13.0
 */
class Piece_Unity_Plugin_Interceptor_Authentication extends Piece_Unity_Plugin_Common implements Piece_Unity_Plugin_Interceptor_Interface
{

    // {{{ constants

    const SESSION_KEY = __CLASS__;

    // }}}
    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_scriptName;
    private $_authenticationState;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ intercept()

    /**
     * Invokes the plugin specific code.
     *
     * @return boolean
     * @throws Piece_Unity_Exception
     */
    public function intercept()
    {
        $this->_prepareAuthenticationState();

        $uri = $this->getConfiguration('uri');
        if (!$uri) {
            throw new Piece_Unity_Exception("The value of the configuration point [ uri ] on the plug-in [ {$this->_name} ] is required");
        }

        if ($this->_isAuthenticationURI($uri)) {
            return true;
        }
            
        $excludes = $this->getConfiguration('excludes');
        if ($excludes) {
            if (!is_array($excludes)) {
                throw new Piece_Unity_Exception("The value of the configuration point [ excludes ] on the plug-in [ {$this->_name} ] should be an array");
            }

            foreach ($excludes as $exclude) {
                if (preg_match("!$exclude!", $this->_scriptName)) {
                    return true;
                }
            }
        }

        $isProtectedResource = false;
        $includes = $this->getConfiguration('includes');
        if ($includes) {
            if (!is_array($includes)) {
                throw new Piece_Unity_Exception("The value of the configuration point [ includes ] on the plug-in [ {$this->_name} ] should be an array");
            }

            foreach ($includes as $include) {
                if (preg_match("!$include!", $this->_scriptName)) {
                    $isProtectedResource = true;
                    break;
                }
            }
        }

        $session = $this->context->getSession();
        $session->setPreloadCallback('_Interceptor_Authentication_StateLoader',
                                     array(__CLASS__, 'loadAuthenticationState')
                                     );
        $session->addPreloadClass('_Interceptor_Authentication_StateLoader',
                                  'Piece_Unity_Service_Autentication_State'
                                  );

        if (!$isProtectedResource) {
            return true;
        }

        $realm = $this->getConfiguration('realm');
        if ($this->_authenticationState->isAuthenticated($realm)) {
            if ($this->_authenticationState->hasCallbackURI($realm)) {
                $this->_authenticationState->removeCallbackURI($realm);
            }

            return true;
        } else {
            $this->_authenticationState->setCallbackURI(
                $realm,
                Stagehand_HTTP_ServerEnv::getRequestURI()
                                                        );
            $this->context->setView($uri);

            return false;
        }
    }

    // }}}
    // {{{ loadAuthenticationState()

    /**
     * Loads Piece_Unity_Service_Authentication_State for preventing that the instance
     * become an incomplete class.
     */
    public static function loadAuthenticationState()
    {
        include_once 'Piece/Unity/Service/Authentication/State.php';
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    protected function initialize()
    {
        $this->addConfigurationPoint('realm');
        $this->addConfigurationPoint('excludes', array());
        $this->addConfigurationPoint('includes', array());
        $this->addConfigurationPoint('uri');

        $this->_scriptName =
            $this->context->removeProxyPath($this->context->getScriptName());
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _prepareAuthenticationState()

    /**
     * Sets the Piece_Unity_Service_Authentication_State object to the session.
     */
    private function _prepareAuthenticationState()
    {
        $session = $this->context->getSession();
        $authenticationState = $session->getAttribute(self::SESSION_KEY);
        if (is_null($authenticationState)) {
            $authenticationState =
                Piece_Unity_Service_Authentication_State::singleton();
            $session->setAttributeByRef(self::SESSION_KEY, $authenticationState);
            $session->setPreloadCallback('_Interceptor_Authentication',
                                         array('Piece_Unity_Plugin_Factory', 'factory')
                                         );
            $session->addPreloadClass('_Interceptor_Authentication',
                                      'Interceptor_Authentication'
                                      );
        } else {
            Piece_Unity_Service_Authentication_State::setInstance($authenticationState);
        }

        $this->_authenticationState = $authenticationState;
    }

    // }}}
    // {{{ _isAuthenticationURI()

    /**
     * Checks whether the requested URI is the authentication URI or not.
     *
     * @param string $authenticationURI
     * @return boolean
     */
    private function _isAuthenticationURI($authenticationURI)
    {
        $url = new Net_URL2($authenticationURI);
        return $this->context->removeProxyPath($url->getPath()) == $this->_scriptName;
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
