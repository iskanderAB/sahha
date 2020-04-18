<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read","readAnswer"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="content")
     * @Groups({"readAnswer"})
     */
    private $fromDoctor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Survey", inversedBy="answer", cascade={"persist", "remove"})
     * @Groups({"readAnswer"})
     */
    private $survey;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Groups({"readAnswer"})
     */
     private $content;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromDoctor(): ?User
    {
        return $this->fromDoctor;
    }

    public function setFromDoctor(?User $fromDoctor): self
    {
        $this->fromDoctor = $fromDoctor;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent($content): self
    {
        $this->content = $content;
        return $this ;
    }
}
