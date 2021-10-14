<?php

/**
 * Facebook Oauth Class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class FacebookOauth extends MySQL
{

    /**
     * User object
     * @var User
     */
    private $user = null;

    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Provider oauth
     * @var Object
     */
    private $provider = null;

    /**
     * OAuth token
     * @var String
     */
    private $token = null;

    /**
     * Ressource owner
     * @var Object
     */
    private $ressourceOwner = null;

    /**
     * FacebookOauth constructor
     * @param User $user User object
     * @param App $App   App object
     */
    public function __construct($user = null, $App = null)
    {

        if (is_null($App)) {
            $this->App = new App(true);
        } else {
            $this->App = $App;
        }

        if (is_null($user)) {
            throw new Exception("Error : Facebook Oauth require User instance", 1);
        }

        $this->user = $user;
    }

    /**
     * Get Oauth name
     * @return String Oauth name
     */
    public function _getOauthName()
    {
        return 'facebook';
    }

    /**
     * Get user object
     * @return User User object
     */
    public function _getUser()
    {
        if (is_null($this->user)) {
            throw new Exception("Error : Facebook Oauth, user is null", 1);
        }
        return $this->user;
    }

    /**
     * Get app object
     * @return App App object
     */
    public function _getApp()
    {
        if (is_null($this->App)) {
            $this->App = new App(true);
        }
        return $this->App;
    }

    /**
     * Get oauth provider
     * @return Object Facebook oauth provider
     */
    public function _getProvider()
    {
        // Check if provider is not already saved
        if (!is_null($this->provider)) {
            return $this->provider;
        }

        $this->provider = new Facebook\Facebook([
          'app_id' => $this->_getApp()->_getFacebookAppID(),
          'app_secret' => $this->_getApp()->_getFacebookAppSecret(),
          'default_graph_version' => 'v2.12'
        ]);
        return $this->_getProvider();
    }

    /**
     * Generate new authorization url
     * @return String Authorization url
     */
    public function _getAuthorizationUrl()
    {
        // Generate new authorization url
        $helper = $this->_getProvider()->getRedirectLoginHelper();
        $permissions = ['email', 'public_profile'];
        return $helper->getLoginUrl(APP_URL.'/app/modules/kr-facebookoauth/src/actions/callback.php', $permissions);
    }

    /**
     * Parse oauth callback
     * @param  Array $callback  Oauth callback
     */
    public function _parseCallback()
    {
        $this->_loadToken();
        return $this->_getUser()->_oauthCallbackID($this);
    }

    /**
     * Load token
     * @param  String $code Token code
     */
    private function _loadToken()
    {
      $helper = $this->_getProvider()->getRedirectLoginHelper();
      $this->token = $helper->getAccessToken();
    }

    /**
     * Get token
     * @return Object Token
     */
    private function _getToken()
    {
        return $this->token;
    }

    /**
     * Get ressource owner
     * @return Object Ressource owner
     */
    private function _getRessourceOwner()
    {
        if (!is_null($this->ressourceOwner)) {
            return $this->ressourceOwner;
        }
        $response = $this->_getProvider()->get('/me?fields=picture.height(400),email,name', $this->_getToken());
        $this->ressourceOwner = $response->getGraphUser();
        return $this->ressourceOwner;
    }

    /**
     * Get name
     * @return String User name
     */
    public function _getName()
    {
        return $this->_getRessourceOwner()->getName();
    }

    /**
     * Get user email
     * @return String User email
     */
    public function _getEmail()
    {
        return $this->_getRessourceOwner()->getEmail();
    }

    /**
     * Get user avatar
     * @return String User avatar path
     */
    public function _getAvatar()
    {
        return $this->_getRessourceOwner()->getPicture()->getUrl();
    }

    /**
     * Get user id
     * @return Int
     */
    public function _getId(){
      return $this->_getRessourceOwner()->getId();
    }
}
