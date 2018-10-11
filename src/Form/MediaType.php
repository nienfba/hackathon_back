<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('url')
            ->add('file', FileType::class, array(
                'required' => false,
                'empty_data' => "none",
                'label' => false,
                'attr' => ['placeholder' => 'Choisissez votre media'],
            ))
            //->add('title', [ 'required' => false ])
            //->add('description', [ 'required' => false ])
            //->add('type')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}