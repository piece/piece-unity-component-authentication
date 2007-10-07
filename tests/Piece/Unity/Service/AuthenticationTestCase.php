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

require_once realpath(dirname(__FILE__) . '/../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Service/Authentication.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Service/Authentication/State.php';

// {{{ Piece_Unity_Service_AuthenticationTestCase

/**
 * TestCase for Piece_Unity_Service_Authentication
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>, 2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.0.0
 */
class Piece_Unity_Service_AuthenticationTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_authentication;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $this->_authentication = &new Piece_Unity_Service_Authentication();
        $this->_authentication->logout('Foo');
    }

    function tearDown()
    {
        Piece_Unity_Service_Authentication_State::clear();
        Piece_Unity_Context::clear();
    }

    function testUserShouldBeMarkedAsAuthenticatedByLogin()
    {
        $this->assertFalse($this->_authentication->isAuthenticated('Foo'));

        $this->_authentication->login('Foo');

        $this->assertTrue($this->_authentication->isAuthenticated('Foo'));
    }

    function testUserShouldBeMarkedAsNotAuthenticatedByLogout()
    {
        $this->assertFalse($this->_authentication->isAuthenticated('Foo'));

        $this->_authentication->login('Foo');

        $this->assertTrue($this->_authentication->isAuthenticated('Foo'));

        $this->_authentication->logout('Foo');

        $this->assertFalse($this->_authentication->isAuthenticated('Foo'));
    }

    function testDefaultRealmShouldBeUsedIfRealmIsNotGiven()
    {
        $this->assertFalse($this->_authentication->isAuthenticated());

        $this->_authentication->login();

        $this->assertTrue($this->_authentication->isAuthenticated());

        $this->_authentication->logout();

        $this->assertFalse($this->_authentication->isAuthenticated());
    }

    function testRequestShouldBeRedirectedToCallbackURL()
    {
        $state = &Piece_Unity_Service_Authentication_State::singleton();
        $state->setCallbackURL(null, 'http://example.org/path/to/callback.php');
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $this->assertFalse($this->_authentication->isAuthenticated());

        $this->_authentication->login();

        $this->assertTrue($this->_authentication->hasCallbackURL());

        $this->_authentication->redirectToCallbackURL();

        $this->assertEquals('http://example.org/path/to/callback.php', $context->getView());
        $this->assertTrue($config->getConfiguration('Renderer_Redirection', 'isExternal'));
    }

    function testRequestShouldBeRedirectedToCallbackURLWithSpecifiedRealm()
    {
        $state = &Piece_Unity_Service_Authentication_State::singleton();
        $state->setCallbackURL('Foo', 'http://example.org/path/to/callback.php');
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $this->assertFalse($this->_authentication->isAuthenticated('Foo'));

        $this->_authentication->login('Foo');

        $this->assertTrue($this->_authentication->hasCallbackURL('Foo'));

        $this->_authentication->redirectToCallbackURL('Foo');

        $this->assertEquals('http://example.org/path/to/callback.php', $context->getView());
        $this->assertTrue($config->getConfiguration('Renderer_Redirection', 'isExternal'));
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
?>