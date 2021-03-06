<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 1.0.0
 */

// {{{ Piece_Unity_Service_Authentication_State

/**
 * The container class for all authentication states by realm.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 1.0.0
 */
class Piece_Unity_Service_Authentication_State
{

    // {{{ constants

    const REALM_DEFALT = __CLASS__;

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

    private $_statesByRealm = array();
    private static $_soleInstance;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ singleton()

    /**
     * Returns the Piece_Unity_Service_Authentication_State instance if it exists. If
     * it not exists, a new instance of Piece_Unity_Service_Authentication_State will
     * be created and returned.
     *
     * @return Piece_Unity_Service_Authentication_State
     */
    public function singleton()
    {
        if (is_null(self::$_soleInstance)) {
            self::$_soleInstance = new self();
        }

        return self::$_soleInstance;
    }

    // }}}
    // {{{ setIsAuthenticated()

    /**
     * Marks the user as "authenticated" or "not authenticated" in the given realm.
     *
     * @param string  $realm
     * @param boolean $isAuthenticated
     */
    public function setIsAuthenticated($realm, $isAuthenticated)
    {
        $this->_statesByRealm[ $this->_getRealm($realm) ]['isAuthenticated'] = $isAuthenticated;
    }

    // }}}
    // {{{ isAuthenticated()

    /**
     * Returns whether the user is authenticated in the given realm or not.
     *
     * @param string $realm
     * @return boolean
     */
    public function isAuthenticated($realm)
    {
        $realm = $this->_getRealm($realm);
        if (!array_key_exists($realm, $this->_statesByRealm)) {
            return false;
        }

        if (!array_key_exists('isAuthenticated', $this->_statesByRealm[$realm])) {
            return false;
        }

        return $this->_statesByRealm[$realm]['isAuthenticated'];
    }

    // }}}
    // {{{ setInstance()

    /**
     * Sets a Piece_Unity_Service_Authentication_State object as a single
     * instance.
     *
     * @param Piece_Unity_Service_Authentication_State $instance
     */
    public function setInstance($instance)
    {
        self::$_soleInstance = $instance;
    }

    // }}}
    // {{{ clear()

    /**
     * Removed a single instance safely.
     */
    public static function clear()
    {
        self::$_soleInstance = null;
    }

    // }}}
    // {{{ setCallbackURI()

    /**
     * Sets the callback URI to the given realm.
     *
     * @param string $realm
     * @param string $callbackURI
     * @since Method available since Release 1.1.0
     */
    public function setCallbackURI($realm, $callbackURI)
    {
        $this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURI'] = $callbackURI;
    }

    // }}}
    // {{{ getCallbackURI()

    /**
     * Gets the callback URI for the given realm.
     *
     * @param string $realm
     * @return string
     * @since Method available since Release 1.1.0
     */
    public function getCallbackURI($realm)
    {
        return @$this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURI'];
    }

    // }}}
    // {{{ removeCallbackURI()

    /**
     * Removes the callback URI for the given realm.
     *
     * @param string $realm
     * @since Method available since Release 1.1.0
     */
    public function removeCallbackURI($realm)
    {
        unset($this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURI']);
    }

    // }}}
    // {{{ hasCallbackURI()

    /**
     * Returns whether the given realm has the callback URI or not.
     *
     * @param string $realm
     * @return boolean
     * @since Method available since Release 1.1.0
     */
    public function hasCallbackURI($realm)
    {
        $realm = $this->_getRealm($realm);
        if (!array_key_exists($realm, $this->_statesByRealm)) {
            return false;
        }

        return array_key_exists('callbackURI', $this->_statesByRealm[$realm]);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ __construct()

    /**
     */
    private function __construct() {}

    // }}}
    // {{{ _getRealm()

    /**
     * Gets the appropriate realm for the given value.
     *
     * @param string $realm
     * @return string
     */
    private function _getRealm($realm)
    {
        if (is_null($realm)) {
            return self::REALM_DEFALT;
        }

        return $realm;
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
