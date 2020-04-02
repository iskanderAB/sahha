<?php


namespace App\Controller;

use App\Entity\User;
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
    public function addUser(Request $request, SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $response = new Response();
        $data = $request->getContent();
        try {
            $user = $serializer->deserialize($data, User::class, "json");
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
        } catch (NotEncodableValueException $exception) {

            return $this->json([
                "status" => 400,
                "message" => $exception->getMessage()
            ],
                400
            );
        }

        return $response;
    }
}