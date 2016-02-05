<?php

namespace CampaignChain\Operation\XingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CampaignChainOperationXingBundle:Default:index.html.twig', array('name' => $name));
    }
}
