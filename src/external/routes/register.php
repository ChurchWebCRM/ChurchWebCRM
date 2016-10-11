<?php

use ChurchCRM\ConfigQuery;
use ChurchCRM\Family;
use ChurchCRM\ListOptionQuery;
use ChurchCRM\Person;
use Slim\Views\PhpRenderer;


$app->group('/register', function () {

  $enableSelfReg = ConfigQuery::create()->filterByName("sEnableSelfRegistration")->findOne();
  if ($enableSelfReg->getBooleanValue()) {

    $this->get('/', function ($request, $response, $args) {

      $renderer = new PhpRenderer("templates/registration/");

      return $renderer->render($response, "family-register.php", array("sRootPath" => $_SESSION['sRootPath']));

    });

    $this->post('/', function ($request, $response, $args) {
      $renderer = new PhpRenderer("templates/registration/");

      $body = $request->getParsedBody();

      $family = new Family();
      $family->setName($body["familyName"]);
      $family->setAddress1($body["familyAddress1"]);
      $family->setCity($body["familyCity"]);
      $family->setState($body["familyState"]);
      $family->setCountry($body["familyCountry"]);
      $family->setHomePhone($body["familyHomePhone"]);
      $family->setEnteredBy(1);
      $family->setDateEntered(new \DateTime());

      $className = "Regular Attender";
      if ($body["familyPrimaryChurch"] == "No") {
        $className = "Guest";
      }
      $familyMembership = ListOptionQuery::create()->filterById(1)->filterByOptionName($className)->findOne();

      $_SESSION["regFamily"] = $family;
      $_SESSION["regFamilyClass"] = $familyMembership;
      $_SESSION["regFamilyCount"] = $body["familyCount"];

      $familyRoles = ListOptionQuery::create()->filterById(2)->orderByOptionSequence()->find();

      $pageObjects = array("sRootPath" => $_SESSION['sRootPath'], "family" => $family, "familyCount" => $_SESSION['regFamilyCount'], "familyRoles" => $familyRoles);

      return $renderer->render($response, "family-register-members.php", $pageObjects);

    });
  }

  $this->post('/confirm', function ($request, $response, $args) {
    $renderer = new PhpRenderer("templates/registration/");

    $body = $request->getParsedBody();
    $family = $_SESSION["regFamily"];
    $familyCount = $_SESSION["regFamilyCount"];
    $familyMembers = array();
    for ($x = 1; $x <= $familyCount; $x++) {
      $person = new Person();
      $person->setFirstName($body["memberFirstName-" . $x]);
      $person->setLastName($body["memberLastName-" . $x]);
      $person->setGender($body["memberGender-" . $x]);
      $person->setEmail($body["memberEmail-" . $x]);

      $phoneType = $body["memberPhoneType-" . $x];
      if ($phoneType == "Mobile") {
        $person->setCellPhone($body["memberPhone-" . $x]);
      } else if ($phoneType == "Home") {
        $person->setHomePhone($body["memberPhone-" . $x]);
      } else if ($phoneType == "Work") {
        $person->setWorkPhone($body["memberPhone-" . $x]);
      }

      $birthday = $body["birthday-" . $x];
      if (!empty($birthday)) {
        $birthdayDate = \DateTime::createFromFormat('m/d/Y', $birthday);
        $person->setBirthDay($birthdayDate->format("d"));
        $person->setBirthMonth($birthdayDate->format('m'));
        $person->setBirthYear($birthdayDate->format('Y'));
      }
      $person->setEnteredBy(1);
      $person->setDateEntered(new \DateTime());

      $familyRole = $body["memberRole-" . $x];
      $person->setFmrId($familyRole);
      $family->addPerson($person);
      array_push($familyMembers, $person);
    }
    $_SESSION['familyMembers'] = $familyMembers;

    $pageObjects = array("sRootPath" => $_SESSION['sRootPath'], "family" => $family, "familyClass" => $_SESSION['regFamilyClass']);
    return $renderer->render($response, "family-register-confirm.php", $pageObjects);

  });

  $this->post('/done', function ($request, $response, $args) {
    $renderer = new PhpRenderer("templates/registration/");
    $family = $_SESSION["regFamily"];


    foreach($_SESSION['familyMembers'] as $person) {
      $person->setFamily($family);
      $person->save();
    }
    $family->save();

    $pageObjects = array("sRootPath" => $_SESSION['sRootPath']);
    return $renderer->render($response, "family-register-done.php", $pageObjects);
  });

});


