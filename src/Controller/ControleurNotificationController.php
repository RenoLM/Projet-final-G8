<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Email; // Assurez-vous d'avoir l'entité Email
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class ControleurNotificationController extends AbstractController
{
    #[Route('/controleur/notification', name: 'app_controleur_notification')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ControleurNotificationController.php',
        ]);
    }

    #[Route('/send-notification', name: 'send_notification', methods: ['POST'])]
    public function sendNotification(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): JsonResponse
    {
        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données ont été décodées correctement
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier que les champs requis sont présents
        if (!isset($data['recipient'], $data['subject'], $data['message'])) {
            return new JsonResponse(['error' => 'Missing data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Extraire les détails de l'email des données JSON
        $recipient = $data['recipient'];
        $subject = $data['subject'];
        $message = $data['message'];

        // Envoyer l'email
        $email = (new SymfonyEmail())
            ->from('rlemoulec1@myges.fr') // Changez ceci à votre email d'expéditeur
            ->to($recipient)
            ->subject($subject)
            ->text($message);

        $mailer->send($email);

        // Enregistrer les détails de l'email dans la base de données
        $emailEntity = new Email();
        $emailEntity->setRecipient($recipient);
        $emailEntity->setSubject($subject);
        $emailEntity->setMessage($message);
        $emailEntity->setSentAt(new \DateTime());

        $entityManager->persist($emailEntity);
        $entityManager->flush();

        // Retourner une réponse JSON
        return new JsonResponse(['status' => 'Notification sent and saved!'], JsonResponse::HTTP_CREATED);
    }
}


