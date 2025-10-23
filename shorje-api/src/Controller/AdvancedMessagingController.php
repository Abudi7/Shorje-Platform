<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api/advanced-messaging')]
#[IsGranted('ROLE_USER')]
class AdvancedMessagingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $slugger
    ) {}

    #[Route('/send-file', name: 'api_send_file', methods: ['POST'])]
    public function sendFile(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $receiverId = $request->request->get('receiverId');
        $messageText = $request->request->get('message', '');
        
        if (!$receiverId) {
            return $this->json(['error' => 'Receiver ID is required'], 400);
        }

        $receiver = $this->em->getRepository(User::class)->find($receiverId);
        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile instanceof UploadedFile) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return $this->json(['error' => 'File type not allowed'], 400);
        }

        if ($uploadedFile->getSize() > $maxSize) {
            return $this->json(['error' => 'File too large'], 400);
        }

        // Generate unique filename
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // Move file to uploads directory
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/messages';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFile->move($uploadDir, $newFilename);

        // Create message with file attachment
        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent($messageText);
        $message->setAttachmentName($uploadedFile->getClientOriginalName());
        $message->setAttachmentMimeType($uploadedFile->getMimeType());
        $message->setAttachmentPath('/uploads/messages/'.$newFilename);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'File sent successfully',
            'messageId' => $message->getId(),
            'file' => [
                'name' => $uploadedFile->getClientOriginalName(),
                'type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'path' => '/uploads/messages/'.$newFilename
            ]
        ]);
    }

    #[Route('/send-voice', name: 'api_send_voice', methods: ['POST'])]
    public function sendVoiceMessage(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $receiverId = $request->request->get('receiverId');
        $duration = $request->request->get('duration', 0);
        
        if (!$receiverId) {
            return $this->json(['error' => 'Receiver ID is required'], 400);
        }

        $receiver = $this->em->getRepository(User::class)->find($receiverId);
        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        $uploadedFile = $request->files->get('voice');
        if (!$uploadedFile instanceof UploadedFile) {
            return $this->json(['error' => 'No voice file uploaded'], 400);
        }

        // Validate voice file
        $allowedTypes = ['audio/webm', 'audio/mp4', 'audio/wav', 'audio/ogg'];
        $maxSize = 25 * 1024 * 1024; // 25MB for voice

        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return $this->json(['error' => 'Voice file type not allowed'], 400);
        }

        if ($uploadedFile->getSize() > $maxSize) {
            return $this->json(['error' => 'Voice file too large'], 400);
        }

        // Generate unique filename
        $newFilename = 'voice-'.uniqid().'.'.$uploadedFile->guessExtension();

        // Move file to uploads directory
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/voice';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFile->move($uploadDir, $newFilename);

        // Create message with voice attachment
        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent('🎤 Voice message');
        $message->setAttachmentName('Voice message');
        $message->setAttachmentMimeType($uploadedFile->getMimeType());
        $message->setAttachmentPath('/uploads/voice/'.$newFilename);
        $message->setIsVoice(true);
        $message->setVoiceDuration($duration);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Voice message sent successfully',
            'messageId' => $message->getId(),
            'voice' => [
                'duration' => $duration,
                'type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'path' => '/uploads/voice/'.$newFilename
            ]
        ]);
    }

    #[Route('/start-video-call', name: 'api_start_video_call', methods: ['POST'])]
    public function startVideoCall(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $receiverId = $request->request->get('receiverId');
        
        if (!$receiverId) {
            return $this->json(['error' => 'Receiver ID is required'], 400);
        }

        $receiver = $this->em->getRepository(User::class)->find($receiverId);
        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        // Generate unique call ID
        $callId = 'call_'.uniqid();
        
        // Create call notification
        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent('📹 Video call invitation');
        $message->setIsVideoCall(true);
        $message->setCallId($callId);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'callId' => $callId,
            'message' => 'Video call initiated',
            'receiver' => [
                'id' => $receiver->getId(),
                'name' => $receiver->getFullName(),
                'avatar' => $receiver->getAvatarUrl()
            ]
        ]);
    }

    #[Route('/end-video-call', name: 'api_end_video_call', methods: ['POST'])]
    public function endVideoCall(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $callId = $data['callId'] ?? null;
        
        if (!$callId) {
            return $this->json(['error' => 'Call ID is required'], 400);
        }

        // Find and update call message
        $message = $this->em->getRepository(Message::class)->findOneBy(['callId' => $callId]);
        if ($message) {
            $message->setContent('📹 Video call ended');
            $message->setIsVideoCall(false);
            $this->em->flush();
        }

        return $this->json([
            'success' => true,
            'message' => 'Video call ended'
        ]);
    }

    #[Route('/get-messages/{userId}', name: 'api_get_messages', methods: ['GET'])]
    public function getMessages(int $userId, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        $messages = $this->em->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->where('(m.sender = :currentUser AND m.receiver = :otherUser) OR (m.sender = :otherUser AND m.receiver = :currentUser)')
            ->setParameter('currentUser', $currentUser)
            ->setParameter('otherUser', $userId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        $formattedMessages = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'name' => $message->getSender()->getFullName(),
                    'avatar' => $message->getSender()->getAvatarUrl()
                ],
                'receiver' => [
                    'id' => $message->getReceiver()->getId(),
                    'name' => $message->getReceiver()->getFullName(),
                    'avatar' => $message->getReceiver()->getAvatarUrl()
                ],
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'isRead' => $message->getIsRead(),
                'attachment' => $message->getAttachmentPath() ? [
                    'name' => $message->getAttachmentName(),
                    'type' => $message->getAttachmentMimeType(),
                    'path' => $message->getAttachmentPath()
                ] : null,
                'isVoice' => $message->getIsVoice(),
                'voiceDuration' => $message->getVoiceDuration(),
                'isVideoCall' => $message->getIsVideoCall(),
                'callId' => $message->getCallId()
            ];
        }, $messages);

        return $this->json([
            'messages' => $formattedMessages,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($formattedMessages)
            ]
        ]);
    }
}
