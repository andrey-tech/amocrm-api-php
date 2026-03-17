<?php
/**
 * Трейт AmoAPIRequest. Отправляет GET/POST запросы к API amoCRM.
 *
 * @author    andrey-tech
 * @copyright 2019-2024 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 3.0.0
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v2.11.0 (15.05.2022) Добавлена поддержка для AJAX-запросов к frontend-методам
 * v3.0.0 (xx.xx.2024) Add support for PHP 8.3, symfony/http-client, symfony/lock (entity + domain locks), custom headers
 *
 */

declare(strict_types=1);

namespace AmoCRM;

use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

trait AmoAPIRequest
{
    /**
     * Флаг включения вывода отладочной информации в лог файл
     */
    public static bool $debug = false;

    /**
     * Объект класса, выполняющего логирование запросов/ответов к API
     */
    public static ?object $debugLogger = null;

    /**
     * Максимальное число запросов к amoCRM API в секунду
     * Не более 7 запросов в секунду!!!
     */
    public static float $throttle = 7;

    /**
     * Флаг включения проверки SSL-сертификата сервера amoCRM
     */
    public static bool $verifySSLCerfificate = true;

    /**
     * Файл SSL-сертификатов X.509 корневых удостоверяющих центров (относительно каталога файла класса AmoAPI)
     * (null - файл, указанный в настройках php.ini)
     */
    public static ?string $SSLCertificateFile = 'cacert.pem';

    /**
     * Домен amoCRM для запросов к API
     */
    public static string $amoDomain = 'amocrm.ru';

    /**
     * UserAgent в запросах к API
     */
    public static string $amoUserAgent = 'amoCRM-API-client/2.0';

    /**
     * Таймаут соединения с сервером аmoCRM, секунды
     */
    public static int $amoConnectTimeout = 30;

    /**
     * Таймаут обмена данными с сервером amoCRM, секунды
     */
    public static int $amoTimeout = 30;

    /**
     * Количество секунд, которое добавляется к параметру updated_at при обновлении сущности
     */
    public static int $updatedAtDelta = 5;

    /**
     * Каталог для хранения lock-файлов (блокировка сущностей и доменов).
     * Используется только когда $lockStore равен null (FlockStore по умолчанию).
     */
    public static string $lockEntityDir = 'lock/';

    /**
     * Хранилище блокировок symfony/lock.
     * Если null, используется FlockStore (файловые блокировки в каталоге $lockEntityDir).
     * Позволяет подключить любой совместимый стор: RedisStore, MemcachedStore и др.
     * Пример: AmoAPI::$lockStore = new RedisStore($redis);
     * Должно быть задано до первого вызова метода, использующего блокировки.
     */
    public static ?PersistingStoreInterface $lockStore = null;

    /**
     * Максимальное число попыток блокировки сущности при обновлении (update) методом AmoObject::save()
     * (0 - блокировка не выполняется)
     */
    public static int $lockEntityAttempts = 10;

    /**
     * Таймаут между попытками блокировки сущности при обновлении (update) методом AmoObject::save(), секунды
     */
    public static int $lockEntityTimeout = 1;

    /**
     * Коды состояния HTTP, соответствующие успешному выполнению запроса
     * @var int[]
     */
    public static array $successStatusCodes = [200, 202, 204];

    /**
     * Кастомные HTTP-заголовки, добавляемые ко всем запросам.
     * Формат: ['Header-Name' => 'value', ...]
     * Пример: AmoAPI::$customHeaders = ['X-My-Header' => 'my-value'];
     * @var array<string, string>
     */
    public static array $customHeaders = [];

