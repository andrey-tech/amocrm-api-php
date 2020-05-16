# amocrm-api

Обертка на PHP для работы с REST API [amoCRM](https://www.amocrm.ru).

## Содержание

<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Описание методов](#%D0%9E%D0%BF%D0%B8%D1%81%D0%B0%D0%BD%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2)
    - [Авторизация](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F)
        - [Авторизация по протоколу oAuth 2.0 \(актуальный метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
            - [Обмен кода авторизации на access token и refresh token](#%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-token-%D0%B8-refresh-token)
            - [Получение нового access token по его истечении](#%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-token-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8)
        - [Авторизация по API-ключу пользователя \(старый метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%81%D1%82%D0%B0%D1%80%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
- [Автор](#%D0%90%D0%B2%D1%82%D0%BE%D1%80)
- [Лицензия](#%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F)

<!-- /MarkdownTOC -->

<a id="%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F"></a>
## Требования

- PHP >= 7.0.
- Произвольный автозагрузчик классов, реализующий стандарт [PSR-4](https://www.php-fig.org/psr/psr-4/).

<a id="%D0%9E%D0%BF%D0%B8%D1%81%D0%B0%D0%BD%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2"></a>
## Описание методов

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F"></a>
### Авторизация

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
#### Авторизация по протоколу oAuth 2.0 ([актуальный метод](https://www.amocrm.ru/developers/content/oauth/oauth))


<a id="%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-token-%D0%B8-refresh-token"></a>
##### Обмен кода авторизации на access token и refresh token

```php
try {
    // Включает отладочный режим с выводом запросов и ответов в STDOUT
    \AmoCRM\AmoAPI::$debug = true;

    // Устанавливает максимальное число запросов к amoCRM API в секунду (не более 7 запросов в секунду!)
    \AmoCRM\AmoAPI::throttle = 1;

    // Включает проверку SSL-сертификата сервера amoCRM
    \AmoCRM\AmoAPI::verifySSLCerfificate = true;

    /**
     * Устанавливает относительный путь к файлу SSL-сертификатов X.509 корневых удостоверяющих центров
     * при проверке SSL-сертификата сервера amoCRM
     */
    \AmoCRM\AmoAPI::SSLCertificateFile = 'cacert.pem';

    // Устанавливает домен amoCRM для запросов к API
    \AmoCRM\AmoAPI::amoDomain = '.amocrm.ru';

    // Устанавливает НТТР заголовок UserAgent в запросах к API
    \AmoCRM\AmoAPI::amoUserAgent = 'amoCRM-API-client/2.0';

    // Устанавливает таймаут соединения с сервером аmoCRM, секунды
    \AmoCRM\AmoAPI::amoTimeout = 30; // Секунды

    /**
     * Устанавливает временной запас на истечение срока действия access token OAuth 2.0, секунд.
     * Access token OAuth 2.0 истекает раньше на указанное число секунд
     */
    \AmoCRM\AmoAPI::accessTokenLeeway = 5 * 60; // Секунды

    /**
     * Устанавливает максимальное число попыток обновления access token OAuth 2.0
     * по истечении его срока действия при ответе '401 Unauthorized'
     */
    \AmoCRM\AmoAPI::reOAuth2Attempts = 1;

    /**
     * Устанавливает таймаут перед обновлением access token OAuth 2.0
     * по истечении его срока действия при ответе '401 Unauthorized', секунд
     */
    \AmoCRM\AmoAPI::reOAuth2Timeout = 5; // Секунды

    // Устанавливает каталог для хранения файлов с токенами OAuth 2.0 для поддоменов amoCRM
    \AmoCRM\AmoAPI::tokensFileDir = 'tokens/';

    // Параметры авторизации по протоколу oAuth 2.0
    $clientId     = 'clientId';
    $clientSecret = 'clientSecret';
    $authCode     = 'authCode';
    $redirectUri  = 'https://www.example.com/oauth2/';
    $subdomain    = 'testneurocrm';
    $storeAll     = true; // Указывает на необходимость сохранения $clientId, $clientSecret, $redirectUri

    // Запрос авторизации
    \AmoCRM\AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode, $storeAll);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-token-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8"></a>
##### Получение нового access token по его истечении

```php
try {
    // Если $storeAll = true при обмене кода авторизации на access token и refresh token
    \AmoCRM\AmoAPI::oAuth2($subdomain);

    // Если $storeAll = false при обмене кода авторизации на access token и refresh token
    // \AmoCRM\AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode = null, $storeAll);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%81%D1%82%D0%B0%D1%80%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
#### Авторизация по API-ключу пользователя ([старый метод](https://www.amocrm.ru/developers/content/oauth/old))

```php
try {
    // Включает отладочный режим с выводом запросов и ответов в STDOUT
    \AmoCRM\AmoAPI::$debug = true;

    // Устанавливает максимальное число запросов к amoCRM API в секунду (не более 7 запросов в секунду!)
    \AmoCRM\AmoAPI::throttle = 1;

    // Включает проверку SSL-сертификата сервера amoCRM
    \AmoCRM\AmoAPI::verifySSLCerfificate = true;

    /**
     * Устанавливает относительный путь к файлу SSL-сертификатов X.509 корневых удостоверяющих центров
     * при проверке SSL-сертификата сервера amoCRM
     */
    \AmoCRM\AmoAPI::SSLCertificateFile = 'cacert.pem';

    // Устанавливает домен amoCRM для запросов к API
    \AmoCRM\AmoAPI::amoDomain = '.amocrm.ru';

    // Устанавливает НТТР заголовок UserAgent в запросах к API
    \AmoCRM\AmoAPI::amoUserAgent = 'amoCRM-API-client/2.0';

    // Устанавливает таймаут соединения с сервером аmoCRM, секунды
    \AmoCRM\AmoAPI::amoTimeout = 30; // Секунды

    // Устанавливает таймаут перед повторной авторизацией по API-ключу пользователя при ошибке '401 Unauthorized', секунд
    \AmoCRM\AmoAPI::reAuthTimeout = 5; // Секунды

    // Устанавливает максимальное число попыток повторной авторизации по API-ключу пользователя при ответе '401 Unauthorized'
    \AmoCRM\AmoAPI::reAuthAttempts = 3;

    // Устанавливает относительный каталог для хранения файлов cookie
    \AmoCRM\AmoAPI::cookieFileDir = 'cookies/';

    // Параметры авторизации по API ключу пользователя
    $login     = 'login@example.com';
    $hash      = 'hash';
    $subdomain = 'subdomain';

    // Запрос авторизации
    \AmoCRM\AmoAPI::auth($login, $hash, $subdomain);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор

© 2019-2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия

[The MIT License](https://github.com/andrey-tech/amocrm-api/blob/master/LICENSE)
