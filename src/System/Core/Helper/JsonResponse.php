<?php
/**
 * Created by PhpStorm.
 * User: levent
 * Date: 03.02.19
 * Time: 14:54
 */

namespace App\System\Core\Helper;


class JsonResponse
{
    /**
     * @param bool $success
     * @param mixed $content
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public static function return($success, $content = '')
    {
        return \Symfony\Component\HttpFoundation\JsonResponse::create([
            'success' => (bool)$success,
            'content' => $content
        ]);
    }
}