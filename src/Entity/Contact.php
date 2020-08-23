<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Contact{

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max=200)
     */
    private $objet;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min=20, max=10000)
     */
    private $content;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Contact
     */
    public function setName(?string $name): Contact
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail(?string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjet(): ?string
    {
        return $this->objet;
    }

    /**
     * @param string $objet
     * @return Contact
     */
    public function setObjet(?string $objet): Contact
    {
        $this->objet = $objet;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Contact
     */
    public function setContent(?string $content): Contact
    {
        $this->content = $content;
        return $this;
    }
}