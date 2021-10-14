<?php

/**
 * Rss feed class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class RssFeed
{

    /**
     * Rss feed url
     * @var String
     */
    private $rssSQL = null;

    /**
     * Rss feed data
     * @var Array
     */
    private $feedData = null;

    /**
     * RssFeed constructor
     * @param Array $rssdata Rss feed data
     */
    public function __construct($rssdata = null)
    {
        if (!is_null($rssdata)) {
            $this->rssSQL = $rssdata;

            // Load rss feed
            $this->_loadRssFeed();
        }
    }

    /**
     * Get RssFeed url
     * @return String RssFeed url
     */
    public function _getUrl()
    {
        if (is_null($this->rssSQL)) {
            throw new Exception("Error : Data is null for rss feed", 1);
        }
        return $this->rssSQL['url_rssfeed'];
    }

    /**
     * Load rss feed
     * @return [type] [description]
     */
    public function _loadRssFeed()
    {
        // Get rss feed json data & parse
        $dataRssJSON = json_decode(file_get_contents('https://api.rss2json.com/v1/api.json?rss_url='.urlencode($this->_getUrl()).'&api_key=gv8lphuigsa1voxctrcugwwqwdfh8f2gv4fluhdp'), true);

        // Check rss feed result
        if ($dataRssJSON['status'] == "ok") {
            $this->feedData = $dataRssJSON;
        } else {
            error_log('Fail to parse rss feed : '.$this->_getUrl());
        }
    }

    /**
     * Get RssFeed title
     * @return String RssFeed title
     */
    public function _getFromTitle()
    {
        return $this->feedData['feed']['title'];
    }

    /**
     * Get feed list
     * @return Array Feed list
     */
    public function _getFeedList()
    {
        return $this->feedData['items'];
    }
}
