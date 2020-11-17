<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Patient;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatientFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom*',
                'attr' => array('maxlength' => 30)
            ])
            
            ->add('prenom',TextType::class, [
                'label'=>'Prénom*',
                'attr' => array('maxlength' => 30)
            ])
            
            
            ->add('sexe', ChoiceType::class, [
                'label'=>'Sexe*',
                'choices' => [
                    'Masculin' => 'Masculin',
                    'Féminin'  => 'Féminin'
                ]
            ])
            
            ->add('dateNaissance',BirthdayType::class, [
                'label'=>'Date de naissance*'

            ])

            ->add('email',EmailType::class, [
                'label'=>'Adresse mail',
                'required' => false,
                'attr' => array('maxlength' => 50)
            ])

            ->add('adresse',TextType::class,[
                'label' => 'Adresse',
                'required' => false,
                'attr' => array('maxlength' => 255)
            ])

            ->add('tel', IntegerType::class,[
                'label' => 'Numéro de Téléphone',
                'required' => false,
                'attr' => array('min' => 0, 'max' => 2147483647)

            ])

            ->add('socialNumber',TextType::class,[
                'label' => 'N. Sécurité Sociale',
                'required' => false,
                'attr' => array('minlength' => 15, 'maxlength' => 15)

            ])
            ->add('medecinTraitant',TextType::class,[
                'label' => 'Médecin Traitant',
                'required' => false,
                'attr' => array('maxlength' => 50)

            ])

            ->add('Public',ChoiceType::class,[
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'label' => "Participation à des études statistiques*",
                'multiple'=>false, 'expanded'=>true,
                'data' => false,
            ])

            ->add('partage',ChoiceType::class,[
                'choices' => [
                    'Oui' => true,
                    'Non' => false
                ],
                'label' => "Dossier visible et modifiable par tous*",
                'multiple'=>false, 'expanded'=>true,
                'data' => false,

            ])

            // ->add('Valider', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patient::class
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
