<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\TokenDecoder;
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
            $user->setRoles(['ROLE_DOCTOR']);

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
     * @Route("/api/doctor/{id}",name="get_doctor",methods={"GET"})
     */
    public function getDoctor ($id,UserRepository $userRepository,Request $request){

        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return $this->json([
                'status' => 404,
                'message' => 'User not found !',
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

        return $this->json($user,200,[],['groups' => 'Read']);
    }

    /**
     * @Route("/api/doctor/{id}",name="delete_doctor",methods={"DELETE"})
     */
    public function deleteDoctor ($id,UserRepository $userRepository,EntityManagerInterface $manager,Request $request){
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return $this->json([
                'status' => 404,
                'message' => 'Doctor not found !',
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
            'message' => 'Doctor deleted '
        ],201);
    }

    /**
     * @Route("/api/doctors",name="get_doctors",methods={"GET"})
     */
    public function getDoctors (Request $request,UserRepository $userRepository){
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN',$roles,true)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        $users = $userRepository->findUsersByRole('ROLE_DOCTOR');
        return $this->json($users,RESPONSE::HTTP_OK,[],['groups' => 'Read']);
    }
    /*
    /**
     * @Route("/api/doctor/{id}",name="edit_doctor",name="{"PUT"})
     */
    /*
    public function editDoctor ($id,Request $request,UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return $this->json([
                'status' => 404,
                'message' => 'User not found'
            ],404);
        }
        $data = $request->getContent();
        try {
            $tokenDecoder = new TokenDecoder($request);
            $tokenEmail = $tokenDecoder->getEmail();
            if($tokenEmail !== $user->getEmail()){
                return $this->json([
                    'status' => 401,
                    'message' => 'Access Denied'
                ],401);
            }
            
        }catch (NotEncodableValueException $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ],400);
        }
    }*/
}