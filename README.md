# amoCRM API Wrapper

![amoCRM logo](./assets/amocrm-logo.png)

Обертка на PHP для работы с REST API [amoCRM](https://www.amocrm.ru) с авторизацией по протоколу oAuth 2.0
или по API-ключу пользователя и троттлингом запросов к серверу amoCRM.

**Документация находится в процессе разработки.**

## Содержание

<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Авторизация](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F)
    - [Авторизация по протоколу oAuth 2.0 \(актуальный метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
        - [Обмен кода авторизации на access токен и refresh токен](#%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD-%D0%B8-refresh-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD)
        - [Получение нового access токена по его истечении](#%D0%9F%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D0%B3%D0%BE-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD%D0%B0-%D0%BF%D0%BE-%D0%B5%D0%B3%D0%BE-%D0%B8%D1%81%D1%82%D0%B5%D1%87%D0%B5%D0%BD%D0%B8%D0%B8)
        - [Авторизация по API-ключу пользователя \(устаревший метод\)](#%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-api-%D0%BA%D0%BB%D1%8E%D1%87%D1%83-%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D0%BE%D0%B2%D0%B0%D1%82%D0%B5%D0%BB%D1%8F-%D1%83%D1%81%D1%82%D0%B0%D1%80%D0%B5%D0%B2%D1%88%D0%B8%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4)
    - [Дополнительные параметры и методы](#%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B-%D0%B8-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B)
- [Работа с сущностями amoCRM](#%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D1%8F%D0%BC%D0%B8-amocrm)
    - [Общие методы моделей](#%D0%9E%D0%B1%D1%89%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9)
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

Для авторизации используются статические методы класса \AmoCRM\AmoAPI: oAuth2() или auth().

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D0%BF%D0%BE-%D0%BF%D1%80%D0%BE%D1%82%D0%BE%D0%BA%D0%BE%D0%BB%D1%83-oauth-20-%D0%B0%D0%BA%D1%82%D1%83%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4"></a>
### Авторизация по протоколу oAuth 2.0 ([актуальный метод](https://www.amocrm.ru/developers/content/oauth/oauth))

- `static \AmoCRM\AmoAPI::oAuth2(string $subdomain, string $clientId, string $clientSecret,  
    string $redirectUri, ?string $authCode = null, bool $storeAll = true) :array`  
    - `$subdomain` - поддомен amoCRM.
    - `$clientId` - ID интеграции.
    - `$clientSecret` - секрет интеграции.
    - `$redirectUri` - URI перенаправления.
    - `$authCode` - код авторизации (временный ключ).
    - `$storeAll` - сохранять $clientId, $clientSecret, $redirectUri вместе с полученными access и refresh токенами.

<a id="%D0%9E%D0%B1%D0%BC%D0%B5%D0%BD-%D0%BA%D0%BE%D0%B4%D0%B0-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8-%D0%BD%D0%B0-access-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD-%D0%B8-refresh-%D1%82%D0%BE%D0%BA%D0%B5%D0%BD"></a>
#### Обмен кода авторизации на access токен и refresh токен

Пример авторизации по протоколу oAuth 2.0 и [обмен временного ключа (кода авторизации)](https://www.amocrm.ru/developers/content/oauth/step-by-step#get_access_token),
действующего в течение 20 минут с момента получения, на access и refresh токены.
```php
use \AmoCRM\AmoAPI;

try {
    // Параметры авторизации по протоколу oAuth 2.0
    $clientId     = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
    $clientSecret = 'TFPoaG2A5hp3G3o6opCL8eC9v92Mm0fKQWEHBDwIjedCmVliT4kI3XQcjOOP1s';
    $authCode     = 'eee60208cc09e3ae3506d667228038345b6578a11d4862094655f630074c8c6ed87a9d804d49b5880e';
    $redirectUri  = 'https://www.example.com/oauth2/';
    $subdomain    = 'testsubdomain';
    $storeAll     = true; // Сохранить значения $clientId, $clientSecret, $redirectUri вместе с токенами

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

Пример авторизации по протоколу oAuth 2.0 и получение нового access и refresh токена 
по истечении срока действия существующего access токена.
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
#### Авторизация по API-ключу пользователя ([устаревший метод](https://www.amocrm.ru/developers/content/oauth/old))

- `static \AmoCRM\AmoAPI::oauth(string $login, string $hash, string $subdomain) :array`
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

<a id="%D0%94%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5-%D0%BF%D0%B0%D1%80%D0%B0%D0%BC%D0%B5%D1%82%D1%80%D1%8B-%D0%B8-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B"></a>
### Дополнительные параметры и методы

Дополнительные параметры авторизации и соединения с сервером amoCRM могут быть установлены
через статические свойства класса \AmoCRM\AmoAPI (ниже приведены значения по умолчанию):

- `AmoAPI::$debug = true` Включает отладочный режим с выводом запросов и ответов в STDOUT.
- `AmoAPI::$throttle = 7` Устанавливает максимальное число запросов к amoCRM API в секунду (не более 7).
- `AmoAPI::$verifySSLCerfificate = true` Включает проверку SSL-сертификата сервера amoCRM.
- `AmoAPI::$SSLCertificateFile = 'cacert.pem'` Устанавливает относительный путь к файлу SSL-сертификатов X.509 корневых
удостоверяющих центров для проверки SSL-сертификата сервера amoCRM.
- `AmoAPI::$amoDomain` = 'amocrm.ru'` Устанавливает домен amoCRM для запросов к API.
- `AmoAPI::$amoUserAgent = 'amoCRM-API-client/2.0'` Устанавливает НТТР заголовок UserAgent в запросах к API.
- `AmoAPI::$amoTimeout = 30` Устанавливает таймаут соединения с сервером аmoCRM, секунды.
- `AmoAPI::$accessTokenLeeway = 300` Устанавливает временной запас на истечение срока действия access токен, секунд.
Access токен истекает раньше на указанное число секунд.
- `AmoAPI::$reOAuth2Attempts = 1` Устанавливает максимальное число попыток обновления access токен по истечении его срока действия при ответе сервера '401 Unauthorized'.
- `AmoAPI::$reOAuth2Timeout = 5` Устанавливает таймаут перед обновлением access токен  по истечении его срока действия при ответе сервера '401 Unauthorized', секунд.
- `AmoAPI::$tokensFileDir = 'tokens/'` Устанавливает каталог для хранения файлов с токенами для поддоменов amoCRM.
- `AmoAPI::$reAuthTimeout = 5`  Устанавливает таймаут перед повторной авторизацией по API-ключу пользователя при ответе сервера '401 Unauthorized', секунд.
- `AmoAPI::$reAuthAttempts = 3` Устанавливает максимальное число попыток повторной авторизации по API-ключу пользователя при ответе сервера '401 Unauthorized'.
- `AmoAPI::$cookieFileDir = 'cookies/'` Устанавливает относительный каталог для хранения файлов cookie.
- `AmoAPI::$limitRows = 500` Устанавливает максимальное количество сущностей, выбираемых за один запрос к серверу amoCRM.

Дополнительные статические методы класса \AmoCRM\AmoAPI:

- `static getInfo(string $with = '', $subdomain = null) :array`
    Возвращает информацию по аккаунту amoCRM.
    - `$with` - Разделенный запятыми список дополнительных параметров запроса.
    - `$subdomain` - поддомен amoCRM. Если не указан, то используется поддомен, указанный при авторизации в методе AmoAPI::oAuth2() или AmoAPI::auth().

- `static getLastResponse(bool $unescapeUnicode = true) :?string`  
    Возвращает последний ответ сервера amoCRM в сыром виде.
    - `$unescapeUnicode` - Декодировать символы UTF-8 \uXXXX в ответе сервера.

<a id="%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-%D1%81%D1%83%D1%89%D0%BD%D0%BE%D1%81%D1%82%D1%8F%D0%BC%D0%B8-amocrm"></a>
## Работа с сущностями amoCRM

Работа с сущностями amoCRM реализована через классы-модели. Базовым классом для них является \AmoCRM\AmoObject.

- `AmoContact` Модель контакта.
- `AmoCompany` Модель компании.
- `AmoLead` Модель сделки.
- `AmoNote` Модель примечания.
- `AmoTask` Модель задачи.
- `AmoCatalogElement` Модель элемента каталога.

<a id="%D0%9E%D0%B1%D1%89%D0%B8%D0%B5-%D0%BC%D0%B5%D1%82%D0%BE%D0%B4%D1%8B-%D0%BC%D0%BE%D0%B4%D0%B5%D0%BB%D0%B5%D0%B9"></a>
### Общие методы моделей

- `__construct(array $data = [])` Заполняет модель данными.
- `fillById(int $id)` Заполняет модель данными по ID сущности.
- `getParams() :array` Возвращает параметры модели.
- `getCustomFields(array $ids) :array` Возвращает дополнительные поля сущности по массиву ID полей.
- `getCustomFieldValueById(int $id)` Возвращает значение дополнительного поля сущности по ID поля.
- `setCustomFields(array $params)` Устанавливает дополнительные поля сущности.
- `addTags(array $tags)` Добавляет теги.
- `delTags(array $tags)` Удаляет теги. 

<a id="%D0%9A%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82"></a>
### Контакт

Статические методы класса \AmoCRM\AmoAPI для работы с контактами:

- `static getAllContacts(array $params, ?string $subdomain = null, bool $returnResponse = false) :\Generator`
    Загружает ВСЕ контакты amoCRM с возможностью фильтрации. Возвращает объект \Generator.
    - `$params` - параметры фильтрации.
    - `$subdomain` - поддомен amoCRM. Если не указан, то используется поддомен, указанный при авторизации в методе AmoAPI::oAuth2() или AmoAPI::auth().
    - `$returnResponse` - возвращать полный ответ сервера amoCRM вместо массива параметров контактов.

- `static getContacts(array $params, ?string $subdomain = null, bool $returnResponse = false) :?array`  
    Загружает контакты amoCRM с возможностью фильтрации и постраничной выборки.
    Возвращает массив параметров контактов для заполнения моделей контактов или null.
    - `$params` - параметры фильтрации и постраничной выборки.
    - `$subdomain` - поддомен amoCRM. Если не указан, то используется поддомен, указанный при авторизации в методе AmoAPI::oAuth2() или AmoAPI::auth().
    - `$returnResponse` - возвращать полный ответ сервера amoCRM вместо массива параметров контактов.

Методы класса \AmoCRM\AmoContact:

- `addLeads(array $leads)` Привязывает сделки к контакту по ID сделок.
- `addCustomers(array $customers)` Привязывает покупателей к контакту по ID покупателей.

Примеры работы с контактами.
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

