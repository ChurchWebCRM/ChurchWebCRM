<?php

use ChurchCRM\ConfigQuery;
use ChurchCRM\Family;
use ChurchCRM\ListOptionQuery;
use ChurchCRM\Person;
use Slim\Views\PhpRenderer;
use ChurchCRM\GroupQuery;
use ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\PersonQuery;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


$app->group('/kioskdevices', function () {

  $this->get('/{guid}', function ($request, $response, $args) {

      $renderer = new PhpRenderer("templates/kioskDevices/");
      $pageObjects = array("sRootPath" => $_SESSION['sRootPath'], "thisDeviceGuid" => $args['guid']);
      return $renderer->render($response, "sunday-school-class-view.php", $pageObjects);

    });
    
    $this->get('/{guid}/activeClassMembers', function ($request, $response, $args) {
     $ssClass = ChurchCRM\Person2group2roleP2g2rQuery::create()
            ->joinWithGroup()
            ->joinWithPerson()
            ->addJoin(ChurchCRM\Map\GroupTableMap::COL_GRP_ROLELISTID, ChurchCRM\Map\ListOptionTableMap::COL_LST_ID , Propel\Runtime\ActiveQuery\Criteria::INNER_JOIN)
            ->withColumn(ChurchCRM\Map\ListOptionTableMap::COL_LST_OPTIONNAME,"RoleName")
            ->findByGroupId(2);
      return $ssClass->toJSON();
    });
    
    $this->get('/{guid}/activeClassMember/{PersonId}/photo', function (ServerRequestInterface  $request, ResponseInterface  $response, $args) {
     $person = PersonQuery::create()->findPk($args['PersonId']);
        if ($person->isPhotoLocal()) {
            return $response->write($person->getPhotoBytes())->withHeader('Content-type', $person->getPhotoContentType());
        } else if ($person->isPhotoRemote()) {
            return $response->withRedirect($person->getPhotoURI());
        } else {
            return $response->withStatus(404);
        }
    });
  
});


