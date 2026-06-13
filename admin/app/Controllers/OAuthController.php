<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;
use App\Config\OAuth as OAuthConfig;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Github;

/**
 * Контролер для OAuth авторизації
 */
class OAuthController extends BaseController
{
    /**
     * Redirect на Google авторизацію
     */
    public function googleRedirect(): void
    {
        if (!OAuthConfig::isGoogleConfigured()) {
            $this->redirectWithError('Google OAuth не налаштовано', '/admin/?route=login');
            return;
        }
        
        $provider = new Google(OAuthConfig::getGoogleConfig());
        
        // Генеруємо authorization URL
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'profile']
        ]);
        
        // Зберігаємо state для захисту від CSRF
        $_SESSION['oauth2state'] = $provider->getState();
        $_SESSION['oauth_provider'] = 'google';
        
        // Redirect на Google
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Callback від Google
     */
    public function googleCallback(): void
    {
        if (!OAuthConfig::isGoogleConfigured()) {
            $this->redirectWithError('Google OAuth не налаштовано', '/admin/?route=login');
            return;
        }
        
        // Перевірка state для захисту від CSRF
        if (empty($_GET['state']) || 
            empty($_SESSION['oauth2state']) || 
            $_GET['state'] !== $_SESSION['oauth2state']) {
            
            unset($_SESSION['oauth2state']);
            $this->redirectWithError('Невалідний state параметр', '/admin/?route=login');
            return;
        }
        
        unset($_SESSION['oauth2state']);
        
        // Перевірка наявності authorization code
        if (empty($_GET['code'])) {
            $this->redirectWithError('Авторизацію скасовано', '/admin/?route=login');
            return;
        }
        
        try {
            $provider = new Google(OAuthConfig::getGoogleConfig());
            
            // Отримуємо access token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            
            // Отримуємо дані користувача
            $resourceOwner = $provider->getResourceOwner($token);
            $userData = $resourceOwner->toArray();
            
            // Обробляємо авторизацію
            $this->handleOAuthLogin(
                'google',
                $userData['sub'], // Google user ID
                $userData['email'],
                $userData['name'] ?? $userData['email'],
                $userData['picture'] ?? null
            );
            
        } catch (\Exception $e) {
            $this->redirectWithError('Помилка Google OAuth: ' . $e->getMessage(), '/admin/?route=login');
        }
    }
    
    /**
     * Redirect на GitHub авторизацію
     */
    public function githubRedirect(): void
    {
        if (!OAuthConfig::isGithubConfigured()) {
            $this->redirectWithError('GitHub OAuth не налаштовано', '/admin/?route=login');
            return;
        }
        
        $provider = new Github(OAuthConfig::getGithubConfig());
        
        // Генеруємо authorization URL
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['user:email', 'read:user']
        ]);
        
        // Зберігаємо state для захисту від CSRF
        $_SESSION['oauth2state'] = $provider->getState();
        $_SESSION['oauth_provider'] = 'github';
        
        // Redirect на GitHub
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Callback від GitHub
     */
    public function githubCallback(): void
    {
        if (!OAuthConfig::isGithubConfigured()) {
            $this->redirectWithError('GitHub OAuth не налаштовано', '/admin/?route=login');
            return;
        }
        
        // Перевірка state для захисту від CSRF
        if (empty($_GET['state']) || 
            empty($_SESSION['oauth2state']) || 
            $_GET['state'] !== $_SESSION['oauth2state']) {
            
            unset($_SESSION['oauth2state']);
            $this->redirectWithError('Невалідний state параметр', '/admin/?route=login');
            return;
        }
        
        unset($_SESSION['oauth2state']);
        
        // Перевірка наявності authorization code
        if (empty($_GET['code'])) {
            $this->redirectWithError('Авторизацію скасовано', '/admin/?route=login');
            return;
        }
        
        try {
            $provider = new Github(OAuthConfig::getGithubConfig());
            
            // Отримуємо access token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            
            // Отримуємо дані користувача
            $resourceOwner = $provider->getResourceOwner($token);
            $userData = $resourceOwner->toArray();
            
            // GitHub може не давати email якщо він приватний
            $email = $userData['email'];
            if (empty($email)) {
                // Отримуємо email через API
                $email = $this->getGithubPrimaryEmail($token->getToken());
            }
            
            if (empty($email)) {
                $this->redirectWithError('Не вдалося отримати email від GitHub', '/admin/?route=login');
                return;
            }
            
            // Обробляємо авторизацію
            $this->handleOAuthLogin(
                'github',
                (string)$userData['id'], // GitHub user ID
                $email,
                $userData['name'] ?? $userData['login'],
                $userData['avatar_url'] ?? null
            );
            
        } catch (\Exception $e) {
            $this->redirectWithError('Помилка GitHub OAuth: ' . $e->getMessage(), '/admin/?route=login');
        }
    }
    
    /**
     * Обробка OAuth логіну
     */
    private function handleOAuthLogin(
        string $provider,
        string $oauthId,
        string $email,
        string $name,
        ?string $avatar
    ): void {
        // Шукаємо користувача за OAuth ID
        $user = User::findByOAuth($provider, $oauthId);
        
        if ($user) {
            // Користувач вже існує - оновлюємо аватар якщо потрібно
            if ($avatar) {
                User::updateOAuthData($user['id'], $avatar);
            }
        } else {
            // Перевіряємо чи існує користувач з таким email
            $existingUser = User::findByEmail($email);
            
            if ($existingUser) {
                // Прив'язуємо OAuth до існуючого користувача
                User::linkOAuth($existingUser['id'], $provider, $oauthId, $avatar);
                $user = User::findById($existingUser['id']);
            } else {
                // Створюємо нового користувача
                $user = User::createFromOAuth($provider, $oauthId, $email, $name, $avatar);
            }
        }
        
        if (!$user) {
            $this->redirectWithError('Не вдалося створити користувача', '/admin/?route=login');
            return;
        }
        
        // Авторизуємо користувача
        Auth::loginById($user['id']);
        
        // Redirect залежно від ролі
        $redirectUrl = Auth::isAdmin() ? '/admin/' : '/admin/?route=profile';
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Отримати primary email з GitHub API
     */
    private function getGithubPrimaryEmail(string $accessToken): ?string
    {
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: AdminOrder-Dashboard',
                'Accept: application/vnd.github.v3+json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        $emails = json_decode($response, true);
        
        if (!is_array($emails)) {
            return null;
        }
        
        // Шукаємо primary email
        foreach ($emails as $emailData) {
            if ($emailData['primary'] ?? false) {
                return $emailData['email'];
            }
        }
        
        // Повертаємо перший доступний
        return $emails[0]['email'] ?? null;
    }
    
    /**
     * Redirect з повідомленням про помилку
     */
    private function redirectWithError(string $message, string $url): void
    {
        $_SESSION['error'] = $message;
        header('Location: ' . $url);
        exit;
    }
}
