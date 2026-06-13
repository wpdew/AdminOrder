<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Auth;

/**
 * Контроллер для управления пользователями
 */
class UserController extends BaseController
{
    /**
     * Список всех пользователей
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $users = User::all();
        
        $this->render('users/index', [
            'title' => 'Пользователи',
            'activeMenu' => 'users',
            'users' => $users
        ]);
    }
    
    /**
     * Профиль текущего пользователя
     */
    public function profile(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $user = User::findById($userId);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Пользователь не найден';
            $this->redirect('/admin/');
        }
        
        $this->render('users/profile', [
            'title' => 'Профиль',
            'activeMenu' => 'profile',
            'user' => $user
        ]);
    }
    
    /**
     * Обновление профиля текущего пользователя
     */
    public function updateProfile(): void
    {
        Auth::requireAuth();

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/?route=profile');
            return;
        }
        
        $userId = Auth::id();
        
        $email = trim($_POST['email'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $interfaceLang = strtolower(trim((string)($_POST['interface_lang'] ?? 'en')));

        if (!in_array($interfaceLang, User::ALLOWED_INTERFACE_LANGS, true)) {
            $interfaceLang = 'en';
        }
        
        // Валидация
        if (empty($email) || empty($nickname) || empty($name)) {
            $_SESSION['flash_error'] = 'Заполните все обязательные поля';
            $this->redirect('/admin/?route=profile');
            return;
        }
        
        // Проверка email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Неверный формат email';
            $this->redirect('/admin/?route=profile');
            return;
        }
        
        // Обновление
        $success = User::update($userId, $email, $nickname, $name, !empty($password) ? $password : null, $interfaceLang);
        
        if ($success) {
            $_SESSION['flash_success'] = 'Профиль успешно обновлен';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['lang'] = $interfaceLang;
        } else {
            $_SESSION['flash_error'] = 'Ошибка обновления. Возможно email или nickname уже используется';
        }
        
        $this->redirect('/admin/?route=profile');
    }
    
    /**
     * Создать нового пользователя (API)
     */
    public function create(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Invalid request method'], 405);
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $interfaceLang = strtolower(trim((string)($_POST['interface_lang'] ?? 'en')));

        if (!in_array($interfaceLang, User::ALLOWED_INTERFACE_LANGS, true)) {
            $interfaceLang = 'en';
        }
        
        // Валидация
        if (empty($email) || empty($nickname) || empty($name) || empty($password)) {
            $this->json(['success' => false, 'error' => 'Заполните все поля']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Неверный формат email']);
            return;
        }
        
        if (strlen($password) < 6) {
            $this->json(['success' => false, 'error' => 'Пароль должен быть минимум 6 символов']);
            return;
        }
        
        // Создание
        $success = User::create($email, $nickname, $password, $name, 'user', $interfaceLang);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Пользователь успешно создан']);
        } else {
            $this->json(['success' => false, 'error' => 'Email или nickname уже используется']);
        }
    }
    
    /**
     * Обновить пользователя (API)
     */
    public function update(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Invalid request method'], 405);
            return;
        }
        
        $id = intval($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $interfaceLang = strtolower(trim((string)($_POST['interface_lang'] ?? 'en')));

        if (!in_array($interfaceLang, User::ALLOWED_INTERFACE_LANGS, true)) {
            $interfaceLang = 'en';
        }
        
        // Валидация
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'Неверный ID пользователя']);
            return;
        }
        
        if (empty($email) || empty($nickname) || empty($name)) {
            $this->json(['success' => false, 'error' => 'Заполните все обязательные поля']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Неверный формат email']);
            return;
        }
        
        if (!empty($password) && strlen($password) < 6) {
            $this->json(['success' => false, 'error' => 'Пароль должен быть минимум 6 символов']);
            return;
        }
        
        // Обновление
        $success = User::update($id, $email, $nickname, $name, !empty($password) ? $password : null, $interfaceLang);
        
        if ($success) {
            if ($id === Auth::id()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['lang'] = $interfaceLang;
            }

            $this->json(['success' => true, 'message' => 'Пользователь успешно обновлен']);
        } else {
            $this->json(['success' => false, 'error' => 'Ошибка обновления. Возможно email или nickname уже используется']);
        }
    }
    
    /**
     * Удалить пользователя (API)
     */
    public function delete(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Invalid request method'], 405);
            return;
        }
        
        $id = intval($_POST['id'] ?? 0);
        
        // Валидация
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'Неверный ID пользователя']);
            return;
        }
        
        // Нельзя удалить самого себя
        if ($id === Auth::id()) {
            $this->json(['success' => false, 'error' => 'Нельзя удалить собственный аккаунт']);
            return;
        }
        
        // Удаление
        $success = User::deleteById($id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Пользователь успешно удален']);
        } else {
            $this->json(['success' => false, 'error' => 'Ошибка удаления пользователя']);
        }
    }
}