    /**
     * Соответствие кодов ошибок и сообщений аmoCRM
     * @var array<int, string>
     */
    protected static array $errorCodes = [

        101 => 'Аккаунт не найден',
        102 => 'POST-параметры должны передаваться в формате JSON',
        103 => 'Параметры не переданы',
        104 => 'Запрашиваемый метод API не найден',

        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',

        // Ошибки возникающие при работе со сделками
        213 => 'Добавление сделок: пустой массив',
        214 => 'Добавление/Обновление сделок: пустой запрос',
        215 => 'Добавление/Обновление сделок: неверный запрашиваемый метод',
        216 => 'Обновление сделок: пустой массив',
        217 => 'Обновление сделок: требуются параметры "id", "updated_at", "status_id", "name"',
        240 => 'Добавление/Обновление сделок: неверный параметр "id" дополнительного поля',

        // Ошибки возникающие при работе с событиями
        218 => 'Добавление событий: пустой массив',
        221 => 'Список событий: требуется тип',
        226 => 'Добавление событий: элемент события данной сущности не найден',
        222 => 'Добавление/Обновление событий: пустой запрос',
        223 => 'Добавление/Обновление событий: неверный запрашиваемый метод (GET вместо POST)',
        224 => 'Обновление событий: пустой массив',
        225 => 'Обновление событий: события не найдены',

        // Ошибки возникающие при работе с контактами
        201 => 'Добавление контактов: пустой массив',
        202 => 'Добавление контактов: нет прав',
        203 => 'Добавление контактов: системная ошибка при работе с дополнительными полями',
        204 => 'Добавление контактов: дополнительное поле не найдено',
        205 => 'Добавление контактов: контакт не создан',
        206 => 'Добавление/Обновление контактов: пустой запрос',
        207 => 'Добавление/Обновление контактов: неверный запрашиваемый метод',
        208 => 'Обновление контактов: пустой массив',
        209 => 'Обновление контактов: требуются параметры "id" и "updated_at"',
        210 => 'Обновление контактов: системная ошибка при работе с дополнительными полями',
        211 => 'Обновление контактов: дополнительное поле не найдено',
        212 => 'Обновление контактов: контакт не обновлён',
        219 => 'Список контактов: ошибка поиска, повторите запрос позднее',

        // Ошибки возникающие при работе с задачами
        227 => 'Добавление задач: пустой массив',
        228 => 'Добавление/Обновление задач: пустой запрос',
        229 => 'Добавление/Обновление задач: неверный запрашиваемый метод',
        230 => 'Обновление задач: пустой массив',
        231 => 'Обновление задач: задачи не найдены',
        232 => 'Добавление событий: ID элемента или тип элемента пустые либо неккоректные',
        233 => 'Добавление событий: по данному ID элемента не найдены некоторые контакты',
        234 => 'Добавление событий: по данному ID элемента не найдены некоторые сделки',
        235 => 'Добавление задач: не указан тип элемента',
        236 => 'Добавление задач: по данному ID элемента не найдены некоторые контакты',
        237 => 'Добавление задач: по данному ID элемента не найдены некоторые сделки',
        238 => 'Добавление контактов: отсутствует значение для дополнительного поля',

        244 => 'Добавление/Обновление/Удаление: нет прав',
        330 => 'Количество привязанных контактов слишком большое',
    ];

    /**
     * Поддомен, использованный при последней авторизации
     */
    protected static ?string $lastSubdomain = null;

    /**
     * Время последнего GET/POST запроса к API по доменам, микросекунды
     * @var array<string, float>
     */
    protected static array $lastRequestTime = [];

    /**
     * Параметры последнего GET/POST запроса к API
     * @var array<string, mixed>
     */
    protected static array $lastRequest = [];

    /**
     * Параметры, использованные при последней авторизации в каждом поддомене
     * @var array<string, array<string, mixed>>
     */
    protected static array $lastAuth = [];

    /**
     * Тело последнего ответа сервера amoCRM
     */
    protected static ?string $lastResult = null;

    /**
     * Счетчик числа запросов к amoCRM для отладочных сообщений
     */
    protected static int $requestCounter = 0;

    /**
     * Уникальное значение ID для метки в отладочных сообщениях
     */
    protected static ?string $uniqId = null;

    /**
     * Фабрика блокировок symfony/lock (инициализируется лениво)
     */
    private static ?LockFactory $lockFactory = null;

