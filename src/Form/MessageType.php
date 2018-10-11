<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content',TextareaType::class, array(
                'label' => 'Laissez votre message : ',
            ))
            //->add('publicationDate')
            //->add('status')
            //->add('ip')
            //->add('info')
            //->add('Author')
            ->add('file',FileType::class, array(
                'attr' => ['placeholder' => 'Choisissez votre media'],
                'label' => ' ',));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
