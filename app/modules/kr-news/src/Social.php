<?php

/**
 * Social class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Social extends MySQL
{

    /**
     * Social type
     * @var String
     */
    private $type = null;

    /**
     * Social constructopr
     * @param String $type Type social
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get list social feed list
     * @return Array Array social feed
     */
    public function _getRssFeedList()
    {
        $listFeedsSQL = parent::querySqlRequest("SELECT * FROM social_krypto WHERE type_social=:type_social", ['type_social' => $this->_getTypeSocial()]);

        $listFeeds = [];
        foreach ($listFeedsSQL as $keySocial => $socialData) {
            $listFeeds[$socialData['id_social']] = new RssFeed($this->_getRssData($socialData));
        }
        return $listFeeds;
    }

    /**
     * Check if social feed already exist
     * @param  String $url social feed url
     * @return Boolean
     */
    private function _checkFeedExist($name)
    {
        return count(parent::querySqlRequest("SELECT * FROM social_krypto WHERE user_social=:user_social", ['user_social' => $name])) > 0;
    }

    /**
     * Add new social feed
     * @param String $url social feed url
     */
    public function _addFeed($name)
    {
        // Check social feed exist
        if ($this->_checkFeedExist($name)) {
            throw new Exception("Social account already exist", 1);
        }

        // Add social feed to database
        $r = parent::execSqlRequest("INSERT INTO social_krypto (type_social, user_social) VALUES (:type_social, :user_social)",
                                  [
                                    'type_social' => 'twitter',
                                    'user_social' => $name
                                  ]);

        // Check insert request status
        if (!$r) {
            throw new Exception("Error SQL : Fail to add social account in database", 1);
        }
        return true;
    }

    public function _removeFeed($feedid){

      $r = parent::execSqlRequest("DELETE FROM social_krypto WHERE id_social=:id_social",
                                  [
                                    'id_social' => $feedid
                                  ]);

      if(!$r) throw new Exception("Error SQL : Fail to delete social feed", 1);


    }

    /**
     * Get social type
     * @return String type social
     */
    public function _getTypeSocial()
    {
        return $this->type;
    }

    /**
     * Get social feed url
     * @param  String $user Social user
     * @return String       Social feed url
     */
    public function _getRssUrl($user)
    {
        if ($this->_getTypeSocial() == "twitter") {
            return 'http://twitrss.me/twitter_user_to_rss/?user='.$user;
        }
    }

    /**
     * Get social feed data
     * @param  Array $data  Social feed data
     * @return Array        Social data
     */
    public function _getRssData($data)
    {
        return [
          'url_rssfeed' => $this->_getRssURL($data['user_social'])
        ];
    }

    /**
     * Format social user
     * @param  String $user Unformated user
     * @return String       Formated user
     */
    public function _formatUserName($user)
    {
        return str_replace(['(', ')', '@', '_', '-'], ['', '', '', '', ''], $user);
    }

    /**
     * Format account social user name
     * @param  String $user User social name
     * @return String       Formated user social name
     */
    public function _formatAccountUserName($user)
    {
        return str_replace(['(', ')'], ['', ''], $user);
    }

    /**
     * Get user social picture
     * @param  String $user User social name
     * @return String       User social picture path
     */
    public function _getUserPicture($user)
    {
        if ($this->_getTypeSocial() == "twitter") {
            return 'https://twitter.com/'.$user.'/profile_image?size=mini';
        }
    }

    /**
     * Get list social feed
     * @return Array   RssFeedArticle array social
     */
    public function _getListFeedRSS()
    {
        $article = [];

        // Get list social feed
        foreach ($this->_getRssFeedList() as $feed) {
            // Get list social article
            foreach ($feed->_getFeedList() as $feedData) {
                // Create RssFeedArticle
                $article[] = new RssFeedArticle($feed, $feedData);
            }
        }

        // Sort feed list by date
        usort($article, function ($a, $b) {
            if ($a->_getTimestamp() < $b->_getTimestamp()) {
                return 1;
            } else {
                return -1;
            }
        });
        return $article;
    }
}
