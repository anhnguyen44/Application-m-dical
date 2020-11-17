<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Speciality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpecialityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('occupation', ChoiceType::class,[
                'label' => 'Métier',
                'choices' => [
                    'Médical' => 'medical',
                    'Paramédical' => 'paramedical',
                ]
            ])

            ->add('role',ChoiceType::class,[
                'choices' => [
                    'ROLE_MEDICAL' => 'ROLE_MEDICAL',
                    'ROLE_SECRETARY' => 'ROLE_SECRETARY',
                    'ROLE_PARAMEDICAL' => 'ROLE_PARAMEDICAL'
                ],
                'label' => 'Rôle'
            ])

            ->add('speciality',TextType::class,[
                'label' => 'Spécialité',
                'attr' => array('maxlength' => 30)
            ])

            ->add('Enregistrer',SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Speciality::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_member';
    }


}
