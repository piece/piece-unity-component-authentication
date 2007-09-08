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
 * @see        Piece_Unity_Plugin_Interceptor_Authentication
 * @since      File available since Release 0.13.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Interceptor/Authentication.php';
require_once 'Piece/Unity/Service/Authentication.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Interceptor_AuthenticationTestCase

/**
 * TestCase for Piece_Unity_Plugin_Interceptor_Authentication
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @see        Piece_Unity_Plugin_Interceptor_Authentication
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

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $this->_oldScriptName = $_SERVER['SCRIPT_NAME'];
    }

    function tearDown()
    {
        foreach (array('Foo', 'Bar', null) as $realm) {
            $service = &new Piece_Unity_Service_Authentication($realm);
            $service->logout();
        }
        $_SERVER['SCRIPT_NAME'] = $this->_oldScriptName;
        unset($GLOBALS['PIECE_UNITY_Plugin_Interceptor_AuthenticationTestCase_isAuthenticated']);
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testProtectedResourceShouldNotBeAbleToAccessIfAuthenticated()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );

        $this->_setIsAuthenticated(true);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    function testProtectedResourceShouldNotBeAbleToAccessIfNotAuthenticated()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));
    }

    function testNonProtectedResourceShouldAlwaysBeAbleToAccess()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );

        $this->_setIsAuthenticated(true);
        $_SERVER['SCRIPT_NAME'] = '/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));

        $this->tearDown();
        $this->setUp();

        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    function testViewShouldBeReplacedWithURLContainsEncodedCallbackURL()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php'),
                                'useCallback' => true
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['QUERY_STRING'] = '_flowExecutionTicket=15059b11c49f77a1830dca29a7a2cd045f72dd6d&firstName=Atsuhiro&lastName=Kubo&_event_confirmForm=confirm';

        $this->assertEquals('http://example.org/authenticate.php?callback=%2Fadmin%2Ffoo.php%3F_flowExecutionTicket%3D15059b11c49f77a1830dca29a7a2cd045f72dd6d%26firstName%3DAtsuhiro%26lastName%3DKubo%26_event_confirmForm%3Dconfirm',
                            $this->_invokeInterceptor($configurations)
                            );

        unset($_SERVER['QUERY_STRING']);
    }

    function testViewShouldBeReplacedWithURLContainsSpecifiedCallbackKey()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php'),
                                'useCallback' => true,
                                'callbackKey' => 'mycallback'
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $_SERVER['QUERY_STRING'] = '_flowExecutionTicket=15059b11c49f77a1830dca29a7a2cd045f72dd6d&firstName=Atsuhiro&lastName=Kubo&_event_confirmForm=confirm';

        $this->assertEquals('http://example.org/authenticate.php?mycallback=%2Fadmin%2Ffoo.php%3F_flowExecutionTicket%3D15059b11c49f77a1830dca29a7a2cd045f72dd6d%26firstName%3DAtsuhiro%26lastName%3DKubo%26_event_confirmForm%3Dconfirm',
                            $this->_invokeInterceptor($configurations)
                            );

        unset($_SERVER['QUERY_STRING']);
    }

    function testResourceProtectionShouldWorkWhenUsingProxy()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/baz/admin/foo.php';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/baz');

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    function testResourceProtectionShouldWorkByResourcesMatch()
    {
        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/(foo|bar)\.php$')
                                );

        $this->_setIsAuthenticated(true);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));

        $this->tearDown();
        $this->setUp();

        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/(foo|bar)\.php$')
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));

        $this->tearDown();
        $this->setUp();

        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/(foo|bar)\.php$')
                                );

        $this->_setIsAuthenticated(true);
        $_SERVER['SCRIPT_NAME'] = '/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));

        $this->tearDown();
        $this->setUp();

        $configurations = array('name'      => 'Foo',
                                'guard'     => array('class' => 'Piece_Unity_Plugin_Interceptor_AuthenticationTestCase_Authentication', 'method' => 'isAuthenticated'),
                                'url'       => 'http://example.org/authenticate.php',
                                'resourcesMatch' => array('^/admin/(foo|bar)\.php$')
                                );

        $this->_setIsAuthenticated(false);
        $_SERVER['SCRIPT_NAME'] = '/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldBeAbleToAccessIfAuthenticatedByService()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $service = &new Piece_Unity_Service_Authentication('Foo');
        $service->login();

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldNotBeAbleToAccessIfNotAuthenticatedByService()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testProtectedResourceShouldNotBeAbleToAccessIfAuthenticatedOtherRealm()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $service = &new Piece_Unity_Service_Authentication('Bar');
        $service->login();

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldAlwaysBeAbleToAccessByService1()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $service = &new Piece_Unity_Service_Authentication('Foo');
        $service->login();

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldAlwaysBeAbleToAccessByService2()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $service = &new Piece_Unity_Service_Authentication('Bar');
        $service->login();

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testNonProtectedResourceShouldAlwaysBeAbleToAccessByService3()
    {
        $configurations = array('name'      => 'Foo',
                                'url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/foo.php';

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testDefaultAuthenticationRealmShouldBeUsedIfRealmIsNotGiven1()
    {
        $configurations = array('url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $service = &new Piece_Unity_Service_Authentication();
        $service->login();

        $this->assertEquals('Foo', $this->_invokeInterceptor($configurations));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testDefaultAuthenticationRealmShouldBeUsedIfRealmIsNotGiven2()
    {
        $configurations = array('url'       => 'http://example.org/authenticate.php',
                                'resources' => array('/admin/foo.php', '/admin/bar.php')
                                );
        $_SERVER['SCRIPT_NAME'] = '/admin/foo.php';
        $service = &new Piece_Unity_Service_Authentication('Bar');
        $service->login();

        $this->assertEquals('http://example.org/authenticate.php', $this->_invokeInterceptor($configurations));
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _invokeInterceptor($configurations)
    {
        $config = &new Piece_Unity_Config();

        foreach ($configurations as $key => $configuration) {
            $config->setConfiguration('Interceptor_Authentication',
                                      $key, $configuration
                                      );
        }
        $config->setConfiguration('Interceptor_Authentication', 'guardDirectory', dirname(__FILE__) . '/' . basename(__FILE__, '.php'));
        $context = &Piece_Unity_Context::singleton();
        $context->setView('Foo');
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();
        return $context->getView();
    }

    function _setIsAuthenticated($isAuthenticated)
    {
        $GLOBALS['PIECE_UNITY_Plugin_Interceptor_AuthenticationTestCase_isAuthenticated'] = $isAuthenticated;
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
