<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OAuthController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile']);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        TokenStorageInterface $tokenStorage
    ): RedirectResponse {
        $client = $clientRegistry->getClient('google');
        
        try {
            // Check if we have the authorization code
            if (!$request->query->has('code')) {
                $this->addFlash('error', 'لم يتم الحصول على رمز التفويض من جوجل');
                return $this->redirectToRoute('app_login');
            }
            
            $accessToken = $client->getAccessToken();
            $googleUser = $client->fetchUserFromToken($accessToken);
            
            $email = $googleUser->getEmail();
            $firstName = $googleUser->getFirstName();
            $lastName = $googleUser->getLastName();
            $googleId = $googleUser->getId();
            $profilePicture = $googleUser->getAvatar();
            
            // Check if user exists
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$existingUser) {
                // Create new user
                $newUser = new User();
                $newUser->setEmail($email);
                $newUser->setFirstName($firstName);
                $newUser->setLastName($lastName);
                $newUser->setGoogleId($googleId);
                $newUser->setIsVerified(true); // Google users are pre-verified
                
                // Generate a random password for OAuth users
                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $passwordHasher->hashPassword($newUser, $randomPassword);
                $newUser->setPassword($hashedPassword);
                
                $em->persist($newUser);
                $em->flush();
                
                $user = $newUser;
            } else {
                // Update existing user with Google data
                if (!$existingUser->getGoogleId()) {
                    $existingUser->setGoogleId($googleId);
                }
                if (!$existingUser->getFirstName() && $firstName) {
                    $existingUser->setFirstName($firstName);
                }
                if (!$existingUser->getLastName() && $lastName) {
                    $existingUser->setLastName($lastName);
                }
                $em->flush();
                
                $user = $existingUser;
            }
            
            // Authenticate the user
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            
            // Store in session for persistence
            $session = $request->getSession();
            $session->set('_security_main', serialize($token));
            $session->save(); // Force session save
            
            return $this->redirectToRoute('app_home');
            
        } catch (IdentityProviderException $e) {
            $this->addFlash('error', 'حدث خطأ أثناء تسجيل الدخول مع جوجل: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        } catch (\KnpU\OAuth2ClientBundle\Exception\InvalidStateException $e) {
            $this->addFlash('error', 'انتهت صلاحية جلسة تسجيل الدخول. يرجى المحاولة مرة أخرى.');
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'حدث خطأ غير متوقع: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/connect/facebook', name: 'connect_facebook_start')]
    public function connectFacebook(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('facebook')
            ->redirect(['email', 'public_profile']);
    }

    #[Route('/connect/facebook/check', name: 'connect_facebook_check')]
    public function connectFacebookCheck(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        TokenStorageInterface $tokenStorage
    ): RedirectResponse {
        $client = $clientRegistry->getClient('facebook');
        
        try {
            $accessToken = $client->getAccessToken();
            $facebookUser = $client->fetchUserFromToken($accessToken);
            
            $email = $facebookUser->getEmail();
            $firstName = $facebookUser->getFirstName();
            $lastName = $facebookUser->getLastName();
            $facebookId = $facebookUser->getId();
            $profilePicture = $facebookUser->getPictureUrl();
            
            // Check if user exists
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$existingUser) {
                // Create new user
                $newUser = new User();
                $newUser->setEmail($email);
                $newUser->setFirstName($firstName);
                $newUser->setLastName($lastName);
                $newUser->setFacebookId($facebookId);
                $newUser->setIsVerified(true); // Facebook users are pre-verified
                
                // Generate a random password for OAuth users
                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $passwordHasher->hashPassword($newUser, $randomPassword);
                $newUser->setPassword($hashedPassword);
                
                $em->persist($newUser);
                $em->flush();
                
                $user = $newUser;
            } else {
                // Update existing user with Facebook data
                if (!$existingUser->getFacebookId()) {
                    $existingUser->setFacebookId($facebookId);
                }
                if (!$existingUser->getFirstName() && $firstName) {
                    $existingUser->setFirstName($firstName);
                }
                if (!$existingUser->getLastName() && $lastName) {
                    $existingUser->setLastName($lastName);
                }
                $em->flush();
                
                $user = $existingUser;
            }
            
            // Authenticate the user
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            
            // Store in session for persistence
            $session = $request->getSession();
            $session->set('_security_main', serialize($token));
            $session->save(); // Force session save
            
            return $this->redirectToRoute('app_home');
            
        } catch (IdentityProviderException $e) {
            $this->addFlash('error', 'حدث خطأ أثناء تسجيل الدخول مع فيسبوك: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }
}