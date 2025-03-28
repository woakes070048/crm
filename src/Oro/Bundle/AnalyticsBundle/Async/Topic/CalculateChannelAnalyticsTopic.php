<?php

namespace Oro\Bundle\AnalyticsBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to calculate a channel analytics
 */
class CalculateChannelAnalyticsTopic extends AbstractTopic implements JobAwareTopicInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro.analytics.calculate_channel_analytics';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Calculates a channel analytics.';
    }

    #[\Override]
    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('channel_id')
            ->addAllowedTypes('channel_id', 'int');

        $resolver
            ->setDefined('customer_ids')
            ->setDefault('customer_ids', [])
            ->addAllowedTypes('customer_ids', 'int[]');
    }

    #[\Override]
    public function createJobName($messageBody): string
    {
        return 'oro_analytics:calculate_channel_analytics:' . $messageBody['channel_id'];
    }
}
