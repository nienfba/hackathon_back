<?php

namespace App\Form;

use App\Entity\Insta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('caption')
            ->add('link')
            ->add('user_username')
            ->add('insta_id')
            ->add('low_resolution')
            ->add('standard_resolution')
            ->add('thumbnail')
            ->add('tags')
            ->add('location')
            ->add('created_time')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Insta::class,
        ]);
    }
}
