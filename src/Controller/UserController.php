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

class UserController extends AbstractController
{
    /**
     * @Route("/api/add/user",name="add_user",methods={"POST"})
     */
    public function addUser (Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder,EntityManagerInterface $manager,ValidatorInterface $validator): Response
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
     * @Route("/api/users",name="get_users",methods={"GET"})
     */
    public function getUsers(UserRepository $userRepository,SerializerInterface $serializer){
        $users = $userRepository->findAll();
        $userJson = $serializer->serialize($users,'json',["groups" => "Read"]);
        return new Response(
            $userJson, 200, [
                "content-type" => "application/json"
            ]
        );
    }
}