<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisponibilidadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('diasDisponibles', ChoiceType::class, [
                'choices' => [
                    '3 Días' => 3,
                    '4 Días' => 4,
                    '5 Días' => 5,
                ],
                'multiple' => false,  // Permitir seleccionar solo una opción
                'expanded' => true,   // Mostrar las opciones como botones de radio
                'label' => false,     // No mostrar etiqueta
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Establecer la clase de datos si es necesario.
            // 'data_class' => Disponibilidad::class, // Descomenta esto si quieres usar la entidad
        ]);
    }
}
