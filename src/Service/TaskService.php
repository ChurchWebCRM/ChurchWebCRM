<?php

namespace ChurchCRM\Service;

class TaskService
{
  private $baseURL;
  private $installedVersion;
  private $latestVersion;

  public function __construct()
  {
    $this->baseURL = $_SESSION['sRootPath'];
    $this->latestVersion =  $_SESSION['latestVersion'];
    $this->installedVersion = $_SESSION['sSoftwareInstalledVersion'];
  }

  function getAdminTasks() {
    requireUserGroupMembership("bAdmin");
    $sSQL = "SELECT cfg_name, IFNULL(cfg_value, cfg_default) AS value FROM config_cfg";
    $rsConfig = mysql_query($sSQL);			// Can't use RunQuery -- not defined yet
    if ($rsConfig) {
      while (list($cfg_name, $cfg_value) = mysql_fetch_row($rsConfig)) {
        $$cfg_name = $cfg_value;
      }
    }

    $tasks = array();
    if ($bRegistered != 1) {
      array_push($tasks, $this->addTask(gettext("Register Software"), $this->baseURL."/Register.php", true));
    }
    if ($sChurchName == "Some Church") {
      array_push($tasks, $this->addTask(gettext("Update Church Info"), $this->baseURL."/SystemSettings.php", true));
    }

    if ($sChurchAddress == "") {
      array_push($tasks, $this->addTask(gettext("Set Church Address"), $this->baseURL."/SystemSettings.php", true));
    }

    if ($sSMTPHost == "") {
      array_push($tasks, $this->addTask(gettext("Set Email Settings"), $this->baseURL."/SystemSettings.php", true));
    }

    if ($this->latestVersion != null && $this->latestVersion["name"] != $this->installedVersion) {
      array_push($tasks, $this->addTask(gettext("New Release") . " " . $this->latestVersion["name"], $this->latestVersion["html_url"], true));
    }

    return $tasks;
  }

  function getCurrentUserTasks() {
    $tasks = array();
    if ($_SESSION['bAdmin']) {
      $tasks = $this->getAdminTasks();
    }
    return $tasks;
  }

  function addTask($title, $link, $admin = false) {
    return  array("title" => $title, "link" => $link, "admin" => $admin);
  }

}
