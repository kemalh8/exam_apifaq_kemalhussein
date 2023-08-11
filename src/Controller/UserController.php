<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(SerializerInterface $serializer): Response
    {
        $user = $this->getUser();

        return $this->json(json_decode($serializer->serialize($user, 'json')));
    }
}
