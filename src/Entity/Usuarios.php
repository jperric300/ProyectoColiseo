<?php

namespace App\Entity;

use App\Repository\UsuariosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuariosRepository::class)]
class Usuarios implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $correoElectronico; // Correo Electrónico en camelCase

    #[ORM\Column(type: 'string', length: 100)]
    private $nombreUsuario; // Nombre de Usuario

    #[ORM\Column(type: 'string')]
    private $password; // Contraseña

    #[ORM\Column(type: 'json')]
    private $roles = [];

    /**
     * @var Collection<int, Opciones>
     */
    #[ORM\OneToMany(targetEntity: Opciones::class, mappedBy: 'id_Usuarios')]
    private Collection $objetio;

    public function __construct()
    {
        $this->objetio = new ArrayCollection();
    } // Roles del usuario

    // Getters y Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorreoElectronico(): ?string
    {
        return $this->correoElectronico;
    }

    public function setCorreoElectronico(string $correoElectronico): self
    {
        $this->correoElectronico = $correoElectronico;

        return $this;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->nombreUsuario = $nombreUsuario;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * 
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->correoElectronico; // Utiliza el correo electrónico como identificador
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantiza que cada usuario tenga al menos el rol ROLE_USER
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password; // Devuelve la contraseña
    }

    public function setPassword(string $password): self // Cambia 'contraseña' a 'password' para coherencia
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Erase credentials
     *
     * Este método se llama cuando el usuario cierra sesión. 
     * Puedes limpiar cualquier dato sensible almacenado en la entidad aquí.
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Si tienes algún dato sensible temporal, límpialo aquí.
    }

    /**
     * @return Collection<int, Opciones>
     */
    public function getObjetio(): Collection
    {
        return $this->objetio;
    }

    public function addObjetio(Opciones $objetio): static
    {
        if (!$this->objetio->contains($objetio)) {
            $this->objetio->add($objetio);
            $objetio->setIdUsuarios($this);
        }

        return $this;
    }

    public function removeObjetio(Opciones $objetio): static
    {
        if ($this->objetio->removeElement($objetio)) {
            // set the owning side to null (unless already changed)
            if ($objetio->getIdUsuarios() === $this) {
                $objetio->setIdUsuarios(null);
            }
        }

        return $this;
    }
}
