<?php

namespace App\Form;

use App\Entity\Inmobiliaria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InmobiliariaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('direccion')
            ->add('email')
            ->add('whatsapp')
            ->add('contactadoWp')
            ->add('telefono')
            ->add('link_venta')
            ->add('link_alquiler')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inmobiliaria::class,
        ]);
    }
}
