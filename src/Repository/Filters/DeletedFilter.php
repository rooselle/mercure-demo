<?php

namespace App\Repository\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasField('deletedAt')) {
            $date = date('Y-m-d H:i:s');

            return $targetTableAlias.".deleted_at > '".$date."' OR ".$targetTableAlias.'.deleted_at IS NULL';
        }

        return '';
    }
}
