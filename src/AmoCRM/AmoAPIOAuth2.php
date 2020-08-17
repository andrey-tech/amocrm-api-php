<?php
/**
 * Трейт AmoAPIOAuth2. Содержит методы для авторизации по протоколу OAuth 2.0
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.4.2
 *
 * v1.0.0 (06.04.2020) Начальный релиз.
 * v1.1.0 (10.04.2020) Изменен алгоритм работы метода oAuth2()
 * v1.1.1 (14.04.2020) Метод loadTokens() теперь публичный
 * v1.2.0 (19.04.2020) Изменен метод oAuth2(): добавлен параметр storeAll
 * v1.2.1 (25.05.2020) Исправлено отладочное сообщение
 * v1.3.0 (08.07.2020) Изменен принцип хранения токенов. Отключен trigger_error
 * v1.4.0 (10.07.2020) Добавлено сохранение параметров авторизации вместе с токенами
 * v1.4.1 (11.07.2020) Исправлен баг в методе reOAuth2()
 * v1.4.2 (17.08.2020) Добавлен use AmoCRM\TokenStorage\FileStorage
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

use AmoCRM\TokenStorage\FileStorage;

trait AmoAPIOAuth2
{
    /**
     * Объект класса для сохранения токенов oAuth 2.0
     * @var object
     */
    public static $tokenStorage;

    /**
     * Счетчик попыток обновления access token OAuth 2.0 при ответе '401 Unauthorized'
     */
    protected static $reOAuth2Counter = 0;

    /**
     * Выполняет авторизацию в amoCRM по протоколу OAuth 2.0
     * @param  string $subdomain    Поддомен amoCRM
     * @param  ?string $clientId     ID интеграции
     * @param  ?string $clientSecret Секрет интеграции
     * @param  ?string $redirectUri  Redirect URI
     * @param  ?string $authCode    Код авторизации
     * @return array
     */
    public static function oAuth2(
        string $subdomain,
        string $clientId = null,
        string $clientSecret = null,
        string $redirectUri = null,
        string $authCode = null
    ) :array {

        // Проверяем наличие установленного объекта класса для сохранения токенов oAuth 2.0
        if (empty(self::$tokenStorage)) {
            self::$tokenStorage = new FileStorage();
        }

        // Полный домен amoCRM для сохранения токенов
        $domain = self::getAmoDomain($subdomain);

        // Сохраняем поддомен, использованный при последней авторизации
        self::$lastSubdomain = $subdomain;

        // Сохраняем данные, использованные при последней авторизации для поддомена
        self::$lastAuth[ $subdomain ] = [
            'is_oauth2'     => true,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'auth_code'     => $authCode,
            'access_token'  => null,
            'refresh_token' => null
        ];

        // Если передан код авторизации, то обмениваем его на access token и refresh token
        if (isset($authCode)) {
            // Получаем новые токены от API amoCRM
            $tokens = self::getTokens($subdomain);

            // Сохраняем полученные токены
            self::$tokenStorage->save($tokens, $domain);
        } else { // Если код авторизации НЕ был передан
            // Загружем сохраненнные токены
            $tokens = self::$tokenStorage->load($domain);

            // Если нет токенов
            if (empty($tokens)) {
                throw new AmoAPIException(
                    "Нет токенов для поддомена '{$subdomain}': в метод oAuth2() необходимо передать параметр authCode"
                );
            }
        }

        // Обновляем данные, использованные при последней авторизации для поддомена
        self::updateLastAuth($tokens, $subdomain);

        // Проверяем наличие всех параметров авторизации для поддомена
        foreach ([ 'client_id', 'client_secret', 'redirect_uri', 'access_token', 'refresh_token' ] as $name) {
            if (empty(self::$lastAuth[ $subdomain ][ $name ])) {
                throw new AmoAPIException(
                    "Не установлен параметр авторизации oAuth2 '{$name}' для поддомена '{$subdomain}'"
                );
            }
        }

        return $tokens;
    }

    /**
     * Обновляем данные, использованные при последней авторизации для поддомена
     * @param  array  $tokens    Токены
     * @param  string $subdomain Поддомен amoCRM
     * @return void
     */
    protected static function updateLastAuth(array $tokens, string $subdomain)
    {
        foreach ([ 'client_id', 'client_secret', 'redirect_uri' ] as $name) {
            if (isset($tokens[ $name ])) {
                self::$lastAuth[ $subdomain ][ $name ] = $tokens[ $name ];
            }
        }

        self::$lastAuth[ $subdomain ]['access_token'] = $tokens['access_token'];
        self::$lastAuth[ $subdomain ]['refresh_token'] = $tokens['refresh_token'];
    }

    /**
     * Отправляет запрос на обмен кода авторизации на access token и refresh token и возвращает их
     * @param  string $subdomain Поддомен amoCRM
     * @return array
     */
    protected static function getTokens(string $subdomain) :array
    {
        $lastAuth = self::$lastAuth[ $subdomain ];
        $params = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'grant_type'    => 'authorization_code',
            'code'          => $lastAuth['auth_code'],
            'redirect_uri'  => $lastAuth['redirect_uri']
        ];

        $response = self::request('/oauth2/access_token', 'POST', $params, $subdomain);

        $tokens = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'redirect_uri'  => $lastAuth['redirect_uri'],
            'access_token'  => $response['access_token'],
            'refresh_token' => $response['refresh_token']
        ];

        return $tokens;
    }

    /**
     * Выполняет запрос на получение нового access token по его истечении и возвращает обновленные токены
     * @param  string $subdomain Поддомен amoCRM
     * @return array
     */
    protected static function refreshTokens(string $subdomain) :array
    {
        $lastAuth = self::$lastAuth[ $subdomain ];
        $params = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'grant_type'    => 'refresh_token',
            'refresh_token' => $lastAuth['refresh_token'],
            'redirect_uri'  => $lastAuth['redirect_uri']
        ];

        $response = self::request('/oauth2/access_token', 'POST', $params, $subdomain);

        $tokens = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'redirect_uri'  => $lastAuth['redirect_uri'],
            'access_token'  => $response['access_token'],
            'refresh_token' => $response['refresh_token']
        ];

        return $tokens;
    }

    /**
     * Выполняет запрос на обновление access token OAuth 2.0,
     * когда код статуса HTTP 401 (401 Unauthorized) в ответе на любые запросы,
     * кроме самих запросов авторизации по протоколу OAuth 2.0
     * @return array|true
     */
    protected static function reOAuth2()
    {
        // Сохраняем параметры последнего запроса и авторизации, ответ сервера
        $lastRequest = self::$lastRequest;
        $subdomain = $lastRequest['subdomain'];
        $lastResult = self::unescapeUnicode(self::$lastResult);
        $lastAuth = self::$lastAuth[ $subdomain ];

        // Выходим, если это запрос по протоколу OAuth 2.0
        if (stripos($lastRequest['query'], '/oauth2/access_token') !== false) {
            return true;
        }

        // Увеличиваем счетчик числа повторных запросов авторизации
        self::$reOAuth2Counter++;

        // Проверяем счетчик попыток повторных запросов
        if (self::$reOAuth2Counter > 1) {
            return true;
        }

        // trigger_error(
        //     "Запрос обновления access token OAuth 2.0 #" . self::$reAuthCounter .
        //         " ({$subdomain}) при ответе '401 Unauthorized': {$lastResult}",
        //     E_USER_NOTICE
        // );

        // Отладочная информация
        self::debug('['. self::$requestCounter . '] RE OAUTH2');

        // Получаем обновленные токены OAuth 2.0
        $tokens = self::refreshTokens($subdomain);

        // Полный домен amoCRM
        $domain = self::getAmoDomain($subdomain);

        // Сохраняем токены
        self::$tokenStorage->save($tokens, $domain);

        // Обновляем данные, использованные при последней авторизации для поддомена
        self::updateLastAuth($tokens, $subdomain);

        // Выполняем повторный запрос
        $response = self::request(
            $lastRequest['query'],
            $lastRequest['type'],
            $lastRequest['params'],
            $lastRequest['subdomain']
        );

        // Сбрасываем счетчик числа повторных запросов
        self::$reOAuth2Counter = 0;

        // Возвращаем результат повторного запроса
        return $response;
    }
}
