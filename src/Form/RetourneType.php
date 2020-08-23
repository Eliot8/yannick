<?php

namespace App\Form;

use App\Entity\RetourneProduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetourneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('raison', TextareaType::class, ['label' => 'Pourquoi souhaitez-vous retourner le produit ?', 'attr' => ['rows' => 4, 'name' => 'raison']])
            ->add('Valider', SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'float-right btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RetourneProduit::class,
        ]);
    }
}