    // -----------------------------------------------------------------------------------------------------------------
    // HTTP-запросы
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Отправляет запрос к amoCRM API
     *
     * @param array<string, mixed> $params Параметры запроса
     * @param array<string, string> $headers Кастомные HTTP-заголовки для этого запроса.
     *                                       Перекрывают $customHeaders и стандартные заголовки.
     *                                       Пример: ['X-Trace-Id' => 'abc123', 'Accept-Language' => 'ru']
     * @return array<string, mixed>|null
     * @throws AmoAPIException
     */
    public static function request(
        string $query,
        string $type = 'GET',
        array $params = [],
        ?string $subdomain = null,
        array $headers = []
    ): ?array {
        // Если поддомен не указан, то используем последний поддомен, переданный при авторизации
        if (!isset($subdomain)) {
            $subdomain = self::$lastSubdomain;
            if (!isset($subdomain)) {
                throw new AmoAPIException('Необходима авторизация auth() или oAuth2()');
            }
        }

        // Проверка наличия авторизации в поддомене
        if (!isset(self::$lastAuth[$subdomain])) {
            throw new AmoAPIException(
                "Не выполнена авторизация auth() или oAuth2() для поддомена {$subdomain}"
            );
        }

        // Сохраняем параметры последнего запроса
        self::$lastRequest = [
            'query'     => $query,
            'type'      => $type,
            'params'    => $params,
            'subdomain' => $subdomain,
        ];

        // Увеличиваем счетчик числа отправленных запросов
        self::$requestCounter++;

        $url  = 'https://' . self::getAmoDomain($subdomain) . $query;
        $body = null;

        // Формируем URL/тело/заголовки в зависимости от типа запроса
        switch ($type) {
            case 'GET':
                if (count($params)) {
                    $url .= '?' . http_build_query($params);
                }
                $headers     = self::buildHTTPHeaders($subdomain, false, false, $headers);
                $requestInfo = " (GET: {$url})";
                self::debug('[' . self::$requestCounter . "] GET: {$url}");
                break;

            case 'POST':
                $body = json_encode($params);
                if ($body === false) {
                    throw new AmoAPIException(
                        'Ошибка JSON-кодирования тела запроса (' . json_last_error_msg() . '): ' .
                        print_r($params, true)
                    );
                }
                $headers     = self::buildHTTPHeaders($subdomain, true, false, $headers);
                $requestInfo = ' (POST: ' . $url . ' ' . self::unescapeUnicode($body) . ')';
                self::debug(
                    '[' . self::$requestCounter . "] POST: {$url}" . PHP_EOL . self::unescapeUnicode($body)
                );
                break;

            case 'AJAX':
                $body        = http_build_query($params);
                $headers     = self::buildHTTPHeaders($subdomain, true, true, $headers);
                $requestInfo = ' (POST (AJAX): ' . $url . ' ' . self::unescapeUnicode($body) . ')';
                self::debug(
                    '[' . self::$requestCounter . '] POST (AJAX): ' . $url . PHP_EOL . self::unescapeUnicode($body)
                );
                break;

            default:
                throw new AmoAPIException("Недопустимый метод запроса {$type}");
        }

        $httpMethod = ($type === 'GET') ? 'GET' : 'POST';

        try {
            $result = self::sendRequest($httpMethod, $url, $headers, $body, $subdomain);
        } catch (TransportExceptionInterface $e) {
            throw new AmoAPIException('Ошибка HTTP клиента: ' . $e->getMessage() . $requestInfo);
        }

        $code            = $result['statusCode'];
        self::$lastResult = $result['content'];

        $domain    = self::getAmoDomain($subdomain);
        $deltaTime = sprintf('%0.4f', microtime(true) - (self::$lastRequestTime[$domain] ?? 0.0));
        $response  = self::unescapeUnicode(self::$lastResult);

        self::debug('[' . self::$requestCounter . "] RESPONSE {$deltaTime}s ({$code}):" . PHP_EOL . $response);

        // Если код статуса HTTP 401 (401 Unauthorized), то выполняем, при необходимости, повторную авторизацию
        if ($code === 401) {
            if (self::$lastAuth[$subdomain]['is_oauth2']) {
                $reAuthResult = self::reOAuth2();
            } else {
                $reAuthResult = self::reAuth();
            }
            if ($reAuthResult !== true) {
                return $reAuthResult;
            }
        }

        // Проверяем код статуса HTTP
        if (!in_array($code, self::$successStatusCodes, true)) {
            throw new AmoAPIException(
                self::getErrorMessage($code) . ": {$requestInfo} (Response: {$response})",
                $code
            );
        }

        // Если код статуса HTTP 204 (No Content), в ответе нет тела
        if ($code === 204) {
            return null;
        }

        // Декодируем тело ответа
        $decoded = json_decode((string) self::$lastResult, true);
        if (is_null($decoded)) {
            throw new AmoAPIException(
                'Ошибка JSON-декодирования тела ответа (' . json_last_error_msg() . "): {$response}"
            );
        }

        // Проверяем наличие полей ошибок в ответе
        if (isset($decoded['_embedded']['errors']) && count($decoded['_embedded']['errors'])) {
            $errors = $decoded['_embedded']['errors'];
            $items  = $decoded['_embedded']['items'] ?? [];

            // Поиск кодов ошибок при обновлении/добавлении
            $codes = [];
            if (isset($errors['update'])) {
                $codes = array_merge($codes, array_column($errors['update'], 'code'));
            }
            if (isset($errors['add'])) {
                $codes = array_merge($codes, array_column($errors['add'], 'code'));
            }

            if (count($codes)) {
                $errorMessage = self::getErrorMessage($codes);
                $exception    = new AmoAPIException(
                    "Ошибки: {$errorMessage} {$requestInfo} (Response: {$response})",
                    reset($codes)
                );
                $exception->setItems($items);
                $exception->setErrors($errors);
                throw $exception;
            }

            // Поиск текстовых сообщений об ошибках
            $errorMessages = [];
            if (isset($errors['update'])) {
                $errorMessages = array_merge(
                    $errorMessages,
                    array_map(
                        static fn ($index, $value): string => "{$index} -> {$value}",
                        array_keys($errors['update']),
                        $errors['update']
                    )
                );
            }
            if (isset($errors['add'])) {
                $errorMessages = array_merge(
                    $errorMessages,
                    array_map(
                        static fn ($index, $value): string => "{$index} -> {$value}",
                        array_keys($errors['add']),
                        $errors['add']
                    )
                );
            }

            if (count($errorMessages)) {
                $exception = new AmoAPIException(
                    'Ошибки (ID сущности -> сообщение об ошибке): ' .
                    implode(', ', $errorMessages) . $requestInfo . " (Response: {$response})",
                    $code
                );
                $exception->setItems($items);
                $exception->setErrors($errors);
                throw $exception;
            }

            $exception = new AmoAPIException(
                "Ошибка: Неизвестная ошибка {$requestInfo} (Response: {$response})",
                $code
            );
            $exception->setItems($items);
            $exception->setErrors($errors);
            throw $exception;
        }

        return $decoded;
    }

