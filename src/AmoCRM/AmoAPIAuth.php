<?php
/**
 * Трейт AmoAPIAuth. Содержит методы для авторизации по API-ключу пользователя
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.2
 *
 * v1.0.0 (07.04.2020) Начальный релиз
 * v1.0.1 (25.05.2020) Исправлено отладочное сообщение
 * v1.0.2 (08.07.2020) Отключен trigger_error
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIAuth
{
    /**
     * Таймаут перед повторной авторизацией по API-ключу пользователя при ошибке '401 Unauthorized', секунд
     * @var integer
     */
    public static $reAuthTimeout = 5; // Секунды

    /**
     * Максимальное число попыток повторной авторизации по API-ключу пользователя при ответе '401 Unauthorized'
     * @var int
     */
    public static $reAuthAttempts = 3;

    /**
     * Каталог для хранения файлов cookie относительно каталога файла класса AmoAPI (на конце /)
     * @var string
     */
    public static $cookieFileDir = 'cookies/';

    /**
     * Счетчик попыток повторной авторизации по API ключу пользователя при ответе '401 Unauthorized'
     */
    protected static $reAuthCounter = 0;

    /**
     * Выполняет авторизацию по API-ключу пользователя
     * @param string $login Логи пользователя
     * @param string $hash API-ключ пользователя
     * @param string $subdomain Поддомен amoCRM
     * @return array
     */
    public static function auth(string $login, string $hash, string $subdomain) :array
    {
        // Сохраняем поддомен, использованный при последней авторизации
        self::$lastSubdomain = $subdomain;

        // Сохраняем данные, использованные при последней авторизации для поддомена
        self::$lastAuth[ $subdomain ] = [
            'is_oauth2' => false,
            'login'     => $login,
            'hash'      => $hash
        ];

        // Параметры авторизации
        $params = [
            'USER_LOGIN' => $login,
            'USER_HASH'  => $hash
        ];

        // Запрос авторизации
        $response = self::request('/private/api/auth.php?type=json', 'POST', $params, $subdomain);

        // Проверка ответа от сервера
        if (! isset($response['response']['auth']) || ! $response['response']['auth']) {
            throw new AmoAPIException(
                "Не удалось выполнить авторизацию по API-ключу: " . self::unescapeUnicode(self::$lastResult)
            );
        }

        return $response;
    }

    /**
     * Выполняет, при необходимости, повторную авторизации по API-ключу пользователя,
     * когда код статуса HTTP 401 (401 Unauthorized) в ответе на любые запросы,
     * кроме самих запросов авторизации по API-ключу пользователя
     * @return array|true
     */
    protected static function reAuth()
    {
        // Извлекаем параметры последнего запроса и авторизации, ответ сервера
        $lastRequest = self::$lastRequest;
        $lastResult = self::unescapeUnicode(self::$lastResult);
        $lastAuth = self::$lastAuth[ $lastRequest['subdomain'] ];

        // Выходим, если это запрос авторизации по API-ключу пользователя
        if (stripos($lastRequest['query'], '/private/api/auth.php') !== false) {
            return true;
        }

        // Увеличиваем счетчик числа повторных запросов авторизации
        self::$reAuthCounter++;

        // Проверяем счетчик попыток повторной авторизации
        if (self::$reAuthCounter > self::$reAuthAttempts) {
            return true;
        }

        // trigger_error(
        //     "Повторная авторизация по API-ключу #" . self::$reAuthCounter .
        //         " (" . $lastRequest['subdomain'] . ") при ответе '401 Unauthorized': {$lastResult}",
        //     E_USER_NOTICE
        // );

        // Отладочная информация
        self::debug('['. self::$requestCounter . "] RE AUTH (" . self::$reAuthTimeout . 's)');

        // Выдерживаем таймаут перед повторным запросом
        sleep(self::$reAuthTimeout);

        // Выполняем повторную авторизацию
        self::auth(
            $lastAuth['login'],
            $lastAuth['hash'],
            $lastRequest['subdomain']
        );

        // Выполняем повторный запрос
        $response = self::request(
            $lastRequest['query'],
            $lastRequest['type'],
            $lastRequest['params'],
            $lastRequest['subdomain']
        );

        // Сбрасываем счетчик числа повторных запросов авторизации
        self::$reAuthCounter = 0;

        // Возвращаем результат повторного запроса
        return $response;
    }
}
