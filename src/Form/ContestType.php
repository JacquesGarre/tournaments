<?php

namespace App\Form;

use App\Entity\Contest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\PlayerType;

class ContestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {           
        $builder
            ->add('name')
            ->add('table_name')
            ->add('players')
            ->add('board')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contest::class,
            'players' => [],
        ]);
    }
}
