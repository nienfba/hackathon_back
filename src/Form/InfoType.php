<?php

namespace App\Form;

use App\Entity\Info;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('icon', ChoiceType::class, array(
                                        'expanded' => false,
                                        'multiple' => false,
                                        'choices'  => array(
                                            'question'          => 'question',
                                            'child'             => 'child',
                                            'cocktail'          => 'cocktail',
                                            'eye'               => 'eye',
                                            'thumbs-up'         => 'thumbs-up',
                                            'umbrella-beach'    => 'umbrella-beach',
                                            'swimmer'           => 'swimmer',
                                            'futbol'            => 'futbol',
                                            'fish'              => 'fish',
                                            'kiwi-bird'         => 'kiwi-bird',
                                            'smile'             => 'smile',
                                            'camera'            => 'camera',
            )))
            ->add('categories', EntityType::class, array(
                // looks for choices from this entity
                'class' => Category::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'name',

                // used to render a select box, check boxes or radios
                'multiple' => true,
                'expanded' => true,
            ))
            /*
            */
            ->add('latitude', TextType::class, array(
//                'data' => '0.000'
            ))
            ->add('longitude',TextType::class, array(
//                'data' => '0.000'
            ))
            //->add('publicationDate')
            //->add('endDate')
            ->add('title')
            ->add('description')
            ->add('media',CollectionType::class, array(
                        'entry_type' => MediaType::class,
                        'allow_add'    =>true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'label' => ' ',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Info::class,
        ]);
    }
}
