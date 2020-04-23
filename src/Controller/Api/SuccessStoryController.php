<?php


namespace App\Controller\Api;


use App\Entity\SuccessStory;
use App\Repository\SuccessStoryRepository;
use App\Repository\UserRepository;
use App\Services\TokenDecoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SuccessStoryController extends AbstractController
{
    /**
     * @Route("/api/successstory",name="add_success_story",methods={"POST"})
     */
    public function addSuccessStory (Request $request,UserRepository $userRepository,ValidatorInterface $validator,SerializerInterface $serializer,EntityManagerInterface $entityManager): JsonResponse
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
            if(!$user){
                return $this->json([
                    'status' => 400,
                    'message' => 'User not found'
                ],400);
            }
            $successStory = $serializer->deserialize($data,SuccessStory::class,'json');
            $errors = $validator->validate($successStory);
            if (count($errors) > 0){
                return $this->json($errors,400);
            }
            $successStory->setCreatedBy($user);
            $successStory->setCreatedAt(new \DateTime());
            $successStory->setAccepted(false);
            $entityManager->persist($successStory);
            $entityManager->flush();

            return $this->json([
                'message' => 'Story created',
                'status' => 201
            ], 201);

        }catch (NotEncodableValueException $exception){
            return $this->json(['status' => 400,
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/api/successstories",name="get_success_stories",methods={"GET"})
     */
    public function getSuccessStories(Request $request,SuccessStoryRepository $storyRepository): JsonResponse
    {
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if(!in_array('ROLE_DOCTOR',$roles,true) && !in_array('ROLE_SUPER_ADMIN',$roles,true)){
            return $this->json([
                'status' => 401,
            'message' => 'Access Denied !'
            ],401);
        }
        return $this->json($storyRepository->findAll(),200,[],['groups' => ['read_story']]);
    }

    /**
     * @Route("/api/successstories/accepted",name="get_success_stories_accepted",methods={"GET"})
     */
    public function getSuccessStoriesAccepted(SuccessStoryRepository $storyRepository,SerializerInterface $serializer): Response
    {
        $stories = $storyRepository->findBy(['accepted' => true]);
        $storiesJson = $serializer->serialize($stories,'json',['groups' => 'read_story',AbstractNormalizer::IGNORED_ATTRIBUTES => ['accepted']]);
        $response = new Response();
        $response->setContent($storiesJson);
        $response->headers->set('Content-type','application/json');
        return $response;
    }

    /**
     * @Route("/api/successstory/accepting/{id}",name="accepting_story",methods={"PATCH"})
     */
    public function acceptingStory ($id,Request $request,SuccessStoryRepository $storyRepository,EntityManagerInterface $entityManager):JsonResponse
    {
        $story = $storyRepository->find($id);
        if(!$story){
            return $this->json([
                'message' => 'Story not found !',
                'status' => 404
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if(!in_array('ROLE_DOCTOR',$roles,true)){
            return $this->json([
                'status' => 401,
                'message' => 'Access Denied !'
            ],401);
        }
        $story->setAccepted(true);
        $entityManager->flush();

        return $this->json([
           'message' => 'Story accepted',
           'status' => 201
        ],201);
    }

    /**
     * @Route("/api/successstory/delete/{id}",name="delete_story",methods={"DELETE"})
     */
    public function deleteStory ($id,Request $request,UserRepository $userRepository,SuccessStoryRepository $storyRepository,EntityManagerInterface $entityManager):JsonResponse
    {
        $story = $storyRepository->find($id);
        if(!$story){
            return $this->json([
                'message' => 'Story not found !',
                'status' => 404
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        $userToken = $userRepository->findOneBy(['email' => $tokenDecoder->getEmail()]);
        if ($userToken !== $story->getCreatedBy() && !in_array('ROLE_DOCTOR', $roles, true) && !in_array('ROLE_SUPER_ADMIN', $roles, true))
        {
            return $this->json([
                'status' => 401,
                'message' => 'Access Denied !'
            ],401);
        }

        $entityManager->remove($story);
        $entityManager->flush();

        return $this->json([
            'message' => 'Story deleted',
            'status' => 201
        ],201);
    }

    /**
     * @Route("/api/successstory/accepted/{id}",name="get_success_story",methods={"GET"})
     * @param SuccessStory|null $successStory
     * @return JsonResponse
     */
    public function getSuccessStory (SuccessStory $successStory = null):JsonResponse
    {
        if(!$successStory || !$successStory->getAccepted()){
            return $this->json([
                'message' => 'Story not found !',
                'status' => 404
            ],404);
        }
        return $this->json($successStory,200,[],['groups' => 'read_story']);
    }

    /**
     * @Route("/api/successstory/{id}",name="get_success_story",methods={"GET"})
     * @param SuccessStory|null $successStory
     * @return JsonResponse
     */
    public function getSuccessStoryForAdmin (SuccessStory $successStory = null, Request $request):JsonResponse
    {
        if(!$successStory){
            return $this->json([
                'message' => 'Story not found !',
                'status' => 404
            ],404);
        }
        $tokenDecoder = new TokenDecoder($request);
        $roles = $tokenDecoder->getRoles();
        if(!in_array('ROLE_DOCTOR',$roles,true) && !in_array('ROLE_SUPER_ADMIN',$roles,true)){
            return $this->json([
                'message' => 'Access Denied !',
                'status' => 403
            ],403);
        }
        return $this->json($successStory,200,[],['groups' => 'read_story']);
    }
}