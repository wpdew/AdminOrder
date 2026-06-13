<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

/**
 * Контроллер для авторизации
 */
class AuthController extends BaseController
{
    /**
     * Определить куда редиректить пользователя после входа
     */
    private function getRedirectAfterLogin(): string
    {
        return Auth::isAdmin() ? '/admin/' : '/admin/?route=profile';
    }
    
    /**
     * Показать страницу входа
     */
    public function showLogin(): void
    {
        // Если уже залогинен, редирект на главную
        if (Auth::check()) {
            $this->redirect($this->getRedirectAfterLogin());
        }
        
        $this->render('auth/login', [
            'title' => 'Вход',
            'error' => $_SESSION['error'] ?? null,
            'email' => $_SESSION['email'] ?? ''
        ], 'auth');
        
        unset($_SESSION['error']);
        unset($_SESSION['email']);
    }
    
    /**
     * Обработка формы входа
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Валидация
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Заполните все поля';
            $_SESSION['email'] = $email;
            $this->redirect('/admin/?route=login');
        }
        
        // Попытка входа
        if (Auth::login($email, $password)) {
            $this->redirect($this->getRedirectAfterLogin());
        } else {
            $_SESSION['error'] = 'Неверный email или пароль';
            $_SESSION['email'] = $email;
            $this->redirect('/admin/?route=login');
        }
    }
    
    /**
     * Показать страницу регистрации
     */
    public function showRegister(): void
    {
        // Если уже залогинен, редирект на главную
        if (Auth::check()) {
            $this->redirect($this->getRedirectAfterLogin());
        }
        
        $this->render('auth/register', [
            'title' => 'Регистрация',
            'error' => $_SESSION['error'] ?? null,
            'name' => $_SESSION['name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'nickname' => $_SESSION['nickname'] ?? ''
        ], 'auth');
        
        unset($_SESSION['error']);
        unset($_SESSION['name']);
        unset($_SESSION['email']);
        unset($_SESSION['nickname']);
    }
    
    /**
     * Обработка регистрации
     */
    public function register(): void
    {
        $email = $_POST['email'] ?? '';
        $nickname = $_POST['nickname'] ?? '';
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        
        // Сохраняем для возврата в форму
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['nickname'] = $nickname;
        
        // Валидация
        if (empty($email) || empty($password) || empty($nickname)) {
            $_SESSION['error'] = 'Заполните все обязательные поля';
            $this->redirect('/admin/?route=register');
        }
        
        // Проверка email формата
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Неверный формат email';
            $this->redirect('/admin/?route=register');
        }
        
        // Проверка длины пароля
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Пароль должен быть минимум 6 символов';
            $this->redirect('/admin/?route=register');
        }
        
        // Создание пользователя
        if (User::create($email, $nickname, $password, $name, 'user', 'en')) {
            // Очищаем сохраненные данные
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            unset($_SESSION['nickname']);
            
            $_SESSION['flash_success'] = 'Регистрация успешна! Войдите в систему';
            $this->redirect('/admin/?route=login');
        } else {
            $_SESSION['error'] = 'Email или nickname уже используется';
            $this->redirect('/admin/?route=register');
        }
    }
    
    /**
     * Выход из системы
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/admin/?route=login');
    }
}
