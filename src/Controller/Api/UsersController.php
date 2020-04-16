<?php


namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends AbstractController
{
    /**
     * @Route("/Api/users/{id}",name="get_user",methods={"GET"})
     */
    public function getUserById ($id,Request $request,UserRepository $userRepository){

        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user || in_array('ROLE_DOCTOR',$user->getRoles(),true) || in_array('ROLE_SUPER_ADMIN',$user->getRoles(),true)){
            return $this->json([
                'status' => 404,
                'message' => 'User not found !',
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();

        if(!in_array('ROLE_SUPER_ADMIN', $roles, true)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }

        return $this->json($user,200,[],['groups' => 'Read']);
    }

    /**
         * @Route("/Api/users/add",name="add_user",methods={"POST"})
     */
    public function addUser (Request $request,UserPasswordEncoderInterface $passwordEncoder,ValidatorInterface $validator,EntityManagerInterface $manager,SerializerInterface $serializer){

        $data = $request->getContent();
        try {
            $tokenDecoder = new TokenDecoder($request);
            $roles = $tokenDecoder->getRoles();

            if (!in_array('ROLE_SUPER_ADMIN',$roles,true)){
                return $this->json([
                    'message' => 'Access Denied !',
                    'status' => 403
                ],403);
            }

            $user = $serializer->deserialize($data, User::class, 'json');
            $password = $user->getPassword();
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
            $user->setRoles(['ROLE_USER']);

            $error = $validator->validate($user);

            if(count($error) > 0){
                return $this->json($error, 400);
            }

            $manager->persist($user);
            $manager->flush();

            return $this->json([
                'message' => 'user created',
                'status' => 201
            ], 201);

        } catch (NotEncodableValueException $exception) {

            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/Api/users/{id}",name="delete_users",methods={"DELETE"})
     */
    public function deleteUser ($id,Request $request,UserRepository $userRepository,EntityManagerInterface $manager){
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user || in_array('ROLE_DOCTOR',$user->getRoles(),true) || in_array('ROLE_SUPER_ADMIN',$user->getRoles(),true)){
            return $this->json([
                'status' => 404,
                'message' =>'User not found !',
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
            if (!in_array('ROLE_SUPER_ADMIN',$roles,true)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        $manager->remove($user);
        $manager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'User deleted '
        ],201);
    }

    /**
     * @Route("/Api/users",name="get_users",methods={"GET"})
     */
    public function getUsers (UserRepository $userRepository,Request $request){
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN',$roles,true) && !in_array('ROLE_DOCTOR',$roles,true)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        $users = $userRepository->findUsersByRole('ROLE_USER');
        return $this->json($users, 200, [], ['groups' => 'Read']);
    }
}