    /**
     * Выполняет HTTP-запрос через symfony/http-client.
     * Перед отправкой захватывает domain-lock, гарантирующий последовательное
     * выполнение запросов к одному домену.
     *
     * @param array<string, string> $headers
     * @return array{statusCode: int, content: string}
     * @throws TransportExceptionInterface
     * @throws AmoAPIException
     */
    protected static function sendRequest(
        string $method,
        string $url,
        array $headers,
        ?string $body,
        string $subdomain
    ): array {
        $domain = self::getAmoDomain($subdomain);

        // Захватываем блокировку домена — только один запрос к домену одновременно
        $domainLock = self::getLockFactory()->createLock(
            'domain.' . $domain,
            ttl: null,
            autoRelease: true
        );
        $domainLock->acquire(true); // блокирующее ожидание

        try {
            // Троттлинг: выдерживаем минимальный интервал между запросами к домену
            self::throttle($domain);

            $options = [
                'headers'      => array_merge(['User-Agent' => self::$amoUserAgent], $headers),
                'timeout'      => self::$amoConnectTimeout,
                'max_duration' => self::$amoTimeout,
                'verify_peer'  => self::$verifySSLCerfificate,
                'verify_host'  => self::$verifySSLCerfificate,
            ];

            if (self::$verifySSLCerfificate && self::$SSLCertificateFile) {
                $options['cafile'] = __DIR__ . DIRECTORY_SEPARATOR . self::$SSLCertificateFile;
            }

            if ($body !== null) {
                $options['body'] = $body;
            }

            // Принудительно TLSv1.2, как требует amoCRM
            $curlOptions = [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2];

            // Для авторизации по API-ключу используем cookie-файл (legacy)
            if (!self::$lastAuth[$subdomain]['is_oauth2']) {
                $cookieFilePath = __DIR__ . DIRECTORY_SEPARATOR . self::$cookieFileDir;
                self::checkDir($cookieFilePath);
                $cookieFile                     = $cookieFilePath . $domain . '.txt';
                $curlOptions[CURLOPT_COOKIEFILE] = $cookieFile;
                $curlOptions[CURLOPT_COOKIEJAR]  = $cookieFile;
            }

            $options['extra']['curl'] = $curlOptions;

            $client   = HttpClient::create();
            $response = $client->request($method, $url, $options);

            return [
                'statusCode' => $response->getStatusCode(),
                'content'    => $response->getContent(throw: false),
            ];
        } finally {
            $domainLock->release();
        }
    }

