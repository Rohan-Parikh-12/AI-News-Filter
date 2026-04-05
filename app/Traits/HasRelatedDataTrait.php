<?php

namespace App\Traits;

trait HasRelatedDataTrait
{
    public function getHasRelatedData(): bool
    {
        foreach ($this->getRelationshipChecks() as $relation => $label) {
            if ($this->{$relation}()->exists()) {
                return true;
            }
        }

        return false;
    }

    public function getRelationshipChecks(): array
    {
        return [];
    }
}
