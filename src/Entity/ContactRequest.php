<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ScyLabs\NeptuneBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRequestRepository")
 */
class ContactRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="ScyLabs\NeptuneBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="string",length=255,nullable=false)
     * @Assert\Ip
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    private $phone;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
    public function setUser(?User $user) : self{
        $this->user = $user;
        return $this;
    }
    public function getUser() : ?User{
        return $this->user;
    }
    public function getIp() :string{
        return $this->ip;
    }
    public function setIp(string $ip) : self{
        $this->ip = $ip;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getPhone() : ?string {
        return $this->phone;
    }
    public function setPhone(?string $phone) : self {
        $this->phone = $phone;
        return $this;
    }
    public function toArray(){
        $array = new ArrayCollection();
        foreach ($this as $key => $value){
            $array[$key] = $value;
        }
        return $array;
    }
}