    /**
     * Обеспечивает троттлинг запросов к API amoCRM (per-domain).
     */
    private static function throttle(string $domain): void
    {
        // Если троттлинг отключён (throttle <= 0), пропускаем без деления на ноль
        if (self::$throttle <= 0.0) {
            self::$lastRequestTime[$domain] = microtime(true);
            return;
        }

        $lastTime = self::$lastRequestTime[$domain] ?? 0.0;
        $usleep   = (int) (1E6 * ($lastTime + 1.0 / self::$throttle - microtime(true)));

        if ($usleep > 0) {
            $throttleTime = sprintf('%0.4f', $usleep / 1E6);
            self::debug(
                '[' . self::$requestCounter . '] THROTTLE (' . self::$throttle . ") {$throttleTime}s"
            );
            usleep($usleep);
        }

        self::$lastRequestTime[$domain] = microtime(true);
    }

    /**
     * Формирует массив HTTP-заголовков для запроса.
     *
     * Приоритет (от низшего к высшему):
     *   стандартные (Authorization, Content-Type)
     *   → AmoAPI::$customHeaders (глобальные)
     *   → $requestHeaders (заголовки конкретного вызова request())
     *
     * @param array<string, string> $requestHeaders Заголовки конкретного запроса
     * @return array<string, string>
     */
    protected static function buildHTTPHeaders(
        string $subdomain,
        bool $isPost,
        bool $isAjax = false,
        array $requestHeaders = []
    ): array {
        $headers = [];

        // OAuth 2.0 Authorization header
        if (self::$lastAuth[$subdomain]['is_oauth2']) {
            if (!empty(self::$lastAuth[$subdomain]['access_token'])) {
                $headers['Authorization'] = 'Bearer ' . self::$lastAuth[$subdomain]['access_token'];
            }
        }

        // Content-Type для POST
        if ($isPost) {
            $headers[$isAjax ? 'X-Requested-With' : 'Content-Type'] = $isAjax
                ? 'XMLHttpRequest'
                : 'application/json';
        }

        // Глобальные кастомные заголовки, затем заголовки конкретного запроса
        return array_merge($headers, self::$customHeaders, $requestHeaders);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Блокировки (symfony/lock)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Возвращает LockFactory (инициализация при первом вызове).
     * Использует $lockStore если задан, иначе FlockStore ($lockEntityDir).
     */
    private static function getLockFactory(): LockFactory
    {
        if (self::$lockFactory === null) {
            if (self::$lockStore !== null) {
                $store = self::$lockStore;
            } else {
                $dir = __DIR__ . DIRECTORY_SEPARATOR . self::$lockEntityDir;
                self::checkDir($dir);
                $store = new FlockStore($dir);
            }
            self::$lockFactory = new LockFactory($store);
        }

        return self::$lockFactory;
    }

    /**
     * Выполняет блокировку сущности при обновлении (update) методом AmoObject::save().
     * Использует symfony/lock поверх FlockStore.
     *
     * @throws AmoAPIException
     */
    public static function lockEntity(object $amoObject): ?Lock
    {
        if (!self::$lockEntityAttempts) {
            return null;
        }

        // Ключ блокировки: "<id>.<тип>" например "123.lead"
        $className = substr(strtolower($amoObject::class), 10);
        $key       = $amoObject->id . '.' . $className;

        $lock = self::getLockFactory()->createLock($key, ttl: null);

        for ($attempt = 1; $attempt <= self::$lockEntityAttempts; $attempt++) {
            if ($lock->acquire(false)) {
                return $lock;
            }

            self::debug(
                '[' . self::$requestCounter . "] LOCK {$key} #{$attempt} (" . self::$lockEntityTimeout . 's)'
            );

            sleep(self::$lockEntityTimeout);
        }

        self::debug(
            '[' . self::$requestCounter . "] LOCK {$key} attempts exceeded (" . self::$lockEntityAttempts . ')'
        );

        return null;
    }

    /**
     * Выполняет разблокировку сущности при обновлении (update) методом AmoObject::save().
     */
    public static function unlockEntity(?Lock $lock): void
    {
        $lock?->release();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Вспомогательные методы
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Возвращает сообщение об ошибках amoCRM, соответствующее коду ошибок
     *
     * @param int|int[] $codes Код или массив кодов ошибок
     */
    protected static function getErrorMessage(int|array $codes): string
    {
        if (!is_array($codes)) {
            $codes = [$codes];
        }

        $errorMessage  = 'Ошибка ';
        $errorMessage .= implode(', ', array_map(static function (int $code): string {
            $message = self::$errorCodes[$code] ?? 'Неизвестная ошибка';
            return "{$code} {$message}";
        }, $codes));

        return $errorMessage;
    }

    /**
     * Возвращает строку с декодированными символами unicode \uXXXX
     * @see https://stackoverflow.com/questions/2934563
     */
    protected static function unescapeUnicode(string|false $string): string
    {
        if ($string === false) {
            return '';
        }

        return preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            static fn (array $match): string => mb_convert_encoding(
                pack('H*', $match[1]),
                'UTF-8',
                'UCS-2BE'
            ),
            (string) $string
        ) ?? (string) $string;
    }

    /**
     * Проверяет наличие каталога и создаёт его рекурсивно при отсутствии.
     *
     * @throws AmoAPIException
     */
    protected static function checkDir(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new AmoAPIException("Не удалось рекурсивно создать каталог: {$directory}");
        }
    }

