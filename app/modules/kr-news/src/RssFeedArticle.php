<?php

/**
 * Rss feed article class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class RssFeedArticle
{
    /**
     * Article data
     * @var Array
     */
    private $articleData = null;

    /**
     * Rss feed object
     * @var RssFeed
     */
    private $feed = null;

    /**
     * Rss Feed article constructor
     * @param RssFeed $feed        RssFeed object
     * @param Array $articleData   Article data
     */
    public function __construct($feed, $articleData)
    {
        $this->articleData = $articleData;
        $this->feed = $feed;
    }

    /**
     * Get article data by key
     * @param  String $key Key
     * @return String      Value associate to the key
     */
    public function _getArticleDataVal($key)
    {
        if (!array_key_exists($key, $this->articleData)) {
            return null;
        }

        // Return value
        return $this->articleData[$key];
    }

    /**
     * Get article data
     * @return Array Article data
     */
    public function _getData()
    {
        return $this->articleData;
    }

    /**
     * Get article picture
     * @return String Article picture
     */
    public function _getPicture()
    {
        return $this->_getArticleDataVal('thumbnail');
    }

    /**
     * Get article title
     * @return String Article title
     */
    public function _getTitle()
    {
        // Limit article title length
        if (strlen($this->_getArticleDataVal('title')) > 80) {
            return substr($this->_getArticleDataVal('title'), 0, 80).'...';
        }
        return $this->_getArticleDataVal('title');
    }

    /**
     * Get article url
     * @return String Article url
     */
    public function _getUrl()
    {
        return $this->_getArticleDataVal('link');
    }

    /**
     * Get article from
     * @return String Article from
     */
    public function _getFrom()
    {
        return $this->feed->_getFromTitle();
    }

    /**
     * Get article author
     * @return String Article author
     */
    public function _getAuthor()
    {
        return $this->_getArticleDataVal('author');
    }

    /**
     * Get article content
     * @return String Article content
     */
    public function _getContent()
    {
        $content = $this->_getArticleDataVal('content');

        // Remove article picture
        $content = preg_replace("/<img[^>]+\>/i", "", $content);

        // Update link for new bank
        $content = str_replace('<a', '<a target=_bank', $content);
        return $content;
    }

    /**
     * Get article date published
     * @return String Date published
     */
    public function _getDatePublish()
    {
        return $this->_getArticleDataVal('pubDate');
    }

    /**
     * Get timestamp date published
     * @return String Timestamp date published
     */
    public function _getTimestamp()
    {
        $DateTime = new DateTime($this->_getDatePublish());
        return $DateTime->getTimestamp();
    }

    /**
     * Get list tags associate
     * @return Array List tags
     */
    public function _getListTags()
    {
        return array_slice($this->_getArticleDataVal('categories'), 0, 3);
    }

    /**
     * Get publish article since
     * @param  Lang $Lang   Lang
     * @return String      Date article published since
     */
    public function _getPublishSince($Lang)
    {
        $now = new DateTime();
        $ago = new DateTime($this->_getDatePublish());
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => $Lang->tr('year'),
            'm' => $Lang->tr('month'),
            'w' => $Lang->tr('week'),
            'd' => $Lang->tr('day'),
            'h' => $Lang->tr('hour'),
            'i' => $Lang->tr('minute'),
            's' => $Lang->tr('second'),
        );

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string).' '.$Lang->tr('ago') : $Lang->tr('just now');
    }

    /**
     * Get list article uniq id
     * @return String Article uniq id
     */
    public function _getArticleUniq()
    {
        return md5($this->_getTitle().'-'.$this->_getUrl());
    }
}
