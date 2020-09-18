<?php

namespace App\Form;

use App\Entity\Marque;
use App\Entity\ModeleChaussure;
use App\Entity\Photo;
use App\Entity\Taille;
use Symfony\Component\Validator\Constraints\Url;
//use http\Url;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModeleChaussureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => '8',
                ]
            ])
            // ->add('coverImage', FileType::class, array(
            //     'label' => false,
            //     'data_class' => null,
            //     // 'required' => false,
            //     'attr' => [
            //         'class' => 'my-2'
            //     ]
            //     //      'mapped'=>false,

            // ))
            // ->setMethod("POST")


            ->add('marque', EntityType::class, [
                'class' => Marque::class,
                'choice_label' => 'nom'
            ])
            // dd
            // ->add(
            //     'photos',
            //     FileType::class,
            //     [
            //         'label' => false,
            //         'by_reference' => true,
            //         'multiple' => true,
            //         'data_class' => null,
            //         'mapped' => false,
            //         // 'required' => false,
            //         'attr' => [
            //             'id' => 'file-input',
            //             'class' => false 
            //         ]
            //     ]
            // )

            //*  ->add('photos', CollectionType::class, [
            //   'entry_type' => PhotoType::class,
            //   'allow_add' => true,
            //   'allow_delete' => true,
            //     'prototype' => true,
            //   'by_reference' => false,
            //      'label' => false,
            //      'mapped'=>false,
            //    ])


            ->add('valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary float-right btn-sm'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ModeleChaussure::class,
        ]);
    }
}
