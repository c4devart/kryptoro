<?php

/**
 * NotificationCenter class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class NotificationCenter extends MySQL
{

    /**
     * User object
     * @var User
     */
    private $user = null;

    /**
     * NotificationCenter constructor
     * @param User $user User object
     */
    public function __construct($user = null)
    {
        if (is_null($user)) {
            throw new Exception("Error : Notification center, user need to be given", 1);
        }
        $this->user = $user;
    }

    /**
     * Get user object
     * @return User User associate to notification center
     */
    private function _getUser()
    {
        if (is_null($this->user)) {
            throw new Exception("Error : User is null in notification center", 1);
        }
        return $this->user;
    }

    /**
     * Get list notification
     * @param  Int $limit      Number notification limit
     * @param  Boolean $onlyns Show only not seen notification
     */
    public function _getListNotification($limit = 30, $onlyns = false)
    {
        $notificationList = [];

        // Fetch notification list
        if ($onlyns) { // Only not seen notification
            $notificationSql = parent::querySqlRequest("SELECT * FROM notification_center_krypto WHERE id_user=:id_user AND status_notification_center=0 ORDER BY id_notification_center DESC", [
                                                            'id_user' => $this->_getUser()->_getUserID()]);
        } else { // All notification
            $notificationSql = parent::querySqlRequest("SELECT * FROM notification_center_krypto WHERE id_user=:id_user ORDER BY id_notification_center DESC", [
                                                            'id_user' => $this->_getUser()->_getUserID()]);
        }

        // Add to return & create notification object
        foreach ($notificationSql as $key => $notificationData) {
            $notificationList[] = new Notification($notificationData['id_notification_center']);
        }
        return array_slice($notificationList, 0, $limit);
    }

    public function _getNumberNotificationUnseen(){
      $notificationSql = parent::querySqlRequest("SELECT * FROM notification_center_krypto WHERE id_user=:id_user AND status_notification_center=0 ORDER BY id_notification_center DESC", [
                                                      'id_user' => $this->_getUser()->_getUserID()]);
      return count($notificationSql);
    }

    /**
     * Send notification
     * @param  String  $title    Notification title
     * @param  String  $content  Notification content
     * @param  String  $icon     Notification icon path
     * @param  Boolean $sendpush Send push notification (with pushbullet)
     */
    public function _sendNotification($title, $content, $icon = '', $sendpush = true, $action = null)
    {

        // Add sql notification
        $r = parent::execSqlRequest("INSERT INTO notification_center_krypto (title_notification_center, text_notification_center, date_notification_center, icon_notification_center, id_user, action_notification_center, status_notification_center)
                                        VALUES (:title_notification_center, :text_notification_center, :date_notification_center, :icon_notification_center, :id_user, :action_notification_center, :status_notification_center)",
                                        [
                                          'title_notification_center' => $title,
                                          'text_notification_center' => $content,
                                          'date_notification_center' => time(),
                                          'icon_notification_center' => $icon,
                                          'id_user' => $this->_getUser()->_getUserID(),
                                          'action_notification_center' => '',
                                          'status_notification_center' => 0
                                        ]);

        // Check insert status
        if (!$r) {
            throw new Exception("Error : Fail to send new notification in notification center", 1);
        }

        if ($sendpush) { // If send notification
            // Send pushbullet notification
            $this->_sendPushbulletNotification($title, $content);
        }

        return true;
    }

    /**
     * Set all notification seend
     */
    public function _setAllSeen()
    {
        // Change all notification status
        $r = parent::execSqlRequest("UPDATE notification_center_krypto SET status_notification_center=1 WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);

        // Check notifcation change status
        if (!$r) {
            throw new Exception("Error SQL : Fail to change notifications status", 1);
        }
        return true;
    }

    /**
     * Send pushbullet notification
     * @param  String $title   pushbullet notification title
     * @param  String $content pushbullet notification content
     * @return [type]          [description]
     */
    public function _sendPushbulletNotification($title, $content)
    {
        // Notification data
        $data = json_encode(array(
            'type' => 'note',
            'title' => $title,
            'body' => $content
        ));

        if(is_null($this->_getUser()->_getPushbulletKey())) return true;

        if(!function_exists('curl_version')){
          error_log('Fail to send push notification : CURL extension not available');
        }

        // Init API pushbullet
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.pushbullet.com/v2/pushes');
        curl_setopt($curl, CURLOPT_USERPWD, $this->_getUser()->_getPushbulletKey()); // Set user pushbullet key
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($data)]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = json_decode(curl_exec($curl), true);
        curl_close($curl);

        // Check API Response message
        if (array_key_exists('error', $result)) {
            throw new Exception("Error : ".$result['error']['message'], 1);
        }

        return true;
    }
}
