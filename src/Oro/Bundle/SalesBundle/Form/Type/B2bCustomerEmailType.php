<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2bCustomerEmailType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // basic plain fields
        $builder
            ->add('email', TextType::class, array('required' => true))
            ->add('primary', CheckboxType::class, array('required' => true));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail',
                'csrf_token_id' => 'b2bcustomer',
                'csrf_protection' => false
            )
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_b2bcustomer_email';
    }
}
