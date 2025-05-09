<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub;

use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareTrait;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class RFMAwareStub implements RFMAwareInterface
{
    use RFMAwareTrait;

    #[\Override]
    public function setDataChannel(?Channel $channel = null)
    {
    }

    #[\Override]
    public function getDataChannel()
    {
    }
}
