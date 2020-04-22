<?php


namespace App\Controller\Api;


use App\Repository\AnswerRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use App\Services\TokenDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;


class AnswerController extends AbstractController
{
    /**
     * @Route("/api/answer",name="getAnswer",methods={"GET"})
     */
    public function getAnswers (Request $request,SurveyRepository $surveyRepository,UserRepository $userRepository,SerializerInterface $serializer,AnswerRepository $answerRepository):JsonResponse
    {
        $data = $request->getContent();
        try {
            $tokenDecoder = new TokenDecoder($request);
            $roles = $tokenDecoder->getRoles();
            if (!in_array('ROLE_USER', $roles, true)) {
                return $this->json([
                    'message' => 'Access Denied !',
                    'status' => 403
                ], 403);
            }
            $user = $userRepository->findOneBy(['email' => $tokenDecoder->getEmail()]);
            $surveys = $surveyRepository->findBy(['createdBy' => $user]);
            if(!$surveys){
                return $this->json([],204);
            }
            $answers = $answerRepository->findBy(['survey' => $surveys]);

            return $this->json($answers, 200,[],['groups' => 'readAnswer']);

        } catch (NotEncodableValueException $exception) {
            return $this->json([
                'status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }
    }
}