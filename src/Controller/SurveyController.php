<?php


namespace App\Controller;


use App\Entity\Survey;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class SurveyController extends AbstractController
{
    /**
     * @Route("/api/survey/add",name="add_survey",methods={"POST"})
     */
    public function addSurvey (Request $request,UserRepository $userRepository,SerializerInterface $serializer,EntityManagerInterface $manager){
        $data = $request->getContent();
        try {
            $tokenDecoder = new TokenDecoder($request);
            $roles = $tokenDecoder->getRoles();
            if (!in_array('ROLE_USER',$roles,true)){
                return $this->json([
                    'message' => 'Access Denied !',
                    'status' => 403
                ],403);
            }
            $survey = $serializer->deserialize($data,Survey::class,'json');
            $email = $tokenDecoder->getEmail();
            $user = $userRepository->findOneBy(['email' => $email]);
            $survey->setCreatedBy($user);
            $manager->persist($survey);
            $manager->flush();
            return $this->json([
                'status' => 201,
                'message' => 'Survey created'
            ],201);

        }catch (NotEncodableValueException $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/api/surveys",name="get_surveys",methods={"GET"})
     */
    public function getSurveys (Request $request,SurveyRepository $surveyRepository,SerializerInterface $serializer){
        $data = $request->getContent();
        try {
            $tokenDecoder = new TokenDecoder($request);
            $roles = $tokenDecoder->getRoles();
            if(!in_array('ROLE_USER',$roles,true)){
                return $this->json([
                    'status' => 401,
                    'message' => 'Access denied'
                ],401);
            }
            $surveys = $surveyRepository->findAll();
            return $this->json($surveys,200, [], ['groups' => 'read_survey']);
        }catch (NotEncodableValueException $exception){
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ],400);
        }
    }

    /**
     * @Route("/api/survey/delete/{id}",name="delete",methods={"DELETE"})
     */
    public function deleteSurvey ($id,Request $request,SurveyRepository $surveyRepository,EntityManagerInterface $manager){
        $data = $request->getContent();
        $survey = $surveyRepository->findOneBy(['id' => $id]);
        if(!$survey){
            return $this->json([
                'status' => 404,
                'message' => 'page not found !'
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if(in_array('ROLE_USER',$roles,true) || in_array('ROLE_DOCTOR',$roles,true)){
            return $this->json([
                'status' => 401,
                'message' => 'Access denied'
            ],401);
        }
        $manager->remove($survey);
        $manager->flush();
        return $this->json([
            'status' => 201,
            'message' => 'Survey deleted'
        ],201);
    }
}