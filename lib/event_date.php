<?php

use Ramsey\uuid\uuid;

class event_date extends \rex_yform_manager_dataset
{
    private $location = null;
    private $category = null;
    private $offer = null;

    public static function generateuuid($id = null) :string
    {
        return uuid::uuid3(uuid::NAMESPACE_URL, $id);
    }

    public function getCategory()
    {
        $this->category = $this->getRelatedDataset('event_category_id');
        return $this->category;
    }
    public function getCategories()
    {
        $this->categories = $this->getRelatedCollection('event_category_id');
        return $this->categories;
    }

    public function getIcs()
    {
        $UID = $this->getUid();
        
        $vCalendar = new \Eluceo\iCal\Component\Calendar('-//' . date("Y") . '//#' . rex::getServerName() . '//' . strtoupper((rex_clang::getCurrent())->getCode()));
        date_default_timezone_set(rex::getProperty('timezone'));
        
        $vEvent = new \Eluceo\iCal\Component\Event($UID);
        
        // date/time
        $vEvent
        ->setUseTimezone(true)
        ->setDtStart($this->getStartDate())
        ->setDtEnd($this->getEndDate())
        ->setCreated(new \DateTime($this->getValue("createdate")))
        ->setModified(new \DateTime($this->getValue("updatedate")))
        ->setNoTime($this->getValue("all_day"))
        // ->setNoTime($is_fulltime) // Wenn Ganztag
        // ->setCategories(explode(",", $sked['entry']->category_name))
        ->setSummary($this->getName())
        ->setDescription($this->getDescriptionAsPlaintext());
        
        // add location
        $locationICS = $this->getLocation();
        if (isset($locationICS)) {
            $ics_lat = $locationICS->getValue('lat');
            $ics_lng = $locationICS->getValue('lng');
            $vEvent->setLocation($locationICS->getAsString(), $locationICS->getValue('name'), $ics_lat != '' ? $ics_lat . ',' . $ics_lng : '');
            // fehlt: set timezone of location
        }
        
        //  add event to calendar
        $vCalendar->addComponent($vEvent);
        
        return $vCalendar->render();
        // ob_clean();
        
        // exit($vEvent);
    }

    public function getLocation()
    {
        if ($this->location === null) {
            $this->location = $this->getRelatedDataset('location');
        }
        return $this->location;
    }
    
    public function getTimezone($lat, $lng)
    {
        $event_timezone = "https://maps.googleapis.com/maps/api/timezone/json?location=" . $lat . "," . $lng . "&timestamp=" . time() . "&sensor=false";
        $event_location_time_json = file_get_contents($event_timezone);
        return $event_location_time_json;
    }

    public function getOfferAll()
    {
        return $this->getRelatedCollection('offer');
    }

    public function getImage() :string
    {
        return $this->image;
    }
    public function getMedia()
    {
        return rex_media::get($this->image);
    }

    public function getDescriptionAsPlaintext() :string
    {
        return strip_tags(html_entity_decode($this->description));
    }
    public function getIcsStatus()
    {
        return strip_tags($this->eventStatus);
    }
    public function getUid()
    {
        if ($this->uid === "" && $this->getValue("uid") === "") {
            $this->uid = self::generateUuid($this->id);

            rex_sql::factory()->setQuery("UPDATE rex_event_date SET uid = :uid WHERE id = :id", [":uid"=>$this->uid, ":id" => $this->getId()]);
        }
        return $this->uid;
    }

    public function getJsonLd()
    {
        $fragment = new rex_fragment();
        $fragment->setVar("event_date", $this);
        return $fragment->parse('event-date-single.json-ld.php');
    }

    public static function formatDate($format_date = IntlDateFormatter::FULL, $format_time = IntlDateFormatter::SHORT, $lang = "de")
    {
        return datefmt_create($lang, $format_date, $format_time, null, IntlDateFormatter::GREGORIAN);
    }

    private function getDateTime($date, $time = "00:00")
    {
        $time = explode(":", $time);
        $dateTime = new DateTime($date);
        $dateTime->setTime($time[0], $time[1]);

        return $dateTime;
    }

    public function getFormattedStartDate($format_date = IntlDateFormatter::FULL, $format_time = IntlDateFormatter::SHORT)
    {
        return self::formatDate($format_date, $format_time)->format($this->getDateTime($this->getValue("startDate"), $this->getValue("startTime")));
    }


    public function getFormattedEndDate($format_date = IntlDateFormatter::FULL, $format_time = IntlDateFormatter::SHORT)
    {
        return self::formatDate($format_date, $format_time)->format($this->getDateTime($this->getValue("endDate"), $this->getValue("endTime")));
    }
    
    public function getName()
    {
        return $this->getValue("name");
    }
    public function getPrice()
    {
        $offer = rex_yform_manager_table::get('rex_event_date_offer')->query()->where("date_id", $this->getValue('id'))->find();

        if (count($offer) > 0) {
            return $offer[0]->getPrice();
        }
        return $this->getCategories()[0]->getPrice();
    }
    public function getPriceFormatted()
    {
        return $this->getPrice() . " EUR";
    }
}
