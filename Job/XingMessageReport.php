<?php
namespace CampaignChain\Operation\XingBundle\Job;

use CampaignChain\CoreBundle\Entity\SchedulerReportOperation;
use CampaignChain\CoreBundle\Job\JobReportInterface;
use Doctrine\ORM\EntityManager;

class XingMessageReport implements JobReportInterface
{
    const OPERATION_BUNDLE_NAME = 'campaignchain/operation-xing';
    const METRIC_LIKES    = 'Likes';
    const METRIC_COMMENTS = 'Comments';

    protected $em;
    protected $container;
    protected $message;
    protected $operation;

    public function __construct(EntityManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getMessage(){
        return $this->message;
    }
    
    public function schedule($operation, $facts = null)
    {
        $scheduler = new SchedulerReportOperation();
        $scheduler->setOperation($operation);
        $scheduler->setInterval('1 hour');
        $scheduler->setEndAction($operation->getActivity()->getCampaign());
        $this->em->persist($scheduler);

        $facts[self::METRIC_LIKES] = 0;
        $facts[self::METRIC_COMMENTS] = 0;

        $factService = $this->container->get('campaignchain.core.fact');
        $factService->addFacts('activity', self::OPERATION_BUNDLE_NAME, $operation, $facts);
    }

    public function execute($operationId)
    {
        $operationService = $this->container->get('campaignchain.core.operation');
        $operation = $operationService->getOperation($operationId);

        $message = $this->em
                        ->getRepository('CampaignChainOperationXingBundle:XingMessage')
                        ->findOneByOperation($operationId);

        if (!$message) {
            throw new \Exception('No message found for an operation with ID: '.$operationId);
        }

        $activity = $message->getOperation()->getActivity();
        $messageId = $message->getMessageId();

        $client = $this->container->get('campaignchain.channel.xing.rest.client');
        $connection = $client->connectByActivity($activity);
        
        $request = $connection->get('activities/' . $messageId, array());
        $response = $request->send()->json();
        
        if(!count($response)){
            $likes = 0;
            $comments = 0;
        } else {
            $likes = $response['activities'][0]['likes']['amount'];
            $comments = $response['activities'][0]['comments']['amount'];
        }

        // Add report data.
        $facts[self::METRIC_LIKES] = $likes;
        $facts[self::METRIC_COMMENTS] = $comments;

        $factService = $this->container->get('campaignchain.core.fact');
        $factService->addFacts('activity', self::OPERATION_BUNDLE_NAME, $operation, $facts);

        $this->message = 'Added to report: likes = '.$likes.', comments = '.$comments.'.';

        return self::STATUS_OK;
    }

}