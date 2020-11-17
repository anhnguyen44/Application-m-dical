<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\HealthCare;
use AppBundle\Entity\Speciality;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthCareFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom',
                'attr' => array('maxlength' => 80)
            ])
            
            ->add('speciality', ChoiceType::class, [
                'label'=>'Spécialité',
                'choices' => $options['specialities'],
                'choice_label' => 'speciality',
                'choice_value' => function(Speciality $spe = null) {
                    return $spe ? $spe->getId() : '';
                },
            ])

            ->add('sessionCount', IntegerType::class, array(
                'label'=> 'Nombre de soin par mois',
                'attr' => array('min' => 1, 'max' => 31)
            ))

            ->add('description', TextareaType::class, [
                'label'=>'Description',
                'required' => false,
                'attr' => array('style' => "resize:none", 'rows' => "10")
            ]);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => HealthCare::class,
            'specialities' => null
        ));
    }
    
}