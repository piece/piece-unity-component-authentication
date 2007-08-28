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
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.0.0
 */

require_once realpath(dirname(__FILE__) . '/../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Service/Authentication.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Plugin/View.php';

// {{{ Piece_Unity_Service_AuthenticationTestCase

/**
 * TestCase for Piece_Unity_Service_Authentication
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Component_Flexy
 * @copyright  2006-2007 KUMAKURA Yousuke <kumatch@users.sourceforge.net>
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

    /**#@-*/

    /**#@+
     * @access public
     */

    function tearDown()
    {
        Piece_Unity_Context::clear();
    }

    function testLogin()
    {
        $serviceName = 'ExampleServiceForTestLogin';

        $authentication = &new Piece_Unity_Service_Authentication($serviceName);
        $authentication->login();
        
        $authenticationSessions = &$this->_getAuthenticationSessions();

        $this->assertTrue(array_key_exists($serviceName,
                                           $authenticationSessions));
        $this->assertTrue($authenticationSessions[$serviceName]);

        $this->tearDown();

        $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];
        $authentication = &new Piece_Unity_Service_Authentication();
        $authentication->login();

        $authenticationSessions = &$this->_getAuthenticationSessions();

        $this->assertTrue(array_key_exists($serviceName,
                                           $authenticationSessions));
        $this->assertTrue($authenticationSessions[$serviceName]);
    }

    function testLogout()
    {
        $serviceName = 'ExampleServiceForTestLogout';

        $authentication = &new Piece_Unity_Service_Authentication($serviceName);
        $authentication->logout();
        
        $authenticationSessions = &$this->_getAuthenticationSessions();

        $this->assertTrue(array_key_exists($serviceName,
                                           $authenticationSessions));
        $this->assertFalse($authenticationSessions[$serviceName]);

        $this->tearDown();

        $authentication = &new Piece_Unity_Service_Authentication();
        $authentication->logout();
        
        $authenticationSessions = &$this->_getAuthenticationSessions();

        $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];
        $this->assertTrue(array_key_exists($serviceName,
                                           $authenticationSessions));
        $this->assertFalse($authenticationSessions[$serviceName]);
    }

    function testAuthenticationSuccess()
    {
        $serviceName = 'ExampleServiceForTestAuthenticationSuccess';

        $authenticationSessions = &$this->_getAuthenticationSessions();
        $authenticationSessions[$serviceName] = true;

        $authentication = &new Piece_Unity_Service_Authentication($serviceName);

        $this->assertTrue($authentication->isAuthenticated());

        $this->tearDown();

        $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];
        $authenticationSessions = &$this->_getAuthenticationSessions();
        $authenticationSessions[$serviceName] = true;

        $authentication = &new Piece_Unity_Service_Authentication();

        $this->assertTrue($authentication->isAuthenticated());
    }

    function testAuthenticationFailureOne()
    {
        $serviceName = 'ExampleServiceForTestAuthenticationFailureOne';

        $authentication = &new Piece_Unity_Service_Authentication($serviceName);

        $this->assertFalse($authentication->isAuthenticated());

        $this->tearDown();

        $authentication = &new Piece_Unity_Service_Authentication();

        $this->assertFalse($authentication->isAuthenticated());
    }

    function testAuthenticationFailureTwo()
    {
        $serviceName = 'ExampleServiceForTestAuthenticationFailureTwo';

        $authenticationSessions = &$this->_getAuthenticationSessions();
        $authenticationSessions[$serviceName] = false;

        $authentication = &new Piece_Unity_Service_Authentication($serviceName);

        $this->assertFalse($authentication->isAuthenticated());

        $this->tearDown();

        $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];
        $authenticationSessions = &$this->_getAuthenticationSessions();
        $authenticationSessions[$serviceName] = false;

        $authentication = &new Piece_Unity_Service_Authentication();

        $this->assertFalse($authentication->isAuthenticated());
    }

    function testCatchTheCallbackParameter()
    {
        $callback = 'ExampleCallbackParameter';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['callback'] = $callback;

        $authentication = &new Piece_Unity_Service_Authentication();
        $authentication->catchCallback();

        $this->assertEquals($callback, $authentication->getCallback());

        $this->tearDown();
        unset($_GET['callback']);

        $callback = 'ExampleCallbackParameter2';
        $serviceName = $GLOBALS['PIECE_UNITY_SERVICE_AUTHENTICATION_DEFAULT_SERVICE_NAME'];

        $callbacks = &$this->_getCallbackSessions();
        $callbacks[$serviceName] = $callback;

        $authentication = &new Piece_Unity_Service_Authentication();
        $authentication->catchCallback();

        $this->assertEquals($callback, $authentication->getCallback());

        $authentication->removeCallback();
        
        $this->assertNull($authentication->getCallback());
        $this->assertNull($callbacks[$serviceName]);

        $authentication->catchCallback();

        $this->assertNull($authentication->getCallback());
        $this->assertNull($callbacks[$serviceName]);
    }

    function testForwardingByCallbackParameter()
    {
        $callback = '/path/to/example.html';

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['callback'] = $callback;

        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $authentication = &new Piece_Unity_Service_Authentication();
        $authentication->catchCallback();

        $this->assertEquals($callback, $authentication->getCallback());

        $authentication->forwardByCallback();

        $view = &new Piece_Unity_Plugin_View();
        $view->invoke();

        $this->assertEquals('http://example.org/path/to/example.html',
                            $context->getView()
                            );
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function &_getAuthenticationSessions()
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();

        $authenticationSessions =
            &$session->getAttribute('_Piece_Unity_Service_Authentication_Services');

        return $authenticationSessions;
    }

    function &_getCallbackSessions()
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();

        $callbacks =
            &$session->getAttribute('_Piece_Unity_Service_Authentication_Callbacks');

        return $callbacks;
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
