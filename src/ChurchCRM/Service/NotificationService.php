<?php

namespace ChurchCRM\Service;

class NotificationService
{
  public static function updateNotifications()
  {
    /* Get the latest notifications from the source.  Store in session variable
     * 
     */
    $_SESSION['SystemNotifications'] = json_decode(file_get_contents("/vagrant/notifications/notifications.json"));
  }
  
  public static function getNotifications()
  {
    /* retreive active notifications from the session variable for display
     * 
     */
    $notifications = array();
    foreach ($_SESSION['SystemNotifications']->messages as $message)
    {
      if($message->targetVersion == $_SESSION['sSoftwareInstalledVersion'])
      {
        array_push($notifications, $message);
      }
    }
    return $notifications;
  }
  
  public static function testActiveNotifications()
  {
    foreach ($_SESSION['SystemNotifications']->messages as $message)
    {
      if($message->targetVersion == $_SESSION['sSoftwareInstalledVersion'])
      {
        return true;
      }
    }
    
  }
  
  public static function isUpdateRequired()
  {
    /*
     * If session does not contain notifications, or if the notification TTL has expired, return true
     * otherwise return false.
     */
    if (!isset($_SESSION['SystemNotifications']))
    {
      return true;
    }
    return false;
  }
  

}