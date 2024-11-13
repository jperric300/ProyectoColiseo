<?php

namespace App\Form;

use App\Entity\Usuarios;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreUsuario', TextType::class, [
                'label' => 'Nombre de usuario',
            ])
            ->add('correoElectronico', TextType::class, [
                'label' => 'Correo Electrónico',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Contraseña'],
                'second_options' => ['label' => 'Repite la Contraseña'],
                'mapped' => false,
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
