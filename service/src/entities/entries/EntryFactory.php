<?php


namespace RoarProj\entities\entries;


use DateTime;

class EntryFactory
{
    public function createEmptyForDate(DateTime $dateTime)
    {
        return new Entry($dateTime, null, null);
    }
}