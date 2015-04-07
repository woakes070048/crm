<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

class OpportunityRepository extends EntityRepository
{
    /**
     * @var WorkflowStep[]
     */
    protected $workflowStepsByName;

    /**
     * Get opportunities by state by current quarter
     *
     * @param $aclHelper AclHelper
     * @param  array     $dateRange
     * @return array
     */
    public function getOpportunitiesByStatus(AclHelper $aclHelper, $dateRange)
    {
        $dateEnd = $dateRange['end'];
        $dateStart = $dateRange['start'];

        return $this->getOpportunitiesDataByStatus($aclHelper, $dateStart, $dateEnd);
    }

    /**
     * @param  AclHelper $aclHelper
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getOpportunitiesDataByStatus(AclHelper $aclHelper, $dateStart = null, $dateEnd = null)
    {
        // select statuses
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('status.name, status.label')
            ->from('OroCRMSalesBundle:OpportunityStatus', 'status')
            ->orderBy('status.name', 'ASC');

        $resultData = array();
        $data = $qb->getQuery()->getArrayResult();
        foreach ($data as $status) {
            $name = $status['name'];
            $label = $status['label'];
            $resultData[$name] = array(
                'name' => $name,
                'label' => $label,
                'budget' => 0,
            );
        }

        // select opportunity data
        $qb = $this->createQueryBuilder('opportunity');
        $qb->select('IDENTITY(opportunity.status) as name, SUM(opportunity.budgetAmount) as budget')
            ->groupBy('opportunity.status');

        if ($dateStart && $dateEnd) {
            $qb->where($qb->expr()->between('opportunity.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }
        $groupedData = $aclHelper->apply($qb)->getArrayResult();

        foreach ($groupedData as $statusData) {
            $status = $statusData['name'];
            $budget = (float)$statusData['budget'];
            if ($budget) {
                $resultData[$status]['budget'] = $budget;
            }
        }

        return $resultData;
    }
}
