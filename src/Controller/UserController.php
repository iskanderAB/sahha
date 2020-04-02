<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/add/user",methods={"POST"})
     */
    public function addUser(Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder,EntityManagerInterface $manager): Response
    {

        $data = $request->getContent();
        try {
            $user = $serializer->deserialize($data, User::class, "json");
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));

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
}