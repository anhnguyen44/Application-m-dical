<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\HealthSession;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthSessionFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextareaType::class, [
                'label'=>'Commentaire',
                'required' => false,
                'attr' => array('style' => "resize:none", 'rows' => "10")
            ])

            ->add('date', DateTimeType::class, array(
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'HH:mm dd/MM/yyyy' //http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => HealthSession::class,
        ));
    }
    
}