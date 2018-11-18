<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Tag;
use AppBundle\Entity\Team;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'Name',
                'required' => true,
            ]
        );
        $builder->add(
            'users',
            EntityType::class,
            [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUsername();
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Mitglieder',
                'required' => true,
                'placeholder' => '---',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Team::class,
            ]
        );
    }
}
