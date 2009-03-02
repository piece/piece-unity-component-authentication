<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.13.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Interceptor/Authentication.php';
require_once 'Piece/Unity/Service/Authentication.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Authentication/State.php';

// {{{ Piece_Unity_Plugin_Interceptor_AuthenticationTestCase

/**
 * Some tests for Piece_Unity_Plugin_Interceptor_Authentication.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.13.0
 */
class Piece_Unity_Plugin_Interceptor_AuthenticationTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_oldScriptName;
    var $_context;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $this->_oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = array();
    }

    function tearDown()
    {
        Piece_Unity_Service_Authentication_State::clear();
        unset($this->_context);
        unset($_SERVER['HTTPS']);
        unset($_SERVER['QUERY_STRING']);
        unset($_SERVER['PATH_INFO']);
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        unset($_SESSION);
        $_SERVER['SCRIPT_NAME'] = $this->_oldScriptName;
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldBeAbleToAccessIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login('Foo');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldNotBeAbleToAccessIfNotAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldNotBeAbleToAccessIfAuthenticatedOtherRealm()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldBeAbleToAccessIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login('Foo');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldBeAbleToAccessIfAuthenticatedOtherRealm()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldBeAbleToAccessIfNotAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testDefaultAuthenticationRealmShouldBeUsedIfRealmIsNotGiven1()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testDefaultAuthenticationRealmShouldBeUsedIfRealmIsNotGiven2()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testRequestedURIShouldNotBeStoredIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = &new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertFalse($authenticationState->hasCallbackURI(null));
        $this->assertNull($authenticationState->getCallbackURI(null));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testRequestedURIShouldBeStoredIfNotAuthenticated1()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testRequestedURIShouldBeStoredIfNotAuthenticated2()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['HTTPS'] = 'on';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('https://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testRequestedURIShouldBeStoredIfNotAuthenticated3()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org:8201/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testRequestedURIShouldBeStoredIfNotAuthenticated4()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '8443';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['HTTPS'] = 'on';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('https://example.org:8443/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testCallbackURIShouldBeKeptUntilProtectedResourceIsRequested()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/authenticate.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $this->_context->setView('Bar');
        $this->_context->setScriptName('/authenticate.php');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Bar', $this->_context->getView());

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/authenticate.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $this->_context->setView('Baz');
        $this->_context->setScriptName('/authenticate.php');
        $service = &new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Baz', $this->_context->getView());

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/bar.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $this->_context->setView('Qux');
        $this->_context->setScriptName('/admin/bar.php');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Qux', $this->_context->getView());

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertFalse($authenticationState->hasCallbackURI(null));
        $this->assertNull($authenticationState->getCallbackURI(null));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testCallbackURIShouldBeEncoded()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['PATH_INFO'] = "/\xe5\xa7\x93/\xe4\xb9\x85\xe4\xbf\x9d";
        $_SERVER['QUERY_STRING'] = '%E5%90%8D=%E6%95%A6%E5%95%93';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php/%E5%A7%93/%E4%B9%85%E4%BF%9D?%E5%90%8D=%E6%95%A6%E5%95%93',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testCallbackURIShouldBeKeptIfAuthenticationURIIsRequestedDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );

        $authenticationState = &Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/authenticate.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $this->_context->setView('Bar');
        $this->_context->setScriptName('/authenticate.php');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Bar', $this->_context->getView());
        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/authenticate.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $this->_context->setView('Bar');
        $this->_context->setScriptName('/authenticate.php');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Bar', $this->_context->getView());
        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldIncludeUrisByIncludes()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm' => 'Foo',
                                'uri' => 'http://example.org/authenticate.php',
                                'includes' => array('^/admin/.*')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldIncludeUrisByResourcesMatch()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm' => 'Foo',
                                'uri' => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/.*')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('http://example.org/authenticate.php',
                            $this->_context->getView()
                            );
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldExcludeUrisByExcludes()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm' => 'Foo',
                                'uri' => 'http://example.org/authenticate.php',
                                'includes' => array('^/admin/.*'),
                                'excludes' => array('^/admin/foo\.php$')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldExcludeTheAuthenticationUri()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/users/authenticate.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/users/authenticate.php',
                                'includes' => array('^/.*')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldExcludeTheAuthenticationUriWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['SCRIPT_NAME'] = '/users/authenticate.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/foo/users/authenticate.php',
                                'includes' => array('^/.*')
                                );
        $this->_configure($configurations);
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testShouldExcludeTheAuthenticationUriWithDirectAccessToABackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['SCRIPT_NAME'] = '/users/authenticate.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/foo/users/authenticate.php',
                                'includes' => array('^/.*')
                                );
        $this->_configure($configurations);
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());
    }

    /**
     * @since Method available since Release 1.1.1
     */
    function testShouldStoreAnAuthenticationStateObjectInASession()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        $this->assertEquals('Foo', $this->_context->getView());

        $session = &$this->_context->getSession();

        $this->assertTrue($session->hasAttribute($GLOBALS['PIECE_UNITY_Interceptor_Authentication_AuthenticationStateSessionKey']));
        $this->assertEquals(strtolower('Piece_Unity_Service_Authentication_State'),
                            strtolower(get_class($session->getAttribute($GLOBALS['PIECE_UNITY_Interceptor_Authentication_AuthenticationStateSessionKey'])))
                            );
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**
     * @since Method available since Release 1.0.0
     */
    function _configure($configurations)
    {
        $config = &new Piece_Unity_Config();
        foreach ($configurations as $key => $configuration) {
            $config->setConfiguration('Interceptor_Authentication',
                                      $key, $configuration
                                      );
        }

        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setView('Foo');
        $session = &$context->getSession();
        @$session->start();
        $this->_context = &$context;
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
