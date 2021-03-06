<?php

if (!function_exists('curl_init')) {
  throw new Exception('Castle needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Castle needs the JSON PHP extension.');
}

if (!function_exists('lcfirst'))
{
  function lcfirst( $str ) {
    $str[0] = strtolower($str[0]);
    return (string)$str;
  }
}

require(dirname(__FILE__) . '/Castle/Castle.php');
require(dirname(__FILE__) . '/Castle/CookieStore.php');
require(dirname(__FILE__) . '/Castle/Errors.php');
require(dirname(__FILE__) . '/Castle/SessionToken.php');
require(dirname(__FILE__) . '/Castle/TokenStore.php');
require(dirname(__FILE__) . '/RestModel/Resource.php');
require(dirname(__FILE__) . '/RestModel/Model.php');
require(dirname(__FILE__) . '/Castle/Models/Account.php');
require(dirname(__FILE__) . '/Castle/Models/Context.php');
require(dirname(__FILE__) . '/Castle/Models/Event.php');
require(dirname(__FILE__) . '/Castle/Models/Label.php');
require(dirname(__FILE__) . '/Castle/Models/Authentication.php');
require(dirname(__FILE__) . '/Castle/Models/User.php');
require(dirname(__FILE__) . '/Castle/JWT.php');
require(dirname(__FILE__) . '/Castle/CurlTransport.php');
require(dirname(__FILE__) . '/Castle/Request.php');
