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

// {{{ Piece_Unity_Plugin_Interceptor_AuthenticationTest

/**
 * Some tests for Piece_Unity_Plugin_Interceptor_Authentication.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.13.0
 */
class Piece_Unity_Plugin_Interceptor_AuthenticationTest extends PHPUnit_Framework_TestCase
{

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

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Service_Authentication_State::clear();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = array();
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function allowToAccessIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login('Foo');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function denyToAccessAProtectedResourceIfNotAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function denyToAccessAProtectedResourceIfAuthenticatedInOtherRealm()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function allowToAccessANonProtectedResourceIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login('Foo');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function allowToAccessANonProtectedResourceIfAuthenticatedInOtherRealm()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function allowToAccessANonProtectedResourceIfNotAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function useTheDefaultAuthenticationRealmIfTheRealmIsNotGiven1()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function useTheDefaultAuthenticationRealmIfTheRealmIsNotGiven2()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login('Bar');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function notStoreTheRequestedUriIfAuthenticated()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $service = new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertFalse($authenticationState->hasCallbackURI(null));
        $this->assertNull($authenticationState->getCallbackURI(null));
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function storeTheRequestedUriIfNotAuthenticated1()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function storeTheRequestedUriIfNotAuthenticated2()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['HTTPS'] = 'on';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('https://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function storeTheRequestedUriIfNotAuthenticated3()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org:8201/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.1.2
     */
    public function storeTheRequestedUriIfNotAuthenticated4()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '8443';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['HTTPS'] = 'on';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('https://example.org:8443/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function keepTheCallbackUriUntilAnyProtectedResourceIsRequested()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

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
        Piece_Unity_Context::singleton()->setView('Bar');
        Piece_Unity_Context::singleton()->setScriptName('/authenticate.php');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Bar', Piece_Unity_Context::singleton()->getView());

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

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
        Piece_Unity_Context::singleton()->setView('Baz');
        Piece_Unity_Context::singleton()->setScriptName('/authenticate.php');
        $service = new Piece_Unity_Service_Authentication();
        $service->login();
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Baz', Piece_Unity_Context::singleton()->getView());

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

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
        Piece_Unity_Context::singleton()->setView('Qux');
        Piece_Unity_Context::singleton()->setScriptName('/admin/bar.php');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Qux', Piece_Unity_Context::singleton()->getView());

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertFalse($authenticationState->hasCallbackURI(null));
        $this->assertNull($authenticationState->getCallbackURI(null));
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function encodeTheCallbackUri()
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
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php/%E5%A7%93/%E4%B9%85%E4%BF%9D?%E5%90%8D=%E6%95%A6%E5%95%93',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function keepTheCallbackUriIfTheAuthenticationUriIsRequestedDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );

        $authenticationState = Piece_Unity_Service_Authentication_State::singleton();

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
        Piece_Unity_Context::singleton()->setView('Bar');
        Piece_Unity_Context::singleton()->setScriptName('/authenticate.php');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Bar', Piece_Unity_Context::singleton()->getView());
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
        Piece_Unity_Context::singleton()->setView('Bar');
        Piece_Unity_Context::singleton()->setScriptName('/authenticate.php');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Bar', Piece_Unity_Context::singleton()->getView());
        $this->assertTrue($authenticationState->hasCallbackURI(null));
        $this->assertEquals('http://example.org/admin/foo.php',
                            $authenticationState->getCallbackURI(null)
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function includeUrisByIncludes()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm' => 'Foo',
                                'uri' => 'http://example.org/authenticate.php',
                                'includes' => array('^/admin/.*')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function includeUrisByResourcesMatch()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $configurations = array('realm' => 'Foo',
                                'uri' => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/.*')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('http://example.org/authenticate.php',
                            Piece_Unity_Context::singleton()->getView()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function excludeUrisByExcludes()
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
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function excludeTheAuthenticationUri()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/users/authenticate.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/users/authenticate.php',
                                'includes' => array('^/.*')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function excludeTheAuthenticationUriForAProxy()
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
        $context = Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function excludeTheAuthenticationUriForDirectAccessToABackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['SCRIPT_NAME'] = '/users/authenticate.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/foo/users/authenticate.php',
                                'includes' => array('^/.*')
                                );
        $this->_configure($configurations);
        $context = Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.1.1
     */
    public function storeTheAuthenticationStateObjectInTheSession()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $configurations = array('realm'     => 'Foo',
                                'uri'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $this->_configure($configurations);
        $interceptor = new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->intercept();

        $this->assertEquals('Foo', Piece_Unity_Context::singleton()->getView());

        $session = Piece_Unity_Context::singleton()->getSession();

        $this->assertTrue($session->hasAttribute(Piece_Unity_Plugin_Interceptor_Authentication::SESSION_KEY));
        $this->assertType('Piece_Unity_Service_Authentication_State',
                          $session->getAttribute(Piece_Unity_Plugin_Interceptor_Authentication::SESSION_KEY)
                          );
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**
     * @since Method available since Release 1.0.0
     */
    private function _configure($configurations)
    {
        $config = new Piece_Unity_Config();
        foreach ($configurations as $key => $configuration) {
            $config->setConfiguration('Interceptor_Authentication',
                                      $key, $configuration
                                      );
        }

        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setView('Foo');
        $session = $context->getSession();
        @$session->start();
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
