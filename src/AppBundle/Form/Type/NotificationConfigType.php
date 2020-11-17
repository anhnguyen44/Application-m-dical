<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('notificationType', ChoiceType::class, [
                'choices' => [
                    'Nombre de notifications' => 1,
                    'Evaluateur particulier' => 2,
                    'Date' => 3,
                ],
                'placeholder' => 'Choisir une option'
            ])

            ->add('numberNotifications', IntegerType::class, [
                'data' => 1
            ])
            ->add('dateNotifications', IntegerType::class, [
                'data' => 1
            ])

            ->add('Enregistrer', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }




}