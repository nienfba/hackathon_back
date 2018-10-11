<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MemberEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password', PasswordType::class, array(
                'required'   => false))
            ->add('confirm_password', PasswordType::class, array(
                'required'   => false))
            ->add('email')
            // ->add('role')
            // ->add('cle')
            // ->add('registrationDate')
            ->add('description')
            ->add('extraCss', TextareaType::class, array(
                'required'   => false))
            ->add('extraJs', TextareaType::class, array(
                'required'   => false))
            //->add('url')
            ->add('file', FileType::class, array(
                'required' => false,
                'empty_data' => "none",
                'label' => false,
                'attr' => ['placeholder' => 'Choisissez votre media'],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
