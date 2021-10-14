<?php
/**
 * News class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class News extends MySQL
{

    /**
     * News constructor
     */
    public function __construct() { }

    /**
     * Get list Rss feed available
     * @return Array   Rss Feed Array
     */
    public function _getRssFeedList()
    {

        // Fetch rss feed list from Database
        $listFeedsSQL = parent::querySqlRequest("SELECT * FROM rssfeed_krypto", []);

        $listFeeds = [];
        foreach ($listFeedsSQL as $keyFeed => $dataFeed) {
            // Create RssFeed object
            $listFeeds[$dataFeed['id_rssfeed']] = new RssFeed($dataFeed);
        }
        return $listFeeds;
    }

    /**
     * Check if rss feed already exist
     * @param  String $url Rss feed url
     * @return Boolean
     */
    private function _checkFeedExist($url)
    {
        return count(parent::querySqlRequest("SELECT * FROM rssfeed_krypto WHERE url_rssfeed=:url_rssfeed", ['url_rssfeed' => $url])) > 0;
    }

    /**
     * Add new rss feed
     * @param String $url Rss feed url
     */
    public function _addFeed($url)
    {

        // Parse rss feed url
        $parseURL = parse_url($url);
        if (count($parseURL) == 0 || !array_key_exists('host', $parseURL)) {
            throw new Exception("Error : Fail to parse URL", 1);
        }

        // Check rss feed already exist
        if ($this->_checkFeedExist($url)) {
            throw new Exception("RSS Feed already installed", 1);
        }

        // Add rss feed to database
        $r = parent::execSqlRequest("INSERT INTO rssfeed_krypto (name_rssfeed, url_rssfeed, date_rssfeed) VALUES
                                        (:name_rssfeed, :url_rssfeed, :date_rssfeed)",
                                        [
                                          'name_rssfeed' => $parseURL['host'],
                                          'url_rssfeed' => $url,
                                          'date_rssfeed' => time()
                                        ]);

        // Check insert request status
        if (!$r) {
            throw new Exception("Error SQL : Fail to add RSS in database", 1);
        }
        return true;
    }

    /**
     * Delete RSS Feed
     * @param  Int $feedid   Feed ID
     */
    public function _removeFeed($feedid){

      $r = parent::execSqlRequest("DELETE FROM rssfeed_krypto WHERE id_rssfeed=:id_rssfeed",
                                  [
                                    'id_rssfeed' => $feedid
                                  ]);

      if(!$r) throw new Exception("Error SQL : Fail to delete news feed", 1);

    }

    /**
     * Get list rss feed article
     * @return Array RssFeed Article array
     */
    public function _getListFeedRSS()
    {
        $article = [];
        // Get list rss feed list
        foreach ($this->_getRssFeedList() as $feed) {
            // Fetch all article for feed
            foreach ($feed->_getFeedList() as $feedData) {
                // Create rss feed article
                $article[] = new RssFeedArticle($feed, $feedData);
            }
        }

        // Sort article by date
        usort($article, function ($a, $b) {
            if ($a->_getTimestamp() < $b->_getTimestamp()) {
                return 1;
            } else {
                return -1;
            }
        });

        return $article;
    }

    // Get rss feed article
    public function _getArticle($uniq)
    {
        // Get list rss feed
        $listArticle = $this->_getListFeedRSS();
        // List all rss feed article
        foreach ($listArticle as $article) {
            // Check if uniq id article
            if ($article->_getArticleUniq() == $uniq) {
                return $article;
            }
        }
        return null;
    }
}
