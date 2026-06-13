<?php

namespace App\Controllers;

use App\Core\Auth;

/**
 * Контроллер служебных страниц доступа
 */
class AccessController extends BaseController
{
    /**
     * Страница ошибки доступа
     */
    public function denied(): void
    {
        Auth::requireAuth();

        $this->render('errors/access-denied', [
            'title' => 'Доступ запрещен',
            'activeMenu' => 'profile'
        ]);
    }
}
