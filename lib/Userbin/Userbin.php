<?php

abstract class Userbin
{
  public static $apiKey;

  public static $apiBase = 'https://secure.userbin.com';

  public static $apiVersion = 'v1';

  public static $sessionStore = 'Userbin_SessionStore';

  const VERSION = '1.0.1';

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function getApiVersion()
  {
    return self::$apiVersion;
  }

  public static function setApiVersion($apiVersion)
  {
    self::$apiVersion = $apiVersion;
  }

  public static function getSessionStore()
  {
    return new self::$sessionStore;
  }

  public static function setSessionStore($serializerClass)
  {
    self::$sessionStore = $serializerClass;
  }

  public static function getSession()
  {
    $sessionData = self::getSessionStore()->read();
    if ($sessionData) {
      return new Userbin_SessionToken($sessionData);
    }
    return null;
  }

  /**
   * Authorize connects you local user to Userbin and starts monitoring
   * the login session. If the user does not exist it will be created before
   * the monitoring session is started. This method should be called whenever
   * the user logs in or is loaded from the database. Note that every call to
   * this method does not result in a HTTP request. Only when there is no prior
   * session or if the session has expired.
   *
   * @param  $userId          The local id of your currently logged in  user
   * @param  array  $userData An array of additional user data (for display purposes). At least email is recommended.
   * @return Userbin_User     The user object returned from Userbin
   */
  public static function authorize($userId, array $userData=null)
  {
    $session = self::getSession();

    if (empty($session)) {
      $user = new Userbin_User($userData);
      $user->setId($userId);
      if (is_array($userData)) {
        $userData = array('user' => $userData);
      }
      $newSession = $user->sessions()->create($userData);
      $session = new Userbin_SessionToken($newSession->token);
      self::getSessionStore()->write($session->serialize());
    }
    else {
      if ($session->getUser()->getId() != $userId) {
        self::logout();
        throw new Userbin_Error('Session scopes not supported yet');
      }
      if ($session->hasExpired()) {
        $request = new Userbin_Request();
        $request->send('post', '/heartbeat');
      }
    }

    return $session->getUser();
  }

  /**
   * If a two-factor authentication process has been started, this method will
   * return the method which is used to perform the authentication. Eg.
   * "authenticator" or "sms"
   * @return string Two-factor authentication method
   */
  public static function getTwoFactorMethod()
  {
    $session = self::getSession();
    if (empty($session)) {
      return false;
    }
    $challenge = $session->getChallenge();
    if (empty($challenge)) {
      return false;
    }
    return $challenge->channel['type'];
  }

  /**
   * This method ends the current monitoring session. It should be called
   * whenever the user logs out from your system.
   *
   * @return none
   */
  public static function logout()
  {
    $session = self::getSession();
    if (isset($session)) {
      self::getSessionStore()->destroy();
      $sess = new Userbin_Session($session->getId());
      $sess->delete();
    }
  }

  /**
   * This method creates a two factor challenge for the current user, if the
   * user has enabled a device for authentication.
   *
   * If there already exists a challenge on the current session, it will be
   * returned. Otherwise a new will be created.
   *
   * @param  forceChallenge    Will force generate a new challenge instead of
   * returning any existing
   * @return Userbin_Challenge The challenge object. It will tell you what type
   * factor the user is using to authenticate (SMS, Google Authenticator etc.).
   */
  public static function twoFactorAuthenticate($force = false)
  {
    $session = self::getSession();
    if (empty($session)) {
      return false;
    }
    if (!$session->needsChallenge()) {
      return false;
    }

    $challenge = $session->getChallenge();
    if (isset($challenge)) {
      if (!$force) {
        return $challenge;
      }
      try {
        $challenge->delete();
      }
      catch (Exception $e) {}
    }
    $challenge = $session->getUser()->challenges()->create();
    return $challenge;
  }

  /**
   * Once a two factor challenge has been created using the twoFactorAuthenticate,
   * the response code from the user is verified using this method.
   *
   * @param  $response A string containing the response from the user
   * @return bool      True if the verification was successful. False otherwise.
   */
  public static function twoFactorVerify($response)
  {
    $session = self::getSession();
    if (empty($session)) {
      return false;
    }
    $challenge = $session->getChallenge();
    if (empty($challenge)) {
      return false;
    }
    return $challenge->verify($response);
  }

  /**
   * This method will generate a link to the hosted Userbin security settings
   * page. On this page the user can see active sessions and enable two step
   * verification.
   *
   * @return string The URL to the security settings page.
   */
  public static function securitySettingsUrl()
  {
    $session = self::getSessionStore()->read();

    if (empty($session)) {
      throw new Userbin_Error();
    }
    return 'https://security.userbin.com/?session_token='.$session;
  }
}
