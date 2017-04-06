<?php
/**
 * Opauth basic configuration file to quickly get you started
 * ==========================================================
 * To use: rename to opauth.conf.php and tweak as you like
 * If you require advanced configuration options, refer to opauth.conf.php.advanced
 */

$host = ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'];

$config = array(
/**
 * Path where Opauth is accessed.
 *  - Begins and ends with /
 *  - eg. if Opauth is reached via http://example.org/auth/, path is '/auth/'
 *  - if Opauth is reached via http://auth.example.org/, path is '/'
 */
	'path' => '/index.php/',

/**
 * Callback URL: redirected to after authentication, successful or otherwise
 */
	'callback_url' => $host.'/index.php',
	
/**
 * A random string used for signing of $auth response.
 */
	'security_salt' => 'LHFm11lYf3Fyw5W10a44aa5x4W1KsVrieQCnpBzzpTBMA5vJidQKDo8pMJbmw22A1C8v',

  'debug' =>true,
	'Strategy' => array(
      'Google' => array(
          'client_id' => '708944901374-a3vscks9jcj8enfiqmaikjf2teg8fvqr.apps.googleusercontent.com',
          'client_secret' => 'oLw3_j5zLYQd2WbEvEJYKEZR'
      ),

		
	),
);