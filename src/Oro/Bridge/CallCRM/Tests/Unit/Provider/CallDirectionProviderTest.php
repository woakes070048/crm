<?php

namespace Oro\Bridge\CallCRM\Tests\Unit\Provider;

use Oro\Bridge\CallCRM\Provider\CallDirectionProvider;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\CallBundle\Entity\CallDirection;

class CallDirectionProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CallDirectionProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new CallDirectionProvider();
    }

    public function testGetDirection()
    {
        $directionName = 'incoming';

        $direction = new CallDirection($directionName);
        $call = new Call();
        $call->setDirection($direction);
        $this->assertEquals($directionName, $this->provider->getDirection($call, new \stdClass()));
    }

    public function testGetDate()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($date->format('Y'), $this->provider->getDate(new Call())->format('Y'));
    }
}
