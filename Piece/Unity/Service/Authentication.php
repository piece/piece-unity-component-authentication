<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.0.0
 */

require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Service/Authentication/State.php';

// {{{ Piece_Unity_Service_Authentication

/**
 * A helper class which make it easy to mark a user as "authenticated" or
 * "not authenticated".
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Service_Authentication
 * @copyright  2007-2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
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

    var $_state;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Sets a single instance of
     * Piece_Unity_Service_Authentication_State to the property.
     */
    function Piece_Unity_Service_Authentication()
    {
        $this->_state = &Piece_Unity_Service_Authentication_State::singleton();
    }

    // }}}
    // {{{ login()

    /**
     * Marks the user as "authenticated" in the given realm.
     *
     * @param string $realm
     */
    function login($realm = null)
    {
        $this->_state->setIsAuthenticated($realm, true);
    }

    // }}}
    // {{{ logout()

    /**
     * Marks the user as "not authenticated" in the given realm.
     *
     * @param string $realm
     */
    function logout($realm = null)
    {
        $this->_state->setIsAuthenticated($realm, false);
    }

    // }}}
    // {{{ isAuthenticated()

    /**
     * Returns whether the user is authenticated in the given realm or not.
     *
     * @param string $realm
     * @return boolean
     */
    function isAuthenticated($realm = null)
    {
        return $this->_state->isAuthenticated($realm);
    }

    // }}}
    // {{{ redirectToCallbackURL()

    /**
     * Redirects to the callback URI for the given realm.
     *
     * @param string $realm
     * @deprecated Method deprecated in Release 1.1.0
     */
    function redirectToCallbackURL($realm = null)
    {
        $this->redirectToCallbackURI($realm);
    }

    // }}}
    // {{{ hasCallbackURL()

    /**
     * Returns whether the given realm has the callback URI or not.
     *
     * @param string $realm
     * @return boolean
     * @deprecated Method deprecated in Release 1.1.0
     */
    function hasCallbackURL($realm = null)
    {
        return $this->hasCallbackURI($realm);
    }

    // }}}
    // {{{ redirectToCallbackURI()

    /**
     * Redirects to the callback URI for the given realm.
     *
     * @param string $realm
     * @since Method available since Release 1.1.0
     */
    function redirectToCallbackURI($realm = null)
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setView($this->_state->getCallbackURI($realm));
        $config = &$context->getConfiguration();
        $config->setConfiguration('Renderer_Redirection', 'isExternal', true);
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
    function hasCallbackURI($realm = null)
    {
        return $this->_state->hasCallbackURI($realm);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

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
