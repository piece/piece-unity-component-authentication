<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>, 2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.0.0
 */

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'] = null;
$GLOBALS['PIECE_UNITY_Service_Authentication_State_DefaultRealm'] = '_default';

// }}}
// {{{ Piece_Unity_Service_Authentication_State

/**
 * The container class for all authentication states by realm.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>, 2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.0.0
 */
class Piece_Unity_Service_Authentication_State
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_statesByRealm = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ singleton()

    /**
     * Returns the Piece_Unity_Service_Authentication_State instance if
     * it exists. If it not exists, a new instance of
     * Piece_Unity_Service_Authentication_State will be created and returned.
     *
     * @return Piece_Unity_Service_Authentication_State
     * @static
     */
    function &singleton()
    {
        if (is_null($GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'])) {
            $GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'] = &new Piece_Unity_Service_Authentication_State();
        }

        return $GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'];
    }

    // }}}
    // {{{ setIsAuthenticated()

    /**
     * Marks the user as "authenticated" or "not authenticated" in the given
     * realm.
     *
     * @param string  $realm
     * @param boolean $isAuthenticated
     */
    function setIsAuthenticated($realm, $isAuthenticated)
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
    function isAuthenticated($realm)
    {
        $realm = $this->_getRealm($realm);
        if (array_key_exists($realm, $this->_statesByRealm)) {
            if (array_key_exists('isAuthenticated', $this->_statesByRealm[$realm])) {
                return $this->_statesByRealm[$realm]['isAuthenticated'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // }}}
    // {{{ setCallbackURL()

    /**
     * Sets the callback URL to the given realm.
     *
     * @param string $realm
     * @param string $callbackURL
     */
    function setCallbackURL($realm, $callbackURL)
    {
        $this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURL'] = $callbackURL;
    }

    // }}}
    // {{{ getCallbackURL()

    /**
     * Gets the callback URL for the given realm.
     *
     * @param string $realm
     * @return string
     */
    function getCallbackURL($realm)
    {
        return @$this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURL'];
    }

    // }}}
    // {{{ removeCallbackURL()

    /**
     * Removes the callback URL for the given realm.
     *
     * @param string $realm
     */
    function removeCallbackURL($realm)
    {
        unset($this->_statesByRealm[ $this->_getRealm($realm) ]['callbackURL']);
    }

    // }}}
    // {{{ setInstance()

    /**
     * Sets a Piece_Unity_Service_Authentication_State object as a single
     * instance.
     *
     * @param Piece_Unity_Service_Authentication_State &$instance
     */
    function setInstance(&$instance)
    {
        $GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'] = &$instance;
    }

    // }}}
    // {{{ hasCallbackURL()

    /**
     * Returns whether the given realm has the callback URL or not.
     *
     * @param string $realm
     * @return boolean
     */
    function hasCallbackURL($realm)
    {
        $realm = $this->_getRealm($realm);
        if (array_key_exists($realm, $this->_statesByRealm)) {
            if (array_key_exists('callbackURL', $this->_statesByRealm[$realm])) {
                return $this->_statesByRealm[$realm]['callbackURL'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // }}}
    // {{{ clear()

    /**
     * Removed a single instance safely.
     *
     * @static
     */
    function clear()
    {
        $GLOBALS['PIECE_UNITY_Service_Authentication_State_Instance'] = null;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _getRealm()

    /**
     * Gets the appropriate realm for the given value.
     *
     * @param string $realm
     * @return string
     */
    function _getRealm($realm)
    {
        if (is_null($realm)) {
            return $GLOBALS['PIECE_UNITY_Service_Authentication_State_DefaultRealm'];
        } else {
            return $realm;
        }
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