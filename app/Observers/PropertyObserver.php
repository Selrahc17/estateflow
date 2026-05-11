<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Property;

class PropertyObserver
{
    public function created(Property $property): void
    {
        AuditLog::log(
            'property_created',
            $property,
            "Property \"{$property->title}\" was created.",
            [],
            $property->only(['title', 'location', 'price', 'status'])
        );
    }

    public function updated(Property $property): void
    {
        $dirty = $property->getDirty();
        if (empty($dirty)) return;

        $old = array_intersect_key($property->getOriginal(), $dirty);

        AuditLog::log(
            'property_updated',
            $property,
            "Property \"{$property->title}\" was updated.",
            $old,
            $dirty
        );
    }

    public function deleted(Property $property): void
    {
        AuditLog::log(
            'property_deleted',
            $property,
            "Property \"{$property->title}\" was deleted.",
            $property->only(['title', 'location', 'price', 'status']),
            []
        );
    }
}
