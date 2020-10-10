<?php

namespace RoarProj\controllers\serializers;

use Neomerx\JsonApi\Schema\SchemaProvider;
use RoarProj\entities\entries\Entry;

class EntryToJson extends SchemaProvider
{
    public function getId($object)
    {
        return $object->getId();
    }

    /**
     * @param Entry $object
     * @return array
     */
    public function getAttributes($object)
    : array {
        return [
            "date"        => $object->getDate()->format(DATE_ISO8601),
            "notes"       => $object->getNotes(),
            "hoursWorked" => $object->getHoursWorked()
        ];
    }

    public function getResourceType()
    {
        return 'Entry';
    }
}
