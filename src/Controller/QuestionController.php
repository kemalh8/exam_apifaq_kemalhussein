<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
class QuestionController extends AbstractController
{  
     private $serializer;

    public function __construct(SerializerInterface $serializer)
    {   
        $this->serializer = $serializer;
        
    }
    #[Route('/questions', name: 'app_question', methods:['GET'])]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();
       
        $serializedObject = json_decode($this->serializer->serialize($questions, 'json'));


        return $this->json($serializedObject);
    }

    #[Route('/questions{question}', name: 'question_one', methods:['GET'])]
    public function getOne(Question $question): Response
    {
        $serializedObject = json_decode($this->serializer->serialize($question, 'json'));

        return $this->json($serializedObject);
    }

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

    #[Route('/questions/{question}', name: 'app_question_update', methods:['PUT'])]
    public function update(Question $question, Request $request, EntityManagerInterface $entityManager): Response
    {
        $objectRequest = json_decode($request->getContent());
        
        $form = $this->createForm(QuestionType::class, $question);
        
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
