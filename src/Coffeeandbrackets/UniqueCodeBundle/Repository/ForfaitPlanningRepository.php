<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Repository;

use Doctrine\Common\Collections\Criteria;

/**
 * ForfaitPlanningRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ForfaitPlanningRepository extends \Doctrine\ORM\EntityRepository
{
    public function findPlaningById($id)
    {
        $criterea = new Criteria();
        $criterea->where($criterea->expr()->eq('forfaitInternalId', $id));
        $criterea->andWhere($criterea->expr()->gte('year', date('Y')));
        $criterea->andWhere($criterea->expr()->gte('month', date('n')));

        return $this->matching($criterea);
    }
}