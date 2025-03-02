<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class ApiKeyController extends AbstractController
{

//    public function __construct(
//         #[Autowire('%app.api_key%')]
//         private readonly string $apiKey
//    )
//    {
//
//    }

    #[Route('/api/key', name: 'app_api_key', methods: ['GET'])]
    public function getApiKey(): JsonResponse
    {
        return new JsonResponse([
            'api_key' => $this->getParameter('app.api_key')
        ]);
    }
}
