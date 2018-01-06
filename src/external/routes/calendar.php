<?php

// Routes

use ChurchCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use ChurchCRM\dto\ChurchMetaData;

$app->group('/calendar', function () {
    $this->get('/events', function ($request, $response, $args) {
        if (!ChurchCRM\dto\SystemConfig::getBooleanValue("bEnableExternalCalendarAPI"))
        {
          throw new \Exception(gettext("External Calendar API is disabled")  , 400);
        }

        $params = $request->getQueryParams();
        $start_date = DateTime::createFromFormat("Y-m-d",$params['start']);
        $start_date->setTime(0,0,0);
        $max_events = InputUtils::FilterInt($params['max']);

        $events = ChurchCRM\EventQuery::create()
                ->filterByPubliclyVisible(true)
                ->orderByStart(Criteria::ASC);

        if($start_date) {
          $events->filterByStart($start_date,  Criteria::GREATER_EQUAL);
        }

        if ($max_events) {
          $events->limit($max_events);
        }
        
        return $response->withJson($events->find()->toArray());

    });
    
    $this->get('/ics', function ($request, $response, $args) {
        if (!ChurchCRM\dto\SystemConfig::getBooleanValue("bEnableExternalCalendarAPI"))
        {
          throw new \Exception(gettext("External Calendar API is disabled")  , 400);
        }

        $params = $request->getQueryParams();
        $start_date = DateTime::createFromFormat("Y-m-d",$params['start']);
        $start_date->setTime(0,0,0);
        $max_events = InputUtils::FilterInt($params['max']);

        $events = ChurchCRM\EventQuery::create()
                ->filterByPubliclyVisible(true)
                ->orderByStart(Criteria::ASC);

        if($start_date) {
          $events->filterByStart($start_date,  Criteria::GREATER_EQUAL);
        }

        if ($max_events) {
          $events->limit($max_events);
        }
        
        $CalendarICS = "BEGIN:VCALENDAR\n".
                       "VERSION:2.0\n".
                       "PRODID:-//ChurchCRM/CRM//NONSGML v".$_SESSION['sSoftwareInstalledVersion']."//EN\n".
                       "CALSCALE:GREGORIAN\n".
                       "METHOD:PUBLISH\n".
                       "X-WR-CALNAME:".ChurchMetaData::getChurchName()."\n".
                       "X-WR-TIMEZONE:".ChurchMetaData::getChurchTimeZone()."\n".
                       "X-WR-CALDESC:\n";
        foreach($events->find() as $event)
        {
          $CalendarICS .= $event->toVEVENT();
        }
        $CalendarICS .="END:VCALENDAR";
        
        $body = $response->getBody();
        $body->write($CalendarICS);
        
        return $response->withHeader('Content-type','text/calendar; charset=utf-8')
          ->withHeader('Content-Disposition','attachment; filename=calendar.ics');;

    });
});
