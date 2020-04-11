<?php


namespace App\Controller;


use App\Entity\Survey;
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
}