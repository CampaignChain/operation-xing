<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\Operation\XingBundle\Job;

use CampaignChain\CoreBundle\Entity\Action;
use Doctrine\ORM\EntityManager;
use CampaignChain\CoreBundle\Entity\Medium;
use CampaignChain\CoreBundle\Job\JobActionInterface;
use Symfony\Component\HttpFoundation\Response;
use CampaignChain\CoreBundle\EntityService\CTAService;

class XingMessage implements JobActionInterface
{
    protected $em;
    protected $container;

    protected $message;
    protected $link;

    public function __construct(EntityManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function execute($operationId)
    {
        $message = $this->em
                        ->getRepository('CampaignChainOperationXingBundle:XingMessage')
                        ->findOneByOperation($operationId);

        if (!$message) {
            throw new \Exception('No message found for an operation with ID: '.$operationId);
        }

        $ctaService = $this->container->get('campaignchain.core.cta');
        $message->setMessage(
            $ctaService->processCTAs($message->getMessage(), $message->getOperation(), CTAService::FORMAT_TXT)->getContent()
        );
        
        $oauthToken = $this->container->get('campaignchain.security.authentication.client.oauth.token');
        $activity = $message->getOperation()->getActivity();
        $identifier = $activity->getLocation()->getIdentifier();
        $token = $oauthToken->getToken($activity->getLocation());
        
        $client = $this->container->get('campaignchain.channel.xing.rest.client');
        $connection = $client->connectByActivity($message->getOperation()->getActivity());
        
        $request = $connection->post('users/' . $identifier . '/status_message', array(), array('id' => $identifier, 'message' => $message->getMessage()));
        $response = $request->send();
        $messageEndpoint = $response->getHeader('location');
        $messageId = basename($messageEndpoint);
        $messageUrl = 'https://www.xing.com/feedy/stories/' . strtok($messageId, '_');
        $message->setUrl($messageUrl);
        $message->setMessageId($messageId);

        $message->getOperation()->setStatus(Action::STATUS_CLOSED);
        $location = $message->getOperation()->getLocations()[0];
        $location->setIdentifier($messageId);
        $location->setUrl($messageUrl);
        $location->setName($message->getOperation()->getName());
        $location->setStatus(Medium::STATUS_ACTIVE);

        // Schedule data collection for report
        $report = $this->container->get('campaignchain.job.report.campaignchain.xing.message');
        $report->schedule($message->getOperation());        
        $this->em->flush();

        $this->message = 'The message "'.$message->getMessage().'" with the ID "'.$messageId.'" has been posted on XING. See it on XING: <a href="'.$messageUrl.'">'.$messageUrl.'</a>';

        return self::STATUS_OK;
    }

    public function getMessage(){
        return $this->message;
    }

}