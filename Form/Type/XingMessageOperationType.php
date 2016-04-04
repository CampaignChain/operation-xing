<?php

namespace CampaignChain\Operation\XingBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\OperationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class XingMessageOperationType extends OperationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'textarea', array(
                'property_path' => 'message',
                'label' => false,
                'attr' => array(
                    'placeholder' => "What's new?",
                    'maxlength' => 420
                )
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaults = array(
            'data_class' => 'CampaignChain\Operation\XingBundle\Entity\XingMessage',
        );

        if($this->content){
            $defaults['data'] = $this->content;
        }
        $resolver->setDefaults($defaults);
    }

    public function getName()
    {
        return 'campaignchain_operation_xing_message';
    }
}
