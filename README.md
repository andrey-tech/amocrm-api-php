# amoCRM API Wrapper

![amoCRM logo](./assets/amocrm-logo.png)

Обертка на PHP для работы с REST API [amoCRM](https://www.amocrm.ru) с авторизацией по протоколу oAuth 2.0
или по API-ключу пользователя и троттлингом запросов.

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
- [Дополнительные параметры](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B)
- [Работа с сущностями amoCRM](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D1%8F%D0%BC%D0%B8-amocrm)
    - [Общие методы моделей](#%D0%9E%D0%B1%D1%89%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9)
    - [Список методов моделей](#%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9)
    - [Методы для загрузки сущностей](#%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%B4%D0%BB%D1%8F-%D0%B7%D0%B0%D0%B3%D1%80%D1%83%D0%B7%D0%BA%D0%B8-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D0%B5%D0%B9)
    - [Дополнительные методы](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)
    - [Примеры работы с контактами](#%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B-%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B-%D1%81-%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D0%B0%D0%BC%D0%B8)
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

    // Получение информации по аккаунту
    print_r(AmoAPI::getInfo());

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

    // Получение информации по аккаунту
    print_r(AmoAPI::getInfo());

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

    // Получение информации по аккаунту
    print_r(AmoAPI::getInfo());

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
`$debug`                | false        | Включает отладочный режим с выводом запросов и ответов в STDOUT
`$throttle`             | 7            | Устанавливает максимальное число запросов к серверу amoCRM в секунду ([не более 7 запросов в секунду](https://www.amocrm.ru/developers/content/api/recommendations))
`$verifySSLCerfificate` | true         | Включает проверку SSL/TLS-сертификата сервера amoCRM
`$SSLCertificateFile`   | 'cacert.pem' | Устанавливает относительный путь к файлу SSL/TLS-сертификатов X.509 корневых удостоверяющих центров (CA) в формате РЕМ
`$amoDomain`            | 'amocrm.ru'  | Устанавливает домен для запросов к API amoCRM
`$amoUserAgent`         | 'amoCRM-API-client/2.0' | Устанавливает НТТР заголовок UserAgent в запросах
`$amoTimeout`           | 30           | Устанавливает таймаут соединения с сервером аmoCRM, секунды
`$accessTokenLeeway`    | 300          | Устанавливает временной запас на истечение срока действия access токен, секунды. Access токен истекает раньше на указанное число секунд
`$reOAuth2Attempts`     | 1            | Устанавливает максимальное число попыток обновления access токена по истечении его срока действия при ответе сервера '401 Unauthorized'
`$reOAuth2Timeout`      | 5            | Устанавливает таймаут перед обновлением access токена по истечении его срока действия при ответе сервера '401 Unauthorized', секунды
`$tokensFileDir`        | 'tokens/'    | Устанавливает каталог для хранения файлов с токенами для поддоменов amoCRM
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

- `__construct(array $data = [])` Создает новую модель и заполняет данными.
- `fillById(int $id)` Заполняет модель данными по ID сущности.
- `getParams() :array` Возвращает текущие параметры модели.
- `getCustomFields(array $ids) :array` Возвращает дополнительные поля по массиву ID полей.
- `getCustomFieldValueById(int $id)` Возвращает значение дополнительного поля по ID поля.
- `setCustomFields(array $params)` Устанавливает значения дополнительных полей.
- `addTags(array $tags)` Добавляет теги.
- `delTags(array $tags)` Удаляет теги. 

<a id="%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D0%BE%D0%B2-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9"></a>
### Список методов моделей

- `AmoContact` Модель контакта.
    - `addLeads(array $id)` Привязывает сделки по ID сделок.
    - `addCustomers(array $id)` Привязывает покупателей по ID покупателей.

- `AmoCompany` Модель компании.
    - `addLeads(array $id)` Привязывает сделки по ID сделок.
    - `addContacts(array $id)` Привязывает контакты по ID контактов.
    - `addCustomers(array $id)` Привязывает покупателей по ID покупателей.

- `AmoLead` Модель сделки.
    - `addContacts(array $id)` Привязывает контакты по ID контактов.
    - `setCatalogElements(int $id)` Устанавливает элементы каталога по ID элементов.

- `AmoTask` Модель задачи.
    - `addContact(int $id)` Привязывает контакт по ID контакта.
    - `addLead(int $id)` Привязывает сделку по ID сделки.

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
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен, указанный при авторизации.
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
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен, указанный при авторизации.
    - `$returnResponse` - возвращать полный ответ сервера amoCRM вместо массива параметров сущностей.

<a id="%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B"></a>
### Дополнительные методы

Дополнительные статические методы класса AmoAPI:

- `static getInfo(string $with = '', ?string $subdomain = null) :array`  
    Возвращает информацию по аккаунту amoCRM.
    - `$with` - Разделенный запятыми список дополнительных параметров запроса.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен, указанный при авторизации.

- `static getLastResponse(bool $unescapeUnicode = true) :?string`  
    Возвращает последний ответ сервера amoCRM в сыром виде.
    - `$unescapeUnicode` - Декодировать символы UTF-8 \uXXXX в ответе сервера.

- `static request(string $query, string $type = 'GET', array $params = [], ?string $subdomain = null) :?array`
    Выполняет запрос к серверу amoCRM в сыром виде.  
    - `$query` - URL-путь с параметрами запроса.
    - `$type` - метод запроса 'GET' или 'POST'.
    - `$params` - параметры запроса.
    - `$subdomain` - поддомен amoCRM. Если null, то используется поддомен, указанный при авторизации.



<a id="%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B-%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B-%D1%81-%D0%BA%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D0%B0%D0%BC%D0%B8"></a>
### Примеры работы с контактами

```php
use AmoCRM\AmoAPI;
use AmoCRM\AmoContact;

try {
    // Загрузка ВСЕХ контактов amoCRM с возможностью фильтрации
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

    // Создание нового контакта
    $contact1 = new AmoContact([
        'name'                => 'Ганс-Дитрих Геншер',
        'responsible_user_id' => 12345678
    ]);

    // Установка дополнительных полей
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

    // Добавление контакта и получение его ID
    $contact1Id = $contact1->save();

    // Обновление существующего контакта и получение ответа сервера amoCRM
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
    $contact3->addLeads([ 12380925, 12380925 ]);

    // Привязка покупателей к контакту по ID покупателей
    $contact3->addCustomers([ 1237374, 1239658 ]);

    // Добавление тегов к контакту
    $contact3->addTags([ 'тег1', 'тег2' ]);

    // Удаление тегов к контакту
    $contact3->delTags([ 'тег3', 'тег4' ]);

    // Сохранение контакта
    $contact3->save();

    // -------------------------------------------------------------------------

    $items = AmoAPI::getContacts([
        'responsible_user_id' => 12373452
    ]);

    // Пакетная привязка сделки к контаткам
    $contacts = [];
    foreach ($items as $item) {
        $contact = new AmoContact($item);
        $contact->addLeads(12380925);
        $contacts[] = $contact;
    }

    // Пакетное обновление контактов
    AmoAPI::saveObjects($contacts);

} catch (\AmoCRM\AmoAPIException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```


<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор

© 2019-2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия

[The MIT License](https://github.com/andrey-tech/amocrm-api/blob/master/LICENSE)

