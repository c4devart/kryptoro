<?php

/**
 * Notification class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Notification extends MySQL
{

    /**
     * Notification ID
     * @var Int
     */
    private $notificationid = null;

    /**
     * Notification Data
     * @var Array
     */
    private $notificationdata = null;

    /**
     * Notification constructor
     * @param Int $notificationid Notification ID
     */
    public function __construct($notificationid = null)
    {
        if (is_null($notificationid)) {
            throw new Exception("Error : Notification ID is missing", 1);
        }

        // Load notification data
        $this->notificationid = $notificationid;
        $this->_loadNotificationData();
    }

    /**
     * Get notification ID
     * @return Int Notification ID
     */
    public function _getNotificationID()
    {
        if (is_null($this->notificationid)) {
            throw new Exception("Error : Notification ID is null", 1);
        }
        return $this->notificationid;
    }

    /**
     * Get notification data by key
     * @param  String $key Data key
     * @return Stirng      Data associate to the key
     */
    public function _getNotificationDataValue($key)
    {
        if (is_null($this->notificationdata)) {
            throw new Exception("Error : Notification data not loaded (".$this->_getNotificationID().")", 1);
        }
        if (!array_key_exists($key, $this->notificationdata)) {
            throw new Exception("Error : Key not found in notification data (key = ".$key.")", 1);
        }
        return $this->notificationdata[$key];
    }

    /**
     * Load notification data
     */
    private function _loadNotificationData()
    {
        // Fetch SQL notification data
        $this->notificationdata = parent::querySqlRequest("SELECT * FROM notification_center_krypto WHERE id_notification_center=:id_notification_center",
                                                      [
                                                        'id_notification_center' => $this->_getNotificationID()
                                                      ]);
        if (count($this->notificationdata) == 0) {
            throw new Exception("Error : Unable to load notification data (".$this->_getNotificationID().")", 1);
        }

        $this->notificationdata = $this->notificationdata[0];
        return true;
    }

    /**
     * Get notification title
     * @return String Notification title
     */
    public function _getTitle()
    {
        return $this->_getNotificationDataValue('title_notification_center');
    }

    /**
     * Get notification body text
     * @return String Notification text
     */
    public function _getBody()
    {
        return $this->_getNotificationDataValue('text_notification_center');
    }

    /**
     * Get notification icon
     * @return String notification icon path
     */
    public function _getIcon()
    {
        return $this->_getNotificationDataValue('icon_notification_center');
    }

    /**
     * Get notification status
     * @return String notification status code
     */
    public function _getStatus()
    {
        return $this->_getNotificationDataValue('status_notification_center');
    }

    /**
     * Get notification date
     * @return String Notification timestamp date
     */
    public function _getDate()
    {
        return $this->_getNotificationDataValue('date_notification_center');
    }

    public function _getAction()
    {
        return $this->_getNotificationDataValue('action_notification_center');
    }

    /**
     * Get notifiction sended since
     * @param  Lang $Lang   Lang object
     * @return String       Notification send since
     */
    public function _getSince($Lang)
    {
        // Create date requirement
        $now = new DateTime();
        $ago = new DateTime();
        $ago->setTimestamp($this->_getDate());

        // Create diff
        $diff = $now->diff($ago);
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        // Get list sub date
        $string = array('y' => $Lang->tr('year'),
          'm' => $Lang->tr('month'),
          'w' => $Lang->tr('week'),
          'd' => $Lang->tr('day'),
          'h' => $Lang->tr('hour'),
          'i' => $Lang->tr('minute'),
          's' => $Lang->tr('second'));

        // Compose since string
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        // Format since string
        $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) .' '.$Lang->tr('ago') : $Lang->tr('just now');
    }

    /**
     * Get notification object
     * @param  Lang $Lang   Lang object
     * @return Array        Notification data
     */
    public function _getNotification($Lang = null)
    {
        return [
          "title" => $this->_getTitle(),
          "body" => $this->_getBody(),
          "icon" => $this->_getIcon(),
          "since" => $this->_getSince($Lang),
          "status" => $this->_getStatus(),
          'action' => $this->_getAction()
        ];
    }

    /**
     * Define notification as seen
     */
    public function _setSeen()
    {

        // Check if notification is not already seen
        if ($this->_getStatus() == 1) {
            return true;
        }

        // Change SQL notifiction status
        $r = parent::execSqlRequest("UPDATE notification_center_krypto SET status_notification_center=:status_notification_center WHERE id_notification_center=:id_notification_center",
                                [
                                  'status_notification_center' => 1,
                                  'id_notification_center' => $this->_getNotificationID()
                                ]);

        // Check update status
        if (!$r) {
            throw new Exception("Error : Fail to change status as seen for notification = ".$this->_getNotificationID(), 1);
        }
        return true;
    }
}
