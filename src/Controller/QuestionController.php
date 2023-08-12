<?php

namespace App\Controller;

use App\Entity\Question;
use Doctrine\ORM\EntityManager;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
class QuestionController extends AbstractController
{  
     private $serializer;
     private $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {   
        $this->serializer = $serializer;

        $this->entityManager = $entityManager;
    
        
    }
    #[Route('/questions', name: 'app_question', methods:['GET'])]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();
       
        $serializedObject = json_decode($this->serializer->serialize($questions, 'json'));


        return $this->json($serializedObject);
    }

    #[Route('/questions/{question}', name: 'question_one', methods:['GET'])]
    public function getOne(Question $question): Response
    {
        $serializedObject = json_decode($this->serializer->serialize($question, 'json'));

        return $this->json($serializedObject);
    }


         #[IsGranted('ROLE_ADMIN')]
    #[Route('/questions/{question}', name: 'question_delete', methods:['DELETE'])]
    public function deleteOne(Question $question, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($question);
        $entityManager->flush();

        $response = new Response();
        $response->setStatusCode(204);
        return $response;
    }

    #[Route('/questions', name: 'qpp_question', methods:['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
       $objectRequest = json_decode($request->getContent());
       if(!isset($objectRequest->title) || !isset($objectRequest->description) || !isset($objectRequest->score)){
        $response = new JsonResponse([
            'success'=> false,
            'message'=> 'The title, desccription or score is missing in the request'
        ]);
        $response->setStatusCode(400);
       } 
        else{
            $question = new Question();
            $question->setTitle($objectRequest->title);
            $question->setDescription($objectRequest->description);
            $question->setScore($objectRequest->score);

            $form = $this->createForm(QuestionType::class, $question);
            $form->submit($objectRequest);

            if($form->isValid()){
                $entityManager->persist($question);
                $entityManager->flush();

               $retour = json_decode($this->serializer->serialize($question, 'json'));
              
               $response = $this->json($retour);
               $response->setStatusCode(201);
               return $response;

            } 
            else{

                $response = $this->json([
                    'success' => false,
                    'message' => 'Bad request'
                ]);

                $response->setStatusCode(400);

                return $response;
            }
       }  
    }


    #[Route('/up/{id}', name: 'increment_score', methods: ['PATCH'])]
    public function incrementScore(Question $question): JsonResponse
    {
        $question->setScore($question->getScore() + 1);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $this->json(['message' => 'Score incremented successfully']);
    }


    
    #[Route('/down/{id}', name: 'decrement_score', methods: ['PATCH'])]
    public function decrementScore(Question $question): JsonResponse
    {
       if($question->getScore() > 0){
            $question->setScore($question->getScore() - 1);

            $this->entityManager->persist($question);
            $this->entityManager->flush();

            return $this->json(['message' => 'Score decremented successfully']);
       } 
       else {
            return $this->json(['message' => 'Score cannot go below zero'], 400);
       }
    } 

        #[IsGranted('ROLE_ADMIN')]
    #[Route('/questions/{question}', name: 'app_question_update', methods:['PUT'])]
    public function update(Question $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        $objectRequest = json_decode($request->getContent());
        
        $form = $this->createForm(QuestionType::class, $question);
        /* //Handle the form submission only if the request is valid
            $form->submit((array) $objectRequest);
            if ($form->isSubmitted() && $form->isValid)
            {
                $entityManager->flush();
               // Serialize the updated question and return a JsonResponse
                $questionSerialized = $this->serializer->serialize($question, 'json');
                return new JsonResponse($questionSerialized);
            } 
            else {
                    $response = $this->json([
                        'success' => false,
                        'message' => 'Bad request'
                    ]);
                $response->setStatusCode(400);
                return $response;
            }
        */
        $form->submit($objectRequest);

        if($form->isValid()){
            $entityManager->flush();

            $question = $form->getData();
            $questionSerialized = json_encode($this->serializer->serialize($question, 'json'));
            
            return new JsonResponse($questionSerialized);

        } 
        else{
            $response = $this->json([
                'success' => false,
                'message' => 'Bad request'
            ]);

            $response->setStatusCode(400);

            return $response;
        }
    }
}
