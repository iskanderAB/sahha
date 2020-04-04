<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoctorController extends AbstractController
{
    /**
     * @Route("/api/doctor/add",name="add_doctor",methods={"POST"})
     */
    public function addDoctor (Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder,EntityManagerInterface $manager,ValidatorInterface $validator): Response
    {

        $data = $request->getContent();
        try {
            $user = $serializer->deserialize($data, User::class, "json");
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
            $user->setRoles(["ROLE_DOCTOR"]);

            $error = $validator->validate($user);

            if(count($error) > 0){
                return $this->json($error, 400);
            }

            $manager->persist($user);
            $manager->flush();

            return $this->json([
               "message"=>"user created",
               "status" => 201
            ], 201);

        } catch (NotEncodableValueException $exception) {

            return $this->json([
                "status" => 400,
                "message" => $exception->getMessage()
            ], 400);
        }

    }

    /**
     * @Route("/api/doctor/{id}",name="get_doctor",methods={"GET"})
     */
    public function getDoctors($id,UserRepository $userRepository){
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return $this->json([
                "status" => 404,
                "message" =>"Doctor not found !",
            ],404);
        }
        return $this->json($user,200,[],["groups" => "Read"]);
    }

    /**
     * @Route("/api/doctor/{id}",name="delete_doctor",methods={"DELETE"})
     */
    public function deleteDoctor($id,UserRepository $userRepository,EntityManagerInterface $manager){
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return $this->json([
                "status" => 404,
                "message" =>"Doctor not found !",
            ],404);
        }
        $manager->remove($user);
        $manager->flush();

        return $this->json([
            "status" => 201,
            "message" => "Doctor deleted "
        ],201);
    }
}