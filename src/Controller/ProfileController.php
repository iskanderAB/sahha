<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\AnswerType;
use App\Form\ProfileType;
use App\Form\UserType;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profile", name="")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile")
     */
    public function index(UserRepository $user,SurveyRepository $surveys)
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'users' => $user->findUsersByRole("ROLE_USER"),
            'surveys' => count($surveys->findAll()),
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


    /**
     * @Route("/newDoctor", name="user_doctor_new", methods={"GET","POST"})
     */
    public function newDoctor(Request $request,UserPasswordEncoderInterface $encoder ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setRoles(["ROLE_SUPER_ADMIN"]);
            $user->setPassword($encoder->encodePassword($user,$user->getPassword()));
            $this->addFlash(
                'notice',
                ' Doctor added ! '
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('profile');
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $encoder ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($encoder->encodePassword($user,$user->getPassword()));
            $this->addFlash(
                'notice',
                'Patient added ! '
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('profile');
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{id}", name="user_show", methods={"GET","POST"})
     * @param User $user
     * @return Response
     */
    public function show(Request $request, User $user): Response
    {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice',"changed saved ! ");
            return $this->redirectToRoute('profile');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

}
