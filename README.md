# amoCRM API Wrapper

![amoCRM logo](./assets/amocrm-logo.png)

Обертка на PHP для работы с REST API [amoCRM](https://www.amocrm.ru) с авторизацией по протоколу oAuth 2.0
или по API-ключу пользователя и троттлингом запросов к серверу amoCRM.

## Содержание

<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Авторизация](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F)
    - [Авторизация по протоколу oAuth 2.0 \(актуальный метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
        - [Обмен кода авторизации на access token и refresh token](#%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-token-%D0%B8-refresh-token)
        - [Получение нового access token по его истечении](#%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-token-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8)
        - [Авторизация по API-ключу пользователя \(старый метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%81%D1%82%D0%B0%D1%80%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
- [Описание методов сущностей amoCRM](#%D0%9E%D0%BF%D0%B8%D1%81%D0%B0%D0%BD%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9-amocrm)
    - [Контакт](#%D0%9A%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82)
- [Автор](#%D0%90%D0%B2%D1%82%D0%BE%D1%80)
- [Лицензия](#%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F)

<!-- /MarkdownTOC -->

<a id="%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F"></a>
## Требования

- PHP >= 7.0.
- Произвольный автозагрузчик классов, реализующий стандарт [PSR-4](https://www.php-fig.org/psr/psr-4/).

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F"></a>
## Авторизация

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
### Авторизация по протоколу oAuth 2.0 ([актуальный метод](https://www.amocrm.ru/developers/content/oauth/oauth))

<a id="%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-token-%D0%B8-refresh-token"></a>
#### Обмен кода авторизации на access token и refresh token

Пример авторизации по протоколу oAuth 2.0 с установкой всех возможных необязательных параметров соединения с сервером amoCRM.
```php
use AmoCRM\AmoAPI;

try {
    // Включает отладочный режим с выводом запросов и ответов в STDOUT
    AmoAPI::$debug = true;

    // Устанавливает максимальное число запросов к amoCRM API в секунду (не более 7 запросов в секунду!)
    AmoAPI::throttle = 7;

    // Включает проверку SSL-сертификата сервера amoCRM
    AmoAPI::verifySSLCerfificate = true;

    /**
     * Устанавливает относительный путь к файлу SSL-сертификатов X.509 корневых удостоверяющих центров
     * для проверки SSL-сертификата сервера amoCRM
     */
    AmoAPI::SSLCertificateFile = 'cacert.pem';

    // Устанавливает домен amoCRM для запросов к API
    AmoAPI::amoDomain = 'amocrm.ru';

    // Устанавливает НТТР заголовок UserAgent в запросах к API
    AmoAPI::amoUserAgent = 'amoCRM-API-client/2.0';

    // Устанавливает таймаут соединения с сервером аmoCRM, секунды
    AmoAPI::amoTimeout = 30; // Секунды

    /**
     * Устанавливает временной запас на истечение срока действия access token OAuth 2.0, секунд.
     * Access token OAuth 2.0 истекает раньше на указанное число секунд
     */
    AmoAPI::accessTokenLeeway = 5 * 60; // Секунды

    /**
     * Устанавливает максимальное число попыток обновления access token OAuth 2.0
     * по истечении его срока действия при ответе '401 Unauthorized'
     */
    AmoAPI::reOAuth2Attempts = 1;

    /**
     * Устанавливает таймаут перед обновлением access token OAuth 2.0
     * по истечении его срока действия при ответе '401 Unauthorized', секунд
     */
    AmoAPI::reOAuth2Timeout = 5; // Секунды

    // Устанавливает каталог для хранения файлов с токенами OAuth 2.0 для поддоменов amoCRM
    AmoAPI::tokensFileDir = 'tokens/';

    // Параметры авторизации по протоколу oAuth 2.0
    $clientId     = 'clientId';
    $clientSecret = 'clientSecret';
    $authCode     = 'authCode';
    $redirectUri  = 'https://www.example.com/oauth2/';
    $subdomain    = 'subdomain';
    
    // Включает режим сохранения $clientId, $clientSecret, $redirectUri вместе с токенами OAuth 2.0
    $storeAll     = true;

    // Запрос авторизации
    AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode, $storeAll);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-token-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8"></a>
#### Получение нового access token по его истечении

```php
use AmoCRM\AmoAPI;

try {
    // Если $storeAll = true при обмене кода авторизации на access token и refresh token
    AmoAPI::oAuth2($subdomain);

    // Если $storeAll = false при обмене кода авторизации на access token и refresh token
    // AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode = null, $storeAll);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%81%D1%82%D0%B0%D1%80%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
#### Авторизация по API-ключу пользователя ([старый метод](https://www.amocrm.ru/developers/content/oauth/old))

Пример авторизации по API-ключу пользователя с установкой всех возможных необязательных параметров соединения с сервером amoCRM.
```php
use AmoCRM\AmoAPI;

try {
    // Включает отладочный режим с выводом запросов и ответов в STDOUT
    AmoAPI::$debug = true;

    // Устанавливает максимальное число запросов к amoCRM API в секунду (не более 7 запросов в секунду!)
    AmoAPI::throttle = 7;

    // Включает проверку SSL-сертификата сервера amoCRM
    AmoAPI::verifySSLCerfificate = true;

    /**
     * Устанавливает относительный путь к файлу SSL-сертификатов X.509 корневых удостоверяющих центров
     * для проверки SSL-сертификата сервера amoCRM
     */
    AmoAPI::SSLCertificateFile = 'cacert.pem';

    // Устанавливает домен amoCRM для запросов к API
    AmoAPI::amoDomain = 'amocrm.ru';

    // Устанавливает НТТР заголовок UserAgent в запросах к API
    AmoAPI::amoUserAgent = 'amoCRM-API-client/2.0';

    // Устанавливает таймаут соединения с сервером аmoCRM, секунды
    AmoAPI::amoTimeout = 30; // Секунды

    // Устанавливает таймаут перед повторной авторизацией по API-ключу пользователя при ошибке '401 Unauthorized', секунд
    AmoAPI::reAuthTimeout = 5; // Секунды

    // Устанавливает максимальное число попыток повторной авторизации по API-ключу пользователя при ответе '401 Unauthorized'
    AmoAPI::reAuthAttempts = 3;

    // Устанавливает относительный каталог для хранения файлов cookie
    AmoAPI::cookieFileDir = 'cookies/';

    // Параметры авторизации по API ключу пользователя
    $login     = 'login@example.com';
    $hash      = 'hash';
    $subdomain = 'subdomain';

    // Запрос авторизации
    AmoAPI::auth($login, $hash, $subdomain);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%9E%D0%BF%D0%B8%D1%81%D0%B0%D0%BD%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9-amocrm"></a>
## Описание методов сущностей amoCRM

<a id="%D0%9A%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82"></a>
### Контакт

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoContact;

try {
    // Загрузка ВСЕХ контактов amoCRM с возможностью фильтрации и постраничной выборки
    $generator = AmoAPI::getAllContacts([
        'query' => 'Ганс'
    ]);
    foreach ($generator as $items) {
        foreach ($items as $item) {
            print_r($item);
        }
    }

    // Загрузка контактов amoCRM с возможностью фильтрации и постраничной выборки
    $items = AmoAPI::getContacts([
        'limit_rows'   => 500,
        'limit_offset' => 1000
    ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Добавление нового контакта и получение ID созданного контакта
    $contact1 = new AmoContact([
        'name'                => 'Ганс-Дитрих Геншер',
        'responsible_user_id' => 12345678
    ]);
    $contact1->addCustomFields([
        '123456' => [[
            'value' => '+79451112233',
            'enum'  => 'WORK'
        ]],
        '123467' => [[
            'value' => 'hans@example.com',
            'enum'  => 'WORK'
        ]]
    ]);
    $contact1Id = $contact1->save();

    // Обновление существующего контакта и получение ответа сервера amoCRM вместо ID сущности
    $contact2 = new AmoContact([
        'id'         => 12345679,
        'name'       => 'Улоф Пальме',
        'first_name' => 'Улоф',
        'last_name'  => 'Пальме'
    ]);
    $response = $contact1->save($returnResponse = true);

    // Пакетное добавление и/или обновление контактов
    $items = AmoAPI::saveObjects([ $contact1, $contact2 ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Заполнение модели по ID контакта
    $contact3 = new AmoContact();
    $contact3->fillById(12345679);
   
    // Получение параметров контакта из модели
    print_r($contact3->getParams());

    // Получение дополнительных полей контакта по ID полей
    print_r($contact3->getCustomFields([ 123456, 123467 ]));    

    // Получение значения дополнительного поля по ID поля
    print_r($contact3->getCustomFieldValueById(155114));

    // -------------------------------------------------------------------------

    // Привязка сделок по ID сделок
    $contact3->addLeads([ 12380925, 12380925 ]);

    // Привязка покупателей по ID покупателей
    $contact3->addCustomers([ 1237374, 1239658 ]);

    // Добавление тегов
    $contact3->addTags([ 'тег1', 'тег2' ]);

    // Удаление тегов
    $contact3->delTags([ 'тег3', 'тег4' ]);

    $contact3->save();

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
