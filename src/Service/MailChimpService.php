<?php

namespace ChurchCRM\Service;
use ChurchCRM\dto\SystemConfig;

class MailChimpService
{

  private $isActive = false;
  private $myMailchimp;

  public function __construct()
  {

   if (SystemConfig::getValue("mailChimpApiKey") != "") {
      $this->isActive = true;
      $this->myMailchimp = new \Mailchimp(SystemConfig::getValue("mailChimpApiKey"));
    }
  }

  function isActive()
  {
    return $this->isActive;
  }

  function isEmailInMailChimp($email)
  {

    if (!$this->isActive) {
      return "Mailchimp is not active";
    }

    if ($email == "") {
      return "No email";
    }

    try {
      $lists = $this->myMailchimp->helper->listsForEmail(array("email" => $email));
      $listNames = array();
      foreach ($lists as $val) {
        array_push($listNames, $val["name"]);
      }
      return implode(",", $listNames);
    } catch (\Mailchimp_Invalid_ApiKey $e) {
      return "Invalid ApiKey";
    } catch (\Mailchimp_List_NotSubscribed $e) {
      return "";
    } catch (\Mailchimp_Email_NotExists $e) {
      return "";
    } catch (\Exception $e) {
      return $e;
    }

  }

  function getLists()
  {
    if (!$this->isActive) {
      return "Mailchimp is not active";
    }
    try {
      $result = $this->myMailchimp->lists->getList();
      return $result["data"];
    } catch (\Mailchimp_Invalid_ApiKey $e) {
      return "Invalid ApiKey";
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }

}
