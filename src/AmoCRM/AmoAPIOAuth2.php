<?php
/**
 * Трейт AmoAPIOAuth2. Содержит методы для авторизации по протоколу OAuth 2.0
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.1
 *
 * v1.0.0 (06.04.2020) Начальный релиз.
 * v1.1.0 (10.04.2020) Изменен алгоритм работы метода oAuth2().
 * v1.1.1 (14.04.2020) Метод loadTokens() теперь публичный
 *                     и может быть использован для проверки наличия токенов для поддомена amoCRM.
 * v1.2.0 (19.04.2020) Изменен метод oAuth2(): добавлен параметр storeAll.
 * v1.2.1 (25.05.2020) Исправлено отладочное сообщение
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIOAuth2
{
    /**
     * Временной запас на истечение срока действия access token OAuth 2.0, секунды
     * То есть access token OAuth 2.0 истекает раньше на указанное число секунд.
     * @var integer
     */
    public static $accessTokenLeeway = 5 * 60; // Секунды

    /**
     * Таймаут перед обновлением access token OAuth 2.0 при ответе '401 Unauthorized', секунд
     * @var integer
     */
    public static $reOAuth2Timeout = 5; // Секунды

    /**
     * Максимальное число попыток обновления access token OAuth 2.0 при ответе '401 Unauthorized'
     * @var int
     */
    public static $reOAuth2Attempts = 1;

    /**
     * Каталог для хранения файлов с токенами OAuth 2.0 относительно каталога файла класса AmoAPI
     * (null - текущий каталог, на конце каталога /)
     * @var string|null
     */
    public static $tokensFileDir = 'tokens/';

    /**
     * Счетчик попыток обновления access token OAuth 2.0 при ответе '401 Unauthorized'
     */
    protected static $reOAuth2Counter = 0;

    /**
     * Выполняет авторизацию в amoCRM по протоколу OAuth 2.0
     * @param  string      $subdomain    Поддомен amoCRM
     * @param  string|null $clientId     ID интеграции
     * @param  string|null $clientSecret Секрет интеграции
     * @param  string|null $redirectUri  Redirect URI, указанный в настройках интеграции
     * @param  string|null $authCode     Код авторизации, полученный в настройках интеграции
     * @param  bool        $storeAll     Флаг хранения всех переданных параметров авторизации
     * @return array
     */
    public static function oAuth2(
        string $subdomain,
        string $clientId = null,
        string $clientSecret = null,
        string $redirectUri = null,
        string $authCode = null,
        bool   $storeAll = true
    ) :array {

        // Сохраняем поддомен, использованный при последней авторизации
        self::$lastSubdomain = $subdomain;

        // Сохраняем данные, использованные при последней авторизации для поддомена
        self::$lastAuth[ $subdomain ] = [
            'is_oauth2'     => true,
            'is_store_all'  => $storeAll,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'auth_code'     => $authCode
        ];

        // Если передан код авторизации, то обмениваем его на access token и refresh token
        if (isset($authCode)) {
            // Получаем новые токены
            $response = self::getTokens($subdomain);

            // Сохраняем токены с дополнительными параметрами в JSON-файл
            $tokens = self::saveTokens($response, $subdomain);

            // Обновляем данные, использованные при последней авторизации для поддомена
            self::updateLastAuth($tokens, $subdomain);

            // Возвращаем токены
            return $tokens;
        }

        // Если код авторизации НЕ был передан,
        // то загружем токены c дополнительными параметрами из JSON-файла
        $tokens = self::loadTokens($subdomain);

        // Если нет JSON-файла с токенами
        if (empty($tokens)) {
            throw new AmoAPIException(
                "В метод oAuth2() необходимо передать параметры авторизации (отсутствует файл токенов для поддомена {$subdomain})"
            );
        }

        // Обновляем данные, использованные при последней авторизации для поддомена
        self::updateLastAuth($tokens, $subdomain);

        // Проверяем наличие всех параметров авторизации
        foreach ([ 'client_id', 'client_secret', 'redirect_uri'] as $name) {
            if (empty(self::$lastAuth[ $subdomain ][ $name ])) {
                throw new AmoAPIException(
                    "В метод oAuth2() необходимо передать параметры авторизации (отсутствуют в файле токенов для поддомена {$subdomain})"
                );
            }
        }

        // Проверяем необходимость получения нового access token по его истечении
        if ($tokens['expires_at'] - self::$accessTokenLeeway <= time()) {
            // Получаем обновленные токены
            $response = self::refreshTokens($subdomain);

            // Сохраняем токены и дополнительные параметры в JSON-файл
            $tokens = self::saveTokens($response, $subdomain);

            // Обновляем данные, использованные при последней авторизации для поддомена
            self::updateLastAuth($tokens, $subdomain);
        }

        return $tokens;
    }

    /**
     * Обновляет данные, использованные при последней авторизации для поддомена
     * @param array|null $params Параметры для добавления к данным
     * @param  string $subdomain Поддомен amoCRM
     * @return void
     */
    protected static function updateLastAuth($params, string $subdomain)
    {
        self::$lastAuth[ $subdomain ] = array_merge(self::$lastAuth[ $subdomain ], $params ?? []);
    }

    /**
     * Отправляет запрос на обмен кода авторизации на access token и refresh token и возвращает новые токены
     * @param  string $subdomain Поддомен amoCRM
     * @return array
     */
    protected static function getTokens(string $subdomain) :array
    {
        // Формируем параметры запроса
        $lastAuth = self::$lastAuth[ $subdomain ];
        $params = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'grant_type'    => 'authorization_code',
            'code'          => $lastAuth['auth_code'],
            'redirect_uri'  => $lastAuth['redirect_uri']
        ];

        // Выполняем запрос
        $response = self::request('/oauth2/access_token', 'POST', $params, $subdomain);

        /**
         * Проверяем наличие всех параметров в ответе
         * 'token_type'    Тип токена (Bearer)
         * 'expires_in'    Время в секундах, через сколько access_token токен истечет
         * 'access_token'  Access токен (действует 1 сутки)
         * 'refresh_token' Refresh токен (действует всего 3 месяца)
         */
        foreach ([ 'token_type', 'expires_in', 'access_token', 'refresh_token' ] as $name) {
            if (empty($response[ $name ])) {
                throw new AmoAPIException("Нет параметра {$name} в ответе сервера: " . print_r($response, true));
            }
        }

        return $response;
    }

    /**
     * Выполняет запрос на получение нового access token по его истечении и возвращает обновленные токены
     * @param  string $subdomain    Поддомен amoCRM
     * @return array
     */
    protected static function refreshTokens(string $subdomain) :array
    {
        // Формируем параметры запроса
        $lastAuth = self::$lastAuth[ $subdomain ];
        $params = [
            'client_id'     => $lastAuth['client_id'],
            'client_secret' => $lastAuth['client_secret'],
            'grant_type'    => 'refresh_token',
            'refresh_token' => $lastAuth['refresh_token'],
            'redirect_uri'  => $lastAuth['redirect_uri']
        ];

        // Выполняем запрос
        $response = self::request('/oauth2/access_token', 'POST', $params, $subdomain);

        /**
         * Проверяем наличие всех параметров в ответе
         * 'token_type'    Тип токена (Bearer)
         * 'expires_in'    Время в секундах, через сколько access_token токен истечет
         * 'access_token'  Access токен (действует 1 сутки)
         * 'refresh_token' Refresh токен (действует всего 3 месяца)
         */
        foreach ([ 'token_type', 'expires_in', 'access_token', 'refresh_token' ] as $name) {
            if (empty($response[ $name ])) {
                throw new AmoAPIException("Нет параметра {$name} в ответе сервера: " . print_r($response, true));
            }
        }

        return $response;
    }

    /**
     * Сохраняет токены с дополнительными параметрами в JSON-файл
     * @param  array  $tokens    Параметры для сохранения
     * @param  string $subdomain Поддомен amoCRM
     * @return array
     */
    protected static function saveTokens(array $tokens, string $subdomain) :array
    {
        // Формируем полное имя файла c токенами
        $tokensFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . (self::$tokensFileDir ?? '');
        self::checkDir($tokensFilePath);
        $tokensFile =  $tokensFilePath . $subdomain . '.json';

        // Сохраняем время (UNIX timestamp), когда токен истечет
        $tokens['expires_at'] = time() + $tokens['expires_in'];

        // Формируем параметры для сохранения в JSON-файл
        $lastAuth = self::$lastAuth[ $subdomain ];
        if ($lastAuth['is_store_all']) {
            $tokens = array_merge([
                'client_id'     => $lastAuth['client_id'],
                'client_secret' => $lastAuth['client_secret'],
                'redirect_uri'  => $lastAuth['redirect_uri']
            ], $tokens);
        }

        // Кодируем параметры в JSON
        $jsonTokens = json_encode($tokens, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        if ($jsonTokens === false) {
            $errorMessage = json_last_error_msg();
            throw new AmoAPIException("Ошибка JSON-кодирования токенов ({$errorMessage}): " . print_r($tokens, true));
        }

        // Записываем в файл
        if (! file_put_contents($tokensFile, $jsonTokens, LOCK_EX)) {
            throw new AmoAPIException("Не удалось записать токены в файл {$tokensFile}");
        }

        return $tokens;
    }

    /**
     * Возвращает токены с дополнительными параметрами из JSON-файла
     * @param  string $subdomain Поддомен amoCRM
     * @return array|null
     */
    public static function loadTokens(string $subdomain)
    {
        // Формируем полное имя файла c токенами
        $tokensFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . (self::$tokensFileDir ?? '');
        $tokensFile =  $tokensFilePath . $subdomain . '.json';

        // Выходим, если файл с токенами не существует
        if (! is_file($tokensFile)) {
            return null;
        }

        // Загружаем содержимое файла с токенами
        $jsonTokens = file_get_contents($tokensFile);
        if ($jsonTokens === false) {
            throw new AmoAPIException("Не удалось прочитать файл токенов {$tokensFile}");
        }

        // Декодируем содержимое файла с токенами
        $tokens = json_decode($jsonTokens, true);
        if (is_null($tokens)) {
            $errorMessage = json_last_error_msg();
            throw new AmoAPIException(
                "Ошибка JSON-декодирования содержимого файла токенов ($errorMessage): {$jsonTokens}"
            );
        }

        return $tokens;
    }

    /**
     * Выполняет, при необходимости, запрос на обновление access token OAuth 2.0,
     * когда код статуса HTTP 401 (401 Unauthorized) в ответе на любые запросы,
     * кроме самих запросов авторизации по протоколу OAuth 2.0
     * @return array|true
     */
    protected static function reOAuth2()
    {
        // Извлекаем параметры последнего запроса и авторизации, ответ сервера
        $lastRequest = self::$lastRequest;
        $subdomain = $lastRequest['subdomain'];
        $lastResult = self::unescapeUnicode(self::$lastResult);
        $lastAuth = self::$lastAuth[ $subdomain ];

        // Выходим, если это запрос по протоколу OAuth 2.0
        if (stripos($lastRequest['query'], '/oauth2/access_token') !== false) {
            return true;
        }

        // Увеличиваем счетчик числа повторных запросов
        self::$reOAuth2Counter++;

        // Проверяем счетчик попыток повторных запросов
        if (self::$reOAuth2Counter > self::$reOAuth2Attempts) {
            return true;
        }

        // Сохраняем сообщение в лог файл
        trigger_error(
            "Запрос обновления access token OAuth 2.0 #" . self::$reAuthCounter .
                " ({$subdomain}) при ответе '401 Unauthorized': {$lastResult}",
            E_USER_NOTICE
        );

        // Отладочная информация
        self::debug('['. self::$requestCounter . "] RE OAUTH2 (" . self::$reOAuth2Timeout . 's)');

        // Выдерживаем таймаут перед повторным запросом
        sleep(self::$reOAuth2Timeout);

         // Получаем обновленные токены OAuth 2.0
        $response = self::refreshTokens($subdomain);

        // Сохраняем токены и дополнительные параметры в JSON-файл
        $tokens = self::saveTokens($response, $subdomain);

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