    /**
     * Возвращает тело последнего ответа сервера amoCRM
     */
    public static function getLastResponse(bool $unescapeUnicode = true): string
    {
        $result = self::$lastResult ?? '';
        return $unescapeUnicode ? self::unescapeUnicode($result) : $result;
    }

    /**
     * Выводит отладочные сообщения
     */
    protected static function debug(string $message = ''): void
    {
        $timeString = (new DateTime())->format('Y-m-d H:i:s.u P');
        $uniqId  = self::getUniqId();
        $message = "*** {$uniqId} [{$timeString}]" . PHP_EOL . $message . PHP_EOL . PHP_EOL;

        if (self::$debug) {
            echo $message;
        }

        if (isset(self::$debugLogger)) {
            self::$debugLogger->debug($message);
        }
    }

    /**
     * Возвращает уникальное значение ID для метки в отладочных сообщениях
     */
    protected static function getUniqId(int $length = 7): string
    {
        if (!isset(self::$uniqId)) {
            self::$uniqId = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $length);
        }

        return self::$uniqId;
    }

    /**
     * Возвращает полное имя домена amoCRM
     */
    public static function getAmoDomain(string $subdomain): string
    {
        if (preg_match('/\.amocrm\.(ru|com)$/', $subdomain)) {
            return $subdomain;
        }

        return $subdomain . '.' . self::$amoDomain;
    }
}
