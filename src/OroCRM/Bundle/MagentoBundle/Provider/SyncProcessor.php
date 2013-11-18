<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Connector;
use Oro\Bundle\IntegrationBundle\Provider\ChannelTypeInterface;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class SyncProcessor implements SyncProcessorInterface
{
    const JOB_VALIDATE_IMPORT = 'mage_customer_import_validation';
    const JOB_IMPORT  = 'mage_customer_import';
    const ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';

    /** @var EntityManager */
    protected $em;

    /** @var ProcessorRegistry */
    protected $processorRegistry;

    /** @var JobExecutor */
    protected $jobExecutor;

    /** @var ConnectorInterface */
    protected $connector;

    /**
     * @param EntityManager $em
     * @param ProcessorRegistry $processorRegistry
     * @param JobExecutor $jobExecutor
     */
    public function __construct(
        EntityManager $em,
        ProcessorRegistry $processorRegistry,
        JobExecutor $jobExecutor
    ) {
        $this->em = $em;
        $this->processorRegistry = $processorRegistry;
        $this->jobExecutor = $jobExecutor;
    }

    /**
     * @param string $channelName
     */
    protected function init($channelName)
    {
        /** @var $item ChannelTypeInterface */
        $channel = $this->em
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneBy(['name' => $channelName]);

        /** @var Connector $connector */
        $connectors = $channel->getConnectors();

        // TODO: some logic to select connector from container and configure it
        /** @var ConnectorInterface $connector */
        $this->connector = $connectors->get(0);

        /*
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $settings = [
            'last_sync_date' => $now->sub(\DateInterval::createFromDateString('1 month')),
            'sync_range'     => '1 week',
            'api_user'       => 'api_user',
            'api_key'        => 'api_user',
            'wsdl_url'       => 'http://mage.dev.lxc/index.php/api/v2_soap/?wsdl=1',
        ];
        */
    }

    /**
     * {@inheritdoc}
     */
    public function process($channelName)
    {
        // TODO: do we need this here at all? NO!!
        $this->init($channelName);

        // get processor
        // TODO: refactor this logic
        $processorAliases = $this->processorRegistry->getProcessorAliasesByEntity(
            ProcessorRegistry::TYPE_IMPORT,
            self::ENTITY_NAME
        );
        $processorAlias = reset($processorAliases);

        // TODO: decide if we need to use it
        $result = $this->processValidation(self::ENTITY_NAME, $processorAlias);
var_dump($result);
die();
        $result = $this->processImport(self::ENTITY_NAME, $processorAlias);
var_dump($result);
die();
    }

    /**
     * @param string $entityName
     * @param string $processorAlias
     * @return array
     */
    public function processImport($entityName, $processorAlias)
    {
        $configuration = array(
            'import' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
            ),
        );

        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            self::JOB_IMPORT,
            $configuration
        );

        if ($jobResult->isSuccessful()) {
            $message = 'oro_importexport.import.import_success';
        } else {
            $message = 'oro_importexport.import.import_error';
        }

        return [
            'success' => $jobResult->isSuccessful(),
            'message' => $message,
            'exceptions' => $jobResult->getFailureExceptions(),
        ];
    }

    /**
     *
     * @param string $entityName
     * @param string $processorAlias
     * @return array
     */
    public function processValidation($entityName, $processorAlias)
    {
        $configuration = array(
            'import_validation' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
            ),
        );

        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            self::JOB_VALIDATE_IMPORT,
            $configuration
        );

        /** @var ContextInterface $contexts */
        $context = $jobResult->getContext();

        $counts = array();
        $counts['errors'] = count($jobResult->getFailureExceptions());
        if ($context) {
            $counts['process'] = 0;
            $counts['read'] = $context->getReadCount();
            $counts['process'] += $counts['add'] = $context->getAddCount();
            $counts['process'] += $counts['replace'] = $context->getReplaceCount();
            $counts['process'] += $counts['update'] = $context->getUpdateCount();
            $counts['process'] += $counts['delete'] = $context->getDeleteCount();
            $counts['process'] -= $counts['error_entries'] = $context->getErrorEntriesCount();
            $counts['errors'] += count($context->getErrors());
        }

        $errorsAndExceptions = [];
        if (!empty($counts['errors'])) {
            $errorsAndExceptions = array_slice(
                array_merge(
                    $jobResult->getFailureExceptions(),
                    $context ? $context->getErrors() : []
                ),
                0,
                100
            );
        }

        return array(
            'isSuccessful' => $jobResult->isSuccessful() && isset($counts['process']) && $counts['process'] > 0,
            'processorAlias' => $processorAlias,
            'counts' => $counts,
            'errors' => $errorsAndExceptions,
            'entityName' => $entityName,
        );
    }

    /**
     * @param $batchData
     * @return bool
     */
    public function processOld($batchData)
    {
        foreach ($batchData as $item) {
            $this->em->persist($this->createCustomer($item));
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param $customerData
     *
     * @return Customer
     */
    protected function createCustomer($customerData)
    {
        $customer = new Customer();
        $customer->fillFromArray($customerData);

        return $customer;
    }
}
