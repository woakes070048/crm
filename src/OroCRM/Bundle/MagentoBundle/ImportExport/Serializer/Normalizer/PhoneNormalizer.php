<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\PhoneNormalizer as BaceNormalizer;

class PhoneNormalizer extends BaceNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof AbstractPhone && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return
            is_string($data)
            && class_exists($type)
            && in_array(self::ABSTRACT_PHONE_TYPE, class_parents($type))
            && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }
}
