<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager, 
        ValidatorInterface $validator,
        SessionInterface $session
    ): Response {
        $user = new Usuarios();

        // Crea el formulario
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Maneja la petición del formulario
        $form->handleRequest($request);

        // Validar el formato del correo electrónico
        $emailConstraint = new Email();
        $emailConstraint->message = 'Por favor, introduce un correo electrónico válido.';

        $violations = $validator->validate($user->getCorreoElectronico(), $emailConstraint);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('error', $violation->getMessage());
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Verifica si ya existe un usuario con el mismo nombre de usuario
            $existingUsername = $entityManager->getRepository(Usuarios::class)->findOneBy([
                'nombreUsuario' => $user->getNombreUsuario(),
            ]);

            if ($existingUsername) {
                // Muestra un mensaje de error si el nombre de usuario ya está en uso
                $this->addFlash('error', 'El nombre de usuario ya existe. Por favor, elige otro.');

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Verifica si ya existe un usuario con el mismo correo electrónico
            $existingEmail = $entityManager->getRepository(Usuarios::class)->findOneBy([
                'correoElectronico' => $user->getCorreoElectronico(),
            ]);

            if ($existingEmail) {
                $this->addFlash('error', 'El correo electrónico ya ha sido utilizado. Por favor, elige otro.');

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Validar la contraseña
            $plainPassword = $form->get('plainPassword')->getData();
            $passwordErrors = $this->validatePassword($plainPassword);

            if (count($passwordErrors) > 0) {
                foreach ($passwordErrors as $error) {
                    $this->addFlash('error', $error);
                }

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Hashear la contraseña
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashedPassword);

            // Guardar el usuario en la base de datos
            $entityManager->persist($user);
            $entityManager->flush();

            // Almacenar el ID del usuario en la sesión
            $session->set('usuario_id', $user->getId());

            // Redirigir a la siguiente ruta después de registrar al usuario
            return $this->redirectToRoute('app_confirm_option');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function validatePassword(string $password): array
    {
        $errors = [];

        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        // Al menos una letra mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }

        // Al menos un número
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'La contraseña debe incluir al menos un número.';
        }

        return $errors;
    }
}
