<?php

/* * *****************************************************************************
 *
 *  filename    : events.php
 *  last change : 2017-11-16
 *  description : manage the full calendar with events
 *
 *  http://www.churchcrm.io/
 *  Copyright 2017 Logel Philippe
 *
 * **************************************************************************** */

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\Base\EventQuery;
use ChurchCRM\Base\EventTypeQuery;
use ChurchCRM\Event;
use ChurchCRM\EventCountsQuery;
use ChurchCRM\EventCounts;
use ChurchCRM\Service\CalendarService;
use ChurchCRM\dto\MenuEventsCount;
use ChurchCRM\Utils\InputUtils;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/events', function () {

  $this->get('/', 'getAllEvents');
  $this->get('', 'getAllEvents');
  $this->get("/types","getEventTypes");  
  $this->get('/{id}/primarycontact', 'getEventPrimaryContact');
  $this->get('/{id}/secondarycontact', 'getEventSecondaryContact');
  $this->get('/{id}/location', 'getEventLocation');
  $this->get('/{id}/audience', 'getEventAudience');
  $this->post('/{id}/time', 'setEventTime');
  $this->post('/', 'newOrUpdateEvent');
});

function getEventTypes($request, Response $response, $args) {
  $EventTypes = EventTypeQuery::Create()
          ->orderByName()
          ->find();
  if ($EventTypes) {
    return $response->write($EventTypes->toJSON());
  }
  return $response->withStatus(404);
}

function getAllEvents($request, Response $response, $args) {
  $Events = EventQuery::create()
          ->find();
  if ($Events) {
    return $response->write($Events->toJSON());
  }
  return $response->withStatus(404);
}



function getEventPrimaryContact($request, $response, $args) {
  $Event = EventQuery::create()
          ->findOneById($args['id']);
  if ($Event) {
    $Contact = $Event->getPersonRelatedByPrimaryContactPersonId();
    if($Contact) { 
      return $response->write($Contact->toJSON());
    }
  }
  return $response->withStatus(404);
}

function getEventSecondaryContact($request, $response, $args) {
  $Contact = EventQuery::create()
          ->findOneById($args['id'])
          ->getPersonRelatedBySecondaryContactPersonId();
  if ($Contact) {
    return $response->write($Contact->toJSON());
  }
  return $response->withStatus(404);
}

function getEventLocation($request, $response, $args) {
  $Location = EventQuery::create()
          ->findOneById($args['id'])
          ->getLocation();
  if ($Location) {
    return $response->write($Location->toJSON());
  }
  return $response->withStatus(404);
}

function getEventAudience($request, $response, $args) {
  $Audience = EventQuery::create()
          ->findOneById($args['id'])
          ->getEventAudiencesJoinGroup();
  if ($Audience) {
    return $response->write($Audience->toJSON());
  }
  return $response->withStatus(404);
}

function setEventTime ($request, Response $response, $args) {
  $input = (object) $request->getParsedBody();

  $event = EventQuery::Create()
    ->findOneById($args['id']);
  if(!$event) {
    return $response->withStatus(404);
  }
  $event->setStart($input->startTime);
  $event->setEnd($input->endTime);
  $event->save();
  return $response->withJson(array("status"=>"success"));
  
}

function newOrUpdateEvent($request, $response, $args) {
  $input = (object) $request->getParsedBody();

  if (!strcmp($input->evntAction, 'createEvent')) {
    $eventTypeName = "";

    $EventGroupType = $input->EventGroupType; // for futur dev : personal or group

    if ($input->eventTypeID) {
      $type = EventTypeQuery::Create()
              ->findOneById($input->eventTypeID);
      $eventTypeName = $type->getName();
    }

    $event = new Event;
    $event->setTitle($input->EventTitle);
    $event->setType($input->eventTypeID);
    $event->setTypeName($eventTypeName);
    $event->setDesc($input->EventDesc);
    $event->setPubliclyVisible($input->EventPubliclyVisible);

    if ($input->EventGroupID > 0)
      $event->setGroupId($input->EventGroupID);

    $event->setStart(str_replace("T", " ", $input->start));
    $event->setEnd(str_replace("T", " ", $input->end));
    $event->setText(InputUtils::FilterHTML($input->eventPredication));
    $event->save();

    if ($input->Total > 0 || $input->Visitors || $input->Members) {
      $eventCount = new EventCounts;
      $eventCount->setEvtcntEventid($event->getID());
      $eventCount->setEvtcntCountid(1);
      $eventCount->setEvtcntCountname('Total');
      $eventCount->setEvtcntCountcount($input->Total);
      $eventCount->setEvtcntNotes($input->EventCountNotes);
      $eventCount->save();

      $eventCount = new EventCounts;
      $eventCount->setEvtcntEventid($event->getID());
      $eventCount->setEvtcntCountid(2);
      $eventCount->setEvtcntCountname('Members');
      $eventCount->setEvtcntCountcount($input->Members);
      $eventCount->setEvtcntNotes($input->EventCountNotes);
      $eventCount->save();

      $eventCount = new EventCounts;
      $eventCount->setEvtcntEventid($event->getID());
      $eventCount->setEvtcntCountid(3);
      $eventCount->setEvtcntCountname('Visitors');
      $eventCount->setEvtcntCountcount($input->Visitors);
      $eventCount->setEvtcntNotes($input->EventCountNotes);
      $eventCount->save();
    }

    $realCalEvnt = $this->CalendarService->createCalendarItem('event', $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(), $event->getId(), $event->getType(), $event->getGroupId()); // only the event id sould be edited and moved and have custom color

    return $response->withJson(array_filter($realCalEvnt));
  } else if ($input->evntAction == 'moveEvent') {
    $event = EventQuery::Create()
            ->findOneById($input->eventID);


    $oldStart = new DateTime($event->getStart('Y-m-d H:i:s'));
    $oldEnd = new DateTime($event->getEnd('Y-m-d H:i:s'));

    $newStart = new DateTime(str_replace("T", " ", $input->start));

    if ($newStart < $oldStart) {
      $interval = $oldStart->diff($newStart);
      $newEnd = $oldEnd->add($interval);
    } else {
      $interval = $newStart->diff($oldStart);
      $newEnd = $oldEnd->sub($interval);
    }

    $event->setStart($newStart->format('Y-m-d H:i:s'));
    $event->setEnd($newEnd->format('Y-m-d H:i:s'));
    $event->save();

    $realCalEvnt = $this->CalendarService->createCalendarItem('event', $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(), $event->getId(), $event->getType(), $event->getGroupId()); // only the event id sould be edited and moved and have custom color

    return $response->withJson(array_filter($realCalEvnt));
  } else if (!strcmp($input->evntAction, 'retriveEvent')) {
    $event = EventQuery::Create()
            ->findOneById($input->eventID);

    $realCalEvnt = $this->CalendarService->createCalendarItem('event', $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(), $event->getId(), $event->getType(), $event->getGroupId()); // only the event id sould be edited and moved and have custom color

    return $response->withJson(array_filter($realCalEvnt));
  } else if (!strcmp($input->evntAction, 'resizeEvent')) {
    $event = EventQuery::Create()
            ->findOneById($input->eventID);

    $event->setEnd(str_replace("T", " ", $input->end));
    $event->save();

    $realCalEvnt = $this->CalendarService->createCalendarItem('event', $event->getTitle(), $event->getStart('Y-m-d H:i:s'), $event->getEnd('Y-m-d H:i:s'), $event->getEventURI(), $event->getId(), $event->getType(), $event->getGroupId()); // only the event id sould be edited and moved and have custom color

    return $response->withJson(array_filter($realCalEvnt));
  }
}
