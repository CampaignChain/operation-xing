<?php

namespace CampaignChain\Operation\XingBundle\EntityService;

use Doctrine\ORM\EntityManager;
use CampaignChain\CoreBundle\EntityService\OperationServiceInterface;
use CampaignChain\CoreBundle\Entity\Operation;

class XingMessage implements OperationServiceInterface
{
    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function getMessageByOperation($id){
        $message = $this->em->getRepository('CampaignChainOperationXingBundle:XingMessage')
            ->findOneByOperation($id);
        if (!$message) {
            throw new \Exception(
                'No message found by operation id '.$id
            );
        }
        return $message;
    }
    
    public function cloneOperation(Operation $oldOperation, Operation $newOperation)
    {
        $message = $this->getMessageByOperation($oldOperation);
        $clonedMessage = clone $message;
        $clonedMessage->setOperation($newOperation);
        $this->em->persist($clonedMessage);
        $this->em->flush();
    }
    
    public function removeOperation($id){
        try {
            $operation = $this->getMessageByOperation($id);
            $this->em->remove($operation);
            $this->em->flush();
        } catch (\Exception $e) {
        }
    }
}