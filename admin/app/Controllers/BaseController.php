<?php

namespace App\Controllers;

/**
 * Базовый контроллер для всех контроллеров приложения
 */
class BaseController
{
    /**
     * Рендер view с layout
     * 
     * @param string $view Путь к view относительно app/Views/pages/ (например 'users/index')
     * @param array $data Данные для передачи в view (доступны как переменные)
     * @param string $layout Layout для использования ('main' или 'auth')
     * @return void
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Извлекаем переменные из массива для доступа в шаблонах
        extract($data);
        
        // Получаем контент view через буферизацию вывода
        ob_start();
        $viewPath = APP_PATH . "/Views/pages/{$view}.php";
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$viewPath}");
        }
        
        require $viewPath;
        $content = ob_get_clean();
        
        // Подключаем layout с уже готовым контентом
        $layoutPath = APP_PATH . "/Views/layouts/{$layout}.php";
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layoutPath}");
        }
        
        require $layoutPath;
    }
    
    /**
     * Отправить JSON ответ
     * 
     * @param array $data Данные для JSON
     * @param int $status HTTP статус код
     * @return void
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Редирект на другую страницу
     * 
     * @param string $url URL для редиректа
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Установить flash сообщение
     * 
     * @param string $type Тип сообщения (success, error, warning, info)
     * @param string $message Текст сообщения
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION["flash_{$type}"] = $message;
    }
    
    /**
     * Получить и удалить flash сообщение
     * 
     * @param string $type Тип сообщения
     * @return string|null
     */
    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION["flash_{$type}"] ?? null;
        
        if ($message) {
            unset($_SESSION["flash_{$type}"]);
        }
        
        return $message;
    }
}
