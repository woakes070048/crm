<?php

declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\Tests\Unit\Migration;

use Oro\Bundle\SalesBundle\Migration\RemoveCustomerAssociationQuery;
use PHPUnit\Framework\TestCase;

class RemoveCustomerAssociationQueryTest extends TestCase
{
    public function testInitialized(): void
    {
        $query = new RemoveCustomerAssociationQuery('Some\Entity', 'some_table', true);
        self::assertEquals(
            'Remove association relation from Oro\Bundle\SalesBundle\Entity\Customer entity to Some\Entity '
            . '(association kind: customer, relation type: manyToOne, drop relation column/table: yes, '
            . 'source table: orocrm_sales_customer, target table: some_table).',
            $query->getDescription()
        );
    }
}
