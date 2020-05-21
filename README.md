# amoCRM API Wrapper

![amoCRM logo](./assets/amocrm-logo.png)

Обертка на PHP для работы с REST API [amoCRM](https://www.amocrm.ru) с авторизацией по протоколу oAuth 2.0
или по API-ключу пользователя и троттлингом запросов к серверу.

Данная библиотека была создана для удовлетворения
[новых требований amoCRM](https://www.amocrm.ru/developers/content/integrations/requirements),
предъявляемых к публичным интерациям:
*"Публичные интеграции должны использовать механизм авторизации oAuth 2.0,
использование механизма API ключей не допускается. Требование с февраля 2020 года"*.

**Документация находится в процессе разработки.**

## Содержание

<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Авторизация](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F)
    - [Авторизация по протоколу oAuth 2.0 \(актуальный метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
        - [Обмен кода авторизации на access токен и refresh токен](#%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD-%D0%B8-refresh-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD)
        - [Получение нового access токена по его истечении](#%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD%D0%B0-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8)
    - [Авторизация по API-ключу пользователя \(устаревший метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%83%D1%81%D1%82%D0%B0%D1%80%D0%B5%D0%B2%D1%88%D0%B8%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
    - [Авторизация в нескольких поддоменах amoCRM](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%B2-%D0%BD%D0%B5%D1%81%D0%BA%D0%BE%D0%BB%D1%8C%D0%BA%D0%B8%D1%85-%D0%BF%D0%BE%D0%B4%D0%B4%D0%BE%D0%BC%D0%B5%D0%BD%D0%B0%D1%85-amocrm)
- [Дополнительные параметры](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B)
- [Работа с сущностями amoCRM](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D1%8F%D0%BC%D0%B8-amocrm)
    - [Общие методы моделей](#%D0%9E%D0%B1%D1%89%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9)
    - [Список методов моделей](#%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9)
    - [Методы для загрузки сущностей](#%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%B4%D0%BB%D1%8F-%D0%B7%D0%B0%D0%B3%D1%80%D1%83%D0%B7%D0%BA%D0%B8-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9)
    - [Методы для пакетного сохранения сущностей](#%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%B4%D0%BB%D1%8F-%D0%BF%D0%B0%D0%BA%D0%B5%D1%82%D0%BD%D0%BE%D0%B3%D0%BE-%D1%81%D0%BE%D1%85%D1%80%D0%B0%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9)
    - [Дополнительные методы](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)
    - [Обработка исключений](#%D0%9E%D0%B1%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B0-%D0%B8%D1%81%D0%BA%D0%BB%D1%8E%D1%87%D0%B5%D0%BD%D0%B8%D0%B9)
- [Примеры](#%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B)
    - [Работа с контактами](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D0%B0%D0%BC%D0%B8)
    - [Работа с компаниями](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC%D0%B8)
    - [Работа со сделками](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81%D0%BE-%D1%81%D0%B4%D0%B5%D0%BB%D0%BA%D0%B0%D0%BC%D0%B8)
    - [Работа с событиями](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D0%BE%D0%B1%D1%8B%D1%82%D0%B8%D1%8F%D0%BC%D0%B8)
    - [Работа с несколькими поддоменами amoCRM](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BD%D0%B5%D1%81%D0%BA%D0%BE%D0%BB%D1%8C%D0%BA%D0%B8%D0%BC%D0%B8-%D0%BF%D0%BE%D0%B4%D0%B4%D0%BE%D0%BC%D0%B5%D0%BD%D0%B0%D0%BC%D0%B8-amocrm)
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

- `static AmoAPI::oAuth2(string $subdomain, string $clientId, string $clientSecret, string $redirectUri, ?string $authCode = null, bool $storeAll = true) :array`  
    - `$subdomain` - поддомен amoCRM.
    - `$clientId` - ID интеграции.
    - `$clientSecret` - секрет интеграции.
    - `$redirectUri` - URI перенаправления.
    - `$authCode` - код авторизации (временный ключ).
    - `$storeAll` - сохранять $clientId, $clientSecret, $redirectUri вместе с полученными access и refresh токенами.

<a id="%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD-%D0%B8-refresh-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD"></a>
#### Обмен кода авторизации на access токен и refresh токен

Пример авторизации по протоколу oAuth 2.0 и [обмен кода авторизации](https://www.amocrm.ru/developers/content/oauth/step-by-step#get_access_token) на access и refresh токены.
```php
use \AmoCRM\AmoAPI;

try {
    // Параметры авторизации по протоколу oAuth 2.0
    $clientId     = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
    $clientSecret = 'TFPoaG2A5hp3G3o6opCL8eC9v92Mm0fKQWEHBDwIjedCmVliT4kI3XQcjOOP1s';
    $authCode     = 'eee60208cc09e3ae3506d667228038345b6578a11d4862094655f630074c8c6ed87a9d804d49b5880e';
    $redirectUri  = 'https://www.example.com/oauth2/';
    $subdomain    = 'testsubdomain';
    $storeAll     = true;

    // Авторизация
    AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode, $storeAll);

    // Получение информации об аккаунте
    print_r(AmoAPI::getAccount());

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD%D0%B0-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8"></a>
#### Получение нового access токена по его истечении

Пример авторизации по протоколу oAuth 2.0 и получение нового access и refresh токена. 
```php
use \AmoCRM\AmoAPI;

try {
    $subdomain = 'testsubdomain';

    // Если $storeAll = true при обмене кода авторизации на access и refresh токен
    AmoAPI::oAuth2($subdomain);

    // Если $storeAll = false при обмене кода авторизации на access и refresh токен
    // AmoAPI::oAuth2($subdomain, $clientId, $clientSecret, $redirectUri, $authCode = null, $storeAll);

    // Получение информации об аккаунте
    print_r(AmoAPI::getAccount());

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%83%D1%81%D1%82%D0%B0%D1%80%D0%B5%D0%B2%D1%88%D0%B8%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
### Авторизация по API-ключу пользователя ([устаревший метод](https://www.amocrm.ru/developers/content/oauth/old))

- `static AmoAPI::oauth(string $login, string $hash, string $subdomain) :array`
    - `$login` - логин пользователя.
    - `$hash` - API-ключ пользователя.
    - `$subdomain` - поддомен amoCRM.

Пример авторизации по API-ключу пользователя.
```php
use \AmoCRM\AmoAPI;

try {
    // Параметры авторизации по API-ключу пользователя
    $login     = 'login@example.com';
    $hash      = 'TFPoaG2A5hp3G3o6opCL8eC9v92Mm0fKQWEHBDwIjedCmVliT4kI3XQcjOOP1s';
    $subdomain = 'testsubdomain';

    // Авторизация
    AmoAPI::auth($login, $hash, $subdomain);

    // Получение информации об аккаунте
    print_r(AmoAPI::getAccount());

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%B2-%D0%BD%D0%B5%D1%81%D0%BA%D0%BE%D0%BB%D1%8C%D0%BA%D0%B8%D1%85-%D0%BF%D0%BE%D0%B4%D0%B4%D0%BE%D0%BC%D0%B5%D0%BD%D0%B0%D1%85-amocrm"></a>
### Авторизация в нескольких поддоменах amoCRM

Библиотека позволяет одновременно работать с несколькими поддоменами amoCRM.
Для этого необходимо последовательно выполнить авторизацию в каждом их поддоменов.

```php
use \AmoCRM\AmoAPI;

try {
    // Авторизация в поддомене 1
    AmoAPI::oAuth2($subdomain1, $clientId1, $clientSecret1, $redirectUri1, $authCode1);

    // Авторизация в поддомене 2
    AmoAPI::auth($login2, $hash2, $subdomain2);

    // Авторизация в поддомене N
    AmoAPI::oAuth2($subdomainN, $clientIdN, $clientSecretN, $redirectUriN, $authCodeN);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка авторизации (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B"></a>
## Дополнительные параметры

Дополнительные параметры авторизации и соединения с сервером amoCRM могут быть установлены
через статические свойства класса AmoAPI.

Свойство                | По умолчанию | Описание
----------------------- | ------------ | --------
`$debug`                | false        | Включает отладочный режим с сохранением запросов и ответов в лог файл
`$debugLogFile`         | temp/debug.log | Устанавливает лог файл отладочного режима
`$throttle`             | 7            | Устанавливает максимальное число запросов к серверу amoCRM в секунду ([не более 7 запросов в секунду](https://www.amocrm.ru/developers/content/api/recommendations))
`$verifySSLCerfificate` | true         | Включает проверку SSL/TLS-сертификата сервера amoCRM
`$SSLCertificateFile`   | 'cacert.pem' | Устанавливает файл SSL/TLS-сертификатов X.509 корневых удостоверяющих центров (CA) в формате РЕМ
`$amoDomain`            | 'amocrm.ru'  | Устанавливает домен для запросов к API amoCRM
`$amoUserAgent`         | 'amoCRM-API-client/2.0' | Устанавливает НТТР заголовок UserAgent в запросах
`$amoTimeout`           | 30           | Устанавливает таймаут соединения с сервером аmoCRM, секунды
`$accessTokenLeeway`    | 300          | Устанавливает временной запас на истечение срока действия access токен, секунды. Access токен истекает раньше на указанное число секунд
`$reOAuth2Attempts`     | 1            | Устанавливает максимальное число попыток обновления access токена по истечении его срока действия при ответе сервера '401 Unauthorized'
`$reOAuth2Timeout`      | 5            | Устанавливает таймаут перед обновлением access токена по истечении его срока действия при ответе сервера '401 Unauthorized', секунды
`$tokensFileDir`        | 'tokens/'    | Устанавливает относительный каталог для хранения файлов с токенами для поддоменов amoCRM
`$reAuthTimeout`        | 5            | Устанавливает таймаут перед повторной авторизацией по API-ключу пользователя при ответе сервера '401 Unauthorized', секунды
`$reAuthAttempts`       | 3            | Устанавливает максимальное число попыток повторной авторизации по API-ключу пользователя при ответе сервера '401 Unauthorized'
`$cookieFileDir`        | 'cookies/'   | Устанавливает относительный каталог для хранения файлов cookie
`$limitRows`            | 500          | Устанавливает максимальное количество сущностей, выбираемых за один запрос к серверу amoCRM ([не более 500, рекомендуется не более 250](https://www.amocrm.ru/developers/content/api/recommendations))

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D1%8F%D0%BC%D0%B8-amocrm"></a>
## Работа с сущностями amoCRM

Работа с сущностями amoCRM строится с помощью:

- методов классов-моделей:
    - `AmoContact` Модель контакта.
    - `AmoCompany` Модель компании.
    - `AmoLead` Модель сделки.
    - `AmoNote` Модель события (примечания).
    - `AmoTask` Модель задачи.
    - `AmoCatalogElement` Модель элемента каталога.

- дополнительных статических методов класса AmoAPI.

<a id="%D0%9E%D0%B1%D1%89%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9"></a>
### Общие методы моделей

Базовый класс моделей (AmoObject) содержит следующие общие методы:

- `__construct(array $data = [], ?string $subdomain = null)` Создает новый объект модели и заполняет данными.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен последней авторизации.
- `fillById(int $id)` Заполняет модель данными по ID сущности.
- `getParams() :array` Возвращает текущие параметры модели.
- `getCustomFields(array|int $ids) :array` Возвращает дополнительные поля по ID полей.
- `getCustomFieldValueById(int $id)` Возвращает значение дополнительного поля по ID поля.
- `setCustomFields(array $params)` Устанавливает значения дополнительных полей.
- `addTags(array|string $tags)` Добавляет теги.
- `delTags(array|string $tags)` Удаляет теги. 
- `save(bool $returnResponse = false)` Сохраняет объект модели в amoCRM и возвращает ID сущности. 
    - `$returnResponse` - вернуть ответ сервера вместо ID сущности.

<a id="%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9"></a>
### Список методов моделей

- `AmoContact` Модель контакта.
    - `addLeads(array|int $id)` Привязывает сделки по ID.
    - `addCustomers(array|int $id)` Привязывает покупателей по ID.
    - `addCompany(int $id)` Привязывает компанию по ID.
    - `getPhone()` Возвращает первый телефон контакта.
    - `getEmail()` Возвращает первый e-mail контакта.

- `AmoCompany` Модель компании.
    - `addLeads(array|int $id)` Привязывает сделки по ID.
    - `addContacts(array|int $id)` Привязывает контакты по ID.
    - `addCustomers(array|int $id)` Привязывает покупателей по ID.
    - `getPhone()` Возвращает первый телефон компании.
    - `getEmail()` Возвращает первый e-mail компании.

- `AmoLead` Модель сделки.
    - `addContacts(array|int $id)` Привязывает контакты по ID ([не более 40 контактов у одной сделки](https://www.amocrm.ru/developers/content/api/leads)).
    - `addCompany(int $id)` Привязывает компанию по ID.
    - `setCatalogElements(int $id)` Устанавливает элементы каталога по ID.

- `AmoTask` Модель задачи.
    - `addContact(int $id)` Привязывает контакт по ID.
    - `addLead(int $id)` Привязывает сделку по ID.

<a id="%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%B4%D0%BB%D1%8F-%D0%B7%D0%B0%D0%B3%D1%80%D1%83%D0%B7%D0%BA%D0%B8-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9"></a>
### Методы для загрузки сущностей

Класс AmoAPI содержит следующие общие статические методы для загрузки сущностей:

- `static getAll<Entities> (array $params, ?string $subdomain = null, bool $returnResponse = false) :\Generator`
    Загружает ВСЕ сущности заданного типа <Entities\> c возможностью фильтрации.  
    Возвращает объект типа \Generator для последующей выборки параметров сущностей.
    - `<Entities>`:
        - Contacts
        - Companies
        - Leads
        - Tasks
        - Notes
        - CatalogElements
    - `$params` - параметры фильтрации.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен последний авторизации.
    - `$returnResponse` - возвращать полный ответ сервера amoCRM вместо массива параметров сущностей.

- `static get<Entities>(array $params, ?string $subdomain = null, bool $returnResponse = false) :?array`  
    Загружает сущности заданного типа <Entities\> c возможностью фильтрации и постраничной выборки.  
    Возвращает массив параметров сущностей для заполнения моделей или null.
    - `<Entities>`:
        - Contacts
        - Companys
        - Leads
        - Tasks
        - Notes
        - Webhooks
        - Widgets
        - IncomingLeads
        - IncomingLeadsSummary
        - Pipelines
        - Catalogs
        - CatalogElements
    - `$params` - параметры фильтрации и постраничной выборки.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен последней авторизации.
    - `$returnResponse` - возвращать полный ответ сервера amoCRM вместо массива параметров сущностей.

<a id="%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%B4%D0%BB%D1%8F-%D0%BF%D0%B0%D0%BA%D0%B5%D1%82%D0%BD%D0%BE%D0%B3%D0%BE-%D1%81%D0%BE%D1%85%D1%80%D0%B0%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9"></a>
### Методы для пакетного сохранения сущностей

Класс AmoAPI содержит статический метод для пакетного сохранения (добавления или обновления) за один запрос до 500
сущностей различного типа для одного поддомена amoCRM:

- `static saveObjects(array $amoObjects, ?string $subdomain = null, bool $returnResponses = false) :array`  
    Добавляет или обновляет сущности в amoCRM. Возвращает массив параметров сущностей.
    - `$amoObjects` Массив объектов моделей (не более 500 объектов одного типа):
        - `AmoContact`
        - `AmoCompany`
        - `AmoLead`
        - `AmoNote`
        - `AmoTask`
        - `AmoCatalogElement`
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен, указанный при последний авторизации.
    - `$returnResponses` Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей.

<a id="%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B"></a>
### Дополнительные методы

Дополнительные статические методы класса AmoAPI:

- `static getAccount(string $with = '', ?string $subdomain = null) :array`  
    Возвращает информацию об аккаунте amoCRM.
    - `$with` - Разделенный запятыми список дополнительных параметров запроса:
        - custom_fields
        - users
        - pipelines
        - groups
        - note_types
        - task_types
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен последней авторизации.

- `static getLastResponse(bool $unescapeUnicode = true) :?string`  
    Возвращает последний ответ сервера amoCRM в сыром виде.
    - `$unescapeUnicode` - Декодировать символы UTF-8 \uXXXX в ответе сервера.

- `static request(string $query, string $type = 'GET', array $params = [], ?string $subdomain = null) :?array`
    Позволяет выполнить запрос к серверу amoCRM в сыром виде.  
    - `$query` - URL-путь с параметрами запроса.
    - `$type` - метод запроса 'GET' или 'POST'.
    - `$params` - параметры запроса.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен последней авторизации.

<a id="%D0%9E%D0%B1%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B0-%D0%B8%D1%81%D0%BA%D0%BB%D1%8E%D1%87%D0%B5%D0%BD%D0%B8%D0%B9"></a>
### Обработка исключений

Класс-исключение AmoAPIException содержит следующие вспомогательные методы:

- `getErrors() :array` Возвращает массив сообщений об ошибках из ответа сервера amoCRM.
- `getItems() :array` Возвращает массив параметров сущностей из ответа сервера amoCRM.

<a id="%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B"></a>
## Примеры

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D0%B0%D0%BC%D0%B8"></a>
### Работа с контактами

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoContact;

// Включение отладочного режима с сохранение запросов и ответов в лог файл
AmoAPI::$debug = true;

try {
    // Авторизация
    AmoAPI::oAuth2($subdomain);

    // Загрузка ВСЕХ контактов с возможностью фильтрации
    $generator = AmoAPI::getAllContacts([
        'query' => 'Ганс'
    ]);
    foreach ($generator as $items) {
        foreach ($items as $item) {
            print_r($item);
        }
    }

    // Загрузка контактов с возможностью фильтрации и постраничной выборки
    $items = AmoAPI::getContacts([
        'limit_rows'   => 100,
        'limit_offset' => 1000
    ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Создание нового контакта
    $contact1 = new AmoContact([
        'name'                => 'Ганс-Дитрих Геншер',
        'responsible_user_id' => 12345678
    ]);

    // Установка дополнительных полей
    $contact1->addCustomFields([
        '6532343' => 41,
        '123456' => [[
            'value' => '+79451112233',
            'enum'  => 'WORK'
        ]],
        '123467' => [[
            'value' => 'hans@example.com',
            'enum'  => 'WORK'
        ]]
    ]);

    // Сохранение контакта и получение его ID
    $contact1Id = $contact1->save();

    // Обновление существующего контакта и получение ответа сервера amoCRM
    $contact2 = new AmoContact([
        'id'         => 12300344,
        'name'       => 'Улоф Йоаким Пальме'
    ]);
    $contact2->first_name = 'Улоф';
    $contact2->last_name  = 'Пальме';
    print_r($contact1->save($returnResponse = true));

    // Пакетное добавление и/или обновление контактов
    $items = AmoAPI::saveObjects([ $contact1, $contact2 ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Заполнение модели контакта по ID контакта
    $contact3 = new AmoContact();
    $contact3->fillById(12345679);

    // Получение всех параметров контакта из модели
    print_r($contact3->getParams());

    // Получение дополнительных полей контакта по ID полей
    print_r($contact3->getCustomFields([ 123456, 123467 ]));    

    // Получение значения дополнительного поля контакта по ID поля
    print_r($contact3->getCustomFieldValueById(155114));

    // -------------------------------------------------------------------------

    // Привязка сделок к контакту по ID сделок
    $contact3->addLeads([ 12380925, 12364352 ]);

    // Привязка покупателей к контакту по ID покупателей
    $contact3->addCustomers([ 1237374, 1239658 ]);

    // Добавление тегов к контакту
    $contact3->addTags([ 'сотрудник', 'стажер' ]);

    // Удаление тегов контакта
    $contact3->delTags('курьер');

    // Сохранение контакта
    $contact3->save();

    // -------------------------------------------------------------------------

    $items = AmoAPI::getContacts([
        'responsible_user_id' => 12373452
    ]);

    // Пакетная привязка сделки к контактам
    $contacts = [];
    foreach ($items as $item) {
        $contacts[] = (new AmoContact($item))->addLeads(12380925);
    }

    // Пакетное сохранение контактов
    AmoAPI::saveObjects($contacts);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC%D0%B8"></a>
### Работа с компаниями

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoCompany;

try {
    // Авторизация
    AmoAPI::oAuth2($subdomain);

    // Загрузка ВСЕХ компаний с возможностью фильтрации
    $generator = AmoAPI::getAllCompanies([
        'query'        => 'OOO',
        'limit_offset' => 12000        
    ]);
    foreach ($generator as $items) {
        foreach ($items as $item) {
            print_r($item);
        }
    }

    // Загрузка компаний с возможностью фильтрации и постраничной выборки
    $items = AmoAPI::getCompanies([
        'responsible_user_id' => 12357492,
        'limit_rows'          => 250,
        'limit_offset'        => 1000
    ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Создание новой компании
    $company1 = new AmoCompany([
        'name'                => 'ООО МММ',
        'responsible_user_id' => 12358394,
    ]);

    // Установка дополнительных полей
    $company1->addCustomFields([
        '2390423' => 'Город Москва',
        '123456' => [[
            'value' => '+79457778899',
            'enum'  => 'WORK'
        ]],
        '123467' => [[
            'value' => 'mmm@example.com',
            'enum'  => 'WORK'
        ]]
    ]);

    // Привязка контакта
    $company1->addContacts(12375435);

    // Привязка сделки
    $company1->addLeads(12349693);

    // Привязка покупателя
    $company1->addCustomers(1237374);

    // Добавление тега
    $company1->addTags('Акционер');

    // Сохранение компании и получение ее ID
    $companyId = $company1->save();

    // Обновление существующей компании и получение ответа сервера amoCRM
    $company2 = new AmoCompany([
        'id'         => 12375435,
        'created_by' => 12396034,
        'name'       => 'ООО Рога и Копыта',
    ]);
    $response = $company2->save($returnResponse = true);

    // Пакетное добавление и/или обновление компаний
    $items = AmoAPI::saveObjects([ $company1, $company2 ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Заполнение модели компании по ID
    $company3 = new AmoCompany();
    $company3->fillById(12375435);

    // Получение всех параметров компании из модели
    print_r($company3->getParams());

    // Получение дополнительных полей компании по ID полей
    print_r($company3->getCustomFields([ 123456, 123467, 2390423 ]));    

    // Получение значения дополнительного поля компании по ID поля
    print_r($company3->getCustomFieldValueById(2390423));

    // -------------------------------------------------------------------------

    $items = AmoAPI::getCompanies([
        'responsible_user_id' => 12358394
    ]);

    // Пакетная привязка сделки к компаниям
    $companies = [];
    foreach ($items as $item) {
        $companies[] = (new AmoCompany($item))->addLeads([ 12380925 ]);
    }

    // Пакетное сохранение компаний
    AmoAPI::saveObjects($companies);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81%D0%BE-%D1%81%D0%B4%D0%B5%D0%BB%D0%BA%D0%B0%D0%BC%D0%B8"></a>
### Работа со сделками

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoLead;

try {
    // Авторизация
    AmoAPI::oAuth2($subdomain);

    // Загрузка ВСЕХ сделок с возможностью фильтрации
    $generator = AmoAPI::getAllLeads([
        'responsible_user_id' => 12357492
    ]);
    foreach ($generator as $items) {
        foreach ($items as $item) {
            print_r($item);
        }
    }

    // Загрузка сделок с возможностью фильтрации и постраничной выборки
    $items = AmoAPI::getLeads([
        'limit_rows'          => 250,
        'limit_offset'        => 2000
    ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Создание новой сделки
    $lead1 = new AmoLead([
        'name'                => 'Заказ № 964023',
        'responsible_user_id' => 12358394,
        'pipeline'            => [ 'id' => 45232121 ],
        'status_id'           => 142,
        'sale'                => 15000
   ]);

    // Установка дополнительных полей
    $lead1->addCustomFields([
        '3434323' => 'Акционерное общество',
        '3434327' => [ 1121, 1122, 1123 ]
    ]);

    // Привязка контакта
    $lead1->addContacts(12375435);

    // Привязка компании
    $lead1->addCompany(12364643);

    // Установка элементов каталога
    $lead1->setCatalogElements([
        93492 => [
            9898: 10,
            9899: 5
        ]
    ]);

    // Добавление тега
    $lead1->addTags('Акционер');

    // Сохранение сделки и получение ее ID
    $leadId = $lead1->save();

    // Обновление существующей компании и получение ответа сервера amoCRM
    $lead2 = new AmoLead([
        'id'         => 123057838,
        'sale'       => 175000
    ]);
    $response = $lead2->save($returnResponse = true);

    // Пакетное добавление и/или обновление сделок
    $items = AmoAPI::saveObjects([ $lead1, $lead2 ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------

    // Заполнение модели сделки по ID
    $lead3 = new AmoContact();
    $lead3->fillById(12328958);

    // Получение параметров сделки из модели
    print_r($lead3->getParams());

    // Получение дополнительных полей сделки по ID полей
    print_r($lead3->getCustomFields([ 123456, 123467, 2390423 ]));    

    // Получение значения дополнительного поля сделки по ID поля
    print_r($lead3->getCustomFieldValueById(2390423));

    // -------------------------------------------------------------------------

    $leads = AmoAPI::getLeads([
        'responsible_user_id' => 12358394
    ]);

    // Пакетная привязка компании к сделкам
    $leads = [];
    foreach ($items as $item) {
        $leads[] = (new AmoLead($item))->addCompany(12380925);
    }

    // Пакетное сохранение сделок
    AmoAPI::saveObjects($leads);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D0%BE%D0%B1%D1%8B%D1%82%D0%B8%D1%8F%D0%BC%D0%B8"></a>
### Работа с событиями

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoNote;

try {
    // Авторизация
    AmoAPI::oAuth2($subdomain);

    // Загрузка ВСЕХ событий, привязанных к сделкам, с возможностью фильтрации
    $generator = AmoAPI::getAllNotes([
        'type'       => 'lead',
        'note_type'  => AmoNote::COMMON_NOTETYPE
    ]);
    foreach ($generator as $items) {
        foreach ($items as $item) {
            print_r($item);
        }
    }

    // Загрузка событий, привязанных к контактам, с возможностью фильтрации и постраничной выборки
    $items = AmoAPI::getLeads([
        'type'           => 'contact',
        'limit_rows'     => 250,
        'limit_offset'   => 2000
    ]);
    foreach ($items as $item) {
        print_r($item);
    }

    // -------------------------------------------------------------------------
    
    // Создание нового события типа "обычное примечание", привязанного к сделке
    $note = new AmoNote([
        'element_id'   => 12328687,
        'note_type'    => AmoNote::COMMON_NOTETYPE,
        'element_type' => AmoNOTE::LEAD_TYPE,
        'text'         => 'Текст примечания к сделке'
    ]);

    // Сохранение события и получение его ID
    $noteId = $note->save();

    // Обновление существующего события
    $note2 = new AmoNote([
        'id'   => 12300958,
        'text' => 'Обновленный текст события'
    ]);

    // Заполнение модели события по ID и изменение текста события
    $note3 = new AmoNote();
    $note3->fillById(12347842);
    $note3->text = 'Новый тест события';

    // Получение параметров события из модели
    print_r($note3->getParams());

    // Пакетное сохранение событий
    AmoAPI::saveObjects([ $note2, $note3 ]);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}

```

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D0%BD%D0%B5%D1%81%D0%BA%D0%BE%D0%BB%D1%8C%D0%BA%D0%B8%D0%BC%D0%B8-%D0%BF%D0%BE%D0%B4%D0%B4%D0%BE%D0%BC%D0%B5%D0%BD%D0%B0%D0%BC%D0%B8-amocrm"></a>
### Работа с несколькими поддоменами amoCRM

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoContact;

try {
    // Авторизация в поддомене 1
    AmoAPI::oAuth2($subdomain1, $clientId1, $clientSecret1, $redirectUri1, $authCode1);

    // Авторизация в поддомене 2
    AmoAPI::oAuth2($subdomain2, $clientId2, $clientSecret2, $redirectUri2, $authCode2);

    // Загрузка компаний из поддомена 1
    $items1 = AmoAPI::getCompanies([
        'responsible_user_id' => 12357492
    ], $subdomain1);

    // Загрузка всех компаний из поддомена 2
    $generator2 = AmoAPI::getAllCompanies([
        'query' => 'OOO'
    ], $subdomain2);

    // Создание новой компании для поддомена 1
    $company1 = new AmoCompany([
        'name' => 'ООО Абракадабра',
    ], $subdomain1);

    // Обновление существущей компании для поддомена 1
    $company2 = new AmoCompany([], $subdomain1);
    $company2->fillById(12389423);
    $company2->name = 'OOO Розенталь';

    // Пакетное сохранение компаний для поддомена 1
    AmoAPI::saveObjects([ $company1, $company2 ], $subomain1);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```
<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор

© 2019-2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия

Данная библиотека распространяется на условиях лицензии [MIT](./LICENSE).
