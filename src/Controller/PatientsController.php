<?php


namespace App\Controller;

use App\Repository\UserRepository;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PatientsController extends AbstractController
{
    /**
     * @Route("/api/patients/{id}",name="get_patients",methods={"GET"})
     */
    public function getPatient ($id,Request $request,UserRepository $userRepository){

        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user || in_array('ROLE_DOCTOR',$user->getRoles()) || in_array('ROLE_SUPER_ADMIN',$user->getRoles())){
            return $this->json([
                'status' => 404,
                'message' => 'User not found !',
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();

        if(!in_array('ROLE_DOCTOR',$roles) && !in_array('ROLE_SUPER_ADMIN',$roles)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }

        return $this->json($user,200,[],['groups' => 'Read']);
    }

    /**
     * @Route("/api/patients/add",name="add_patients",methods={"POST"})
     */
    public function addPatients (Request $request){

        //TODO add Patients : zid mritdh
    }

    /**
     * @Route("/api/patients/{id}",name="delete_patients",methods={"DELETE"})
     */
    public function deletePatients ($id,Request $request,UserRepository $userRepository,EntityManagerInterface $manager){
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user || in_array('ROLE_DOCTOR',$user->getRoles()) || in_array('ROLE_SUPER_ADMIN',$user->getRoles())){
            return $this->json([
                'status' => 404,
                'message' =>'User not found !',
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if (!in_array("ROLE_SUPER_ADMIN",$roles)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        $manager->remove($user);
        $manager->flush();

        return $this->json([
            'status' => 201,
            'message' => 'Patients deleted '
        ],201);
    }

    /**
     * @Route("/api/patients",name="get_patients",methods={"GET"})
     */
    public function getPatients (UserRepository $userRepository,Request $request){
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if (!in_array("ROLE_SUPER_ADMIN",$roles) && !in_array("ROLE_DOCTOR",$roles)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        $patients = $userRepository->findUsersByRole("ROLE_USER");
        return $this->json($patients, 200, [], ['groups' => 'Read']);
    }
}