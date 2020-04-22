<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"Read","read_survey","readAnswer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Groups({"Read","read_survey","readAnswer","read_story"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"Read"})
     */
    
    private $roles = [];

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="8")
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"Read","read_survey","readAnswer","read_story"})
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"Read","read_survey","readAnswer","read_story"})
     */
    private $lastName;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    private $Birthday;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"Read"})
     */
    private $Gouvernorate;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Choice(choices={"F","M"})
     * @Groups({"Read"})
     */
    private $Gender;

    /**
     * @Assert\NotBlank()
     * @Assert\Positive()
     * @Assert\Length(min="8",max="8")
     * @ORM\Column(type="integer", nullable=true, length=8)
     * @Groups({"Read","readAnswer"})
     */
    private $PhoneNumber;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"Read"})
     */
    private $Address;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Survey", mappedBy="createdBy",cascade={"persist"})
     */
    private $surveys;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="fromDoctor")
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SuccessStory", mappedBy="createdBy")
     */
    private $successStories;

    public function __construct()
    {
        $this->surveys = new ArrayCollection();
        $this->content = new ArrayCollection();
        $this->successStories = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthday(): ?string
    {
        return $this->Birthday;
    }

    public function setBirthday(?string $Birthday): self
    {
        $this->Birthday = $Birthday;

        return $this;
    }

    public function getGouvernorate(): ?string
    {
        return $this->Gouvernorate;
    }

    public function setGouvernorate(?string $Gouvernorate): self
    {
        $this->Gouvernorate = $Gouvernorate;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->Gender;
    }

    public function setGender(?string $Gender): self
    {
        $this->Gender = $Gender;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->PhoneNumber;
    }

    public function setPhoneNumber(?int $PhoneNumber): self
    {
        $this->PhoneNumber = $PhoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->Address;
    }

    public function setAddress(?string $Address): self
    {
        $this->Address = $Address;

        return $this;
    }

    /**
     * @return Collection|Survey[]
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): self
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys[] = $survey;
            $survey->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): self
    {
        if ($this->surveys->contains($survey)) {
            $this->surveys->removeElement($survey);
            // set the owning side to null (unless already changed)
            if ($survey->getCreatedBy() === $this) {
                $survey->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getContent(): Collection
    {
        return $this->content;
    }

    public function addContent(Answer $content): self
    {
        if (!$this->content->contains($content)) {
            $this->content[] = $content;
            $content->setFromDoctor($this);
        }

        return $this;
    }

    public function removeContent(Answer $content): self
    {
        if ($this->content->contains($content)) {
            $this->content->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getFromDoctor() === $this) {
                $content->setFromDoctor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SuccessStory[]
     */
    public function getSuccessStories(): Collection
    {
        return $this->successStories;
    }

    public function addSuccessStory(SuccessStory $successStory): self
    {
        if (!$this->successStories->contains($successStory)) {
            $this->successStories[] = $successStory;
            $successStory->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSuccessStory(SuccessStory $successStory): self
    {
        if ($this->successStories->contains($successStory)) {
            $this->successStories->removeElement($successStory);
            // set the owning side to null (unless already changed)
            if ($successStory->getCreatedBy() === $this) {
                $successStory->setCreatedBy(null);
            }
        }

        return $this;
    }
}