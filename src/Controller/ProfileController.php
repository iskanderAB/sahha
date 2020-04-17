<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\SurveyType;
use App\Repository\AnswerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile", name="")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile")
     */
    public function index(UserRepository $user)
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'users' => $user->findUsersByRole("ROLE_USER"),
        ]);
    }

    /**
     * @Route("/patients", name="patients", methods={"GET"})
     */
    public function patients(): Response
    {
        return $this->render('profile/patients.html.twig', [
            'answers' => $this->getUser()->getContent(),
        ]);
    }


    /**
     * @Route("/{id}/answer", name="DoctorAnswer", methods={"GET","POST"})
     */
    public function edit(Request $request, Survey $survey): Response
    {
        if ($survey->getAnswer() == null) {
            $answer = new Answer();
        } else {
            $answer = $survey->getAnswer();
        }
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        $user =$this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setFromDoctor($user);
            $survey->setAnswer($answer);
            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );
            $this->getDoctrine()->getManager()->persist($answer);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('patients');
        }
        return $this->render('profile/answer.html.twig', [
            'survey' => $survey,
            'form' => $form->createView(),
        ]);
    }
}
