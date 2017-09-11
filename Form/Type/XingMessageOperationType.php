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

namespace CampaignChain\Operation\XingBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\OperationType;
use CampaignChain\TextareaCountFormTypeBundle\Form\Type\TextareaCountType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XingMessageOperationType extends OperationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setOptions($options);

        $builder
            ->add('message', TextareaCountType::class, array(
                'property_path' => 'message',
                'label' => false,
                'attr' => array(
                    'placeholder' => "What's new?",
                    'maxlength' => 420
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $defaults = array(
            'data_class' => 'CampaignChain\Operation\XingBundle\Entity\XingMessage',
        );

        $resolver->setDefaults($defaults);
    }

    public function getBlockPrefix()
    {
        return 'campaignchain_operation_xing_message';
    }
}
