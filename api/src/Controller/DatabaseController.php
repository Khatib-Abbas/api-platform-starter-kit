<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DatabaseController extends AbstractController
{
    #[Route('/api/database/ssl-status', name: 'app_database_ssl_status', methods: ['GET'])]
    public function checkSSLStatus(Connection $connection): JsonResponse
    {
        try {
            // Vérification basique de la connexion SSL
            $hasSSL = $connection->executeQuery("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->fetchAssociative();

            // Informations détaillées sur SSL
            $sslInfo = [
                // Vérification du chiffrement utilisé
                'cipher' => $connection->executeQuery("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->fetchAssociative(),
                // Version SSL/TLS utilisée
                'version' => $connection->executeQuery("SHOW SESSION STATUS LIKE 'Ssl_version'")->fetchAssociative(),
                // Vérification si le certificat est accepté
                'verify' => $connection->executeQuery("SHOW SESSION STATUS LIKE 'Ssl_verify_mode'")->fetchAssociative(),
            ];

            // Configuration actuelle de SSL dans MySQL
            $sslVariables = $connection->executeQuery("SHOW VARIABLES LIKE '%ssl%'")->fetchAllAssociative();

            // Paramètres de connexion (sans les informations sensibles)
            $params = $connection->getParams();
            unset($params['password'], $params['url']);

            return new JsonResponse([
                'ssl_active' => !empty($hasSSL['Value']),
                'connection_details' => [
                    'database' => $connection->getDatabase(),
                    'connected' => true,
                    'parameters' => $params
                ],
                'ssl_details' => $sslInfo,
                'ssl_configuration' => $sslVariables
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'connected' => false
            ], 500);
        }
    }
}
