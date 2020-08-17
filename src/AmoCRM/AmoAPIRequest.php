<?php
/**
 * Трейт AmoAPIRequest. Отправляет GET/POST запросы к API amoCRM.
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 2.10.1
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.1.0 (05.07.2019) Добавлен обработчик ошибки 401 Unautorized
 * v1.2.0 (05.07.2019) Улучшен обработчик ошибки 401 Unautorized для нескольких поддоменов
 * v1.2.1 (05.07.2019) Добавлена проверка авторизации в поддомене
 * v1.3.0 (05.07.2019) Добавлен параметр reAuthTimeout
 * v1.3.1 (19.08.2019) Исправлен поиск ошибок в ответе
 * v1.3.2 (02.10.2019) Исправлены сообщения об ошибках JSON
 * v1.4.0 (13.11.2019) Массив ошибок и ответов добавляется в объект AmoAPIException
 * v1.5.0 (04.04.2020) Добавлена опция cURL CURLOPT_SSLVERSION
 * v2.0.0 (07.04.2020) Добавлена авторизация по протоколу OAuth 2.0.
 *                     Параметр $throttle теперь это максимальное число запросов в секунду.
 *                     Рефракторинг
 * v2.1.0 (09.04.2020) Добавлен метод getLastResponse()
 * v2.1.1 (10.04.2020) Уточнены сообщения об ошибках в request()
 * v2.1.2 (11.04.2020) Уточнены коды исключений в request()
 * v2.2.0 (16.05.2020) Добавлен вывод времени ответа сервера в отладочные сообщения
 * v2.3.0 (21.05.2020) Добавлен вывод отладочных сообщений в лог файл
 * v2.4.0 (21.05.2020) Добавлен параметр $updatedAtDelta
 * v2.5.0 (25.05.2020) Добавлена возможность вывода отладочных сообщений в STDOUT
 * v2.6.0 (26.05.2020) Добавлена блокировка сущностей при обновлении (update) методом AmoObject::save()
 * v2.6.1 (10.06.2020) Исправлено приведение к целому в методе throttle()
 * v2.7.0 (14.06.2020) Добавлен параметр $amoConnectTimeout
 * v2.7.1 (08.07.2020) Исправлена обработка пустого значения каталога хранения cookies. Рефракторинг
 * v2.8.0 (10.07.2020) Имя файлов cookies теперь включает полное имя домена amoCRM
 * v2.9.0 (11.07.2020) Добавлена возможность передаче в параметре $subdomain полного домена amoCRM
 * v2.9.1 (15.07.2020) Метод getAmoDomain() теперь публичный
 * v2.9.2 (19.07.2020) Исправлено сообщение об ошибке с кодом 244
 * v2.9.3 (07.08.2020) Сообщение об ошибке дополнено параметрами запроса
 * v2.10.0 (16.08.2020) Добавлена поддержка для класса, выполняющего логирование запросов/ответов к API
 * v2.10.1 (17.08.2020) Добавлен use DateTime
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

use DateTime;
use DateTimeZone;

trait AmoAPIRequest
{
    /**
     * Флаг включения вывода отладочной информации в лог файл
     * @var bool
     */
    public static $debug = false;

    /**
     * Объект класса, выполняющего логирование запросов/ответов к API
     * @var object
     */
    public static $debugLogger;

    /**
     * Максимальное число запросов к amoCRM API в секунду
     * Не более 7 запросов в секунду!!!
     * @var float
     */
    public static $throttle = 7;

    /**
     * Флаг включения проверки SSL-сертификата сервера amoCRM
     * @var bool
     */
    public static $verifySSLCerfificate = true;

    /**
     * Файл SSL-сертификатов X.509 корневых удостоверяющих центров (относительно каталога файла класса AmoAPI)
     * (null - файл, указанный в настройках php.ini)
     * @var string|null
     */
    public static $SSLCertificateFile = 'cacert.pem';

    /**
     * Домен amoCRM для запросов к API
     * @var string
     */
    public static $amoDomain = 'amocrm.ru';

    /**
     * UserAgent в запросах к API
     * @var string
     */
    public static $amoUserAgent = 'amoCRM-API-client/2.0';

    /**
     * Таймаут соединения с сервером аmoCRM, секунды
     * @var integer
     */
    public static $amoConnectTimeout = 30; // Секунды

    /**
     * Таймау обмена данными с сервером amoCRM, секунды
     * @var integer
     */
    public static $amoTimeout = 30; // Секунды

    /**
     * Количество секунд, которое добавляется к параметру updated_at при обновлении сущности
     * @var int
     */
    public static $updatedAtDelta = 5; // Секунды

    /**
     * Каталог для хранения lock файлов блокировки сущностей при обновлении (update) методом AmoObject::save()
     * @var string
     */
    public static $lockEntityDir = 'lock/';

    /**
     * Максимальное число попыток блокировки сущности при обновлении (update) методом AmoObject::save()
     * (0 - блокировка не выполняется)
     * @var int
     */
    public static $lockEntityAttempts = 10;

    /**
     * Таймаут между попытками блокировки сущности при обновлении (update) методом AmoObject::save(), секунды
     * @var int
     */
    public static $lockEntityTimeout = 1; // Секунды

    /**
     * Соответствие кодов ошибок и сообщений аmoCRM
     * @var array
     */
    protected static $errorCodes = [

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
        330 => 'Количество привязанных контактов слишком большое'
    ];

    /**
     * Поддомен, использованный при последней авторизации
     * @var string
     */
    protected static $lastSubdomain;

    /**
     * Время последнего GET/POST запроса к API, микросекунды
     * @var float
     */
    protected static $lastRequestTime = 0;

    /**
     * Параметры последнего GET/POST запроса к API
     * @var array
     */
    protected static $lastRequest = [];

    /**
     * Параметры, использованные при последней авторизации в каждом поддомене
     * @var array
     */
    protected static $lastAuth = [];

    /**
     * Тело последнего ответа сервера amoCRM
     * @var string/null
     */
    protected static $lastResult;

    /**
     * Счетчик числа запросов к amoCRM для отладочных сообщений
     * @var integer
     */
    protected static $requestCounter = 0;

    /**
     * Уникальное значение ID для метки в отладочных сообщениях
     * @var string
     */
    protected static $uniqId;

    /**
     * Устанавливает параметры по умолчанию для библиотеки libcurl
     * @param resource $curl Ресурс cURL
     * @param string $subdomain Поддомен amoCRM
     * @return void
     * @throws AmoAPIException
     */
    protected static function setDefaultCurlOptions($curl, string $subdomain)
    {
        // Устанавливаем основные опции curl
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, self::$amoUserAgent);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$amoTimeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$amoConnectTimeout);
        // @see https://www.amocrm.ru/developers/content/api/recommendations
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        // Если авторизация по API-ключу пользователя, то устанавливаем файл cookies
        if (! self::$lastAuth[ $subdomain ]['is_oauth2']) {
            // Формируем полное имя файла cookies
            $cookieFilePath = __DIR__ . DIRECTORY_SEPARATOR . self::$cookieFileDir;
            self::checkDir($cookieFilePath);
            $cookieFile = $cookieFilePath . self::getAmoDomain($subdomain) . '.txt';
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
        }

        // Включение или отключение проверки SSL-сертификата сервера amoCRM
        if (! empty(self::$verifySSLCerfificate)) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            if (self::$SSLCertificateFile) {
                $SSLCertificateFile = __DIR__ . DIRECTORY_SEPARATOR . self::$SSLCertificateFile;
                curl_setopt($curl, CURLOPT_CAINFO, $SSLCertificateFile);
            }
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }
    }

    /**
     * Отправляет запрос к amoCRM API
     * @param string $query Путь в строке запроса
     * @param string $type Тип запроса GET|POST
     * @param array $params Параметры запроса
     * @param string|null $subdomain Поддомен amoCRM
     * @return array|null
     * @throws AmoAPIException
     */
    public static function request(string $query, string $type = 'GET', array $params = [], $subdomain = null)
    {
        // Если поддомен не указан, то используем последний поддомен, переданный при авторизации
        if (! isset($subdomain)) {
            $subdomain = self::$lastSubdomain;
            if (! isset($subdomain)) {
                throw new AmoAPIException("Необходима авторизация auth() или oAuth2()");
            }
        }

        // Проверка наличия авторизации в поддомене
        if (! isset(self::$lastAuth[ $subdomain ])) {
            throw new AmoAPIException("Не выполнена авторизация auth() или oAuth2() для поддомена {$subdomain}");
        }

        // Сохраняем параметры последнего запроса
        self::$lastRequest = [
            'query'     => $query,
            'type'      => $type,
            'params'    => $params,
            'subdomain' => $subdomain
        ];
        
        // Увеличиваем счетчик числа отправленных запросов
        self::$requestCounter++;

        // Инициализируем curl и устанавливаем параметры по умолчанию
        $curl = curl_init();
        self::setDefaultCurlOptions($curl, $subdomain);

        // Формируем URL запроса
        $url = 'https://' . self::getAmoDomain($subdomain) . $query;

        // Устанавливаем параметры для запроса методом GET или POST
        switch ($type) {
            case 'GET':
                // Кодируем строку запроса
                if (count($params)) {
                    $url .= '?' . http_build_query($params);
                }

                // Добавляем заголовки HTTP
                self::setHTTPHeaders($curl, $subdomain, false);
    
                // Отладочная информация
                $requestInfo = " (GET: {$url})";
                self::debug('['. self::$requestCounter . "] GET: {$url}");
    
                break;
    
            case 'POST':
                // Кодируем тело запроса
                $jsonParams = json_encode($params);
                if ($jsonParams === false) {
                    $errorMessage = json_last_error_msg();
                    throw new AmoAPIException(
                        "Ошибка JSON-кодирования тела запроса ({$errorMessage}): " . print_r($params, true)
                    );
                }
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonParams);

                // Добавляем заголовки HTTP
                self::setHTTPHeaders($curl, $subdomain, true);

                // Отладочная информация
                $jsonParams = self::unescapeUnicode($jsonParams);
                $requestInfo = " (POST: {$url} {$jsonParams})";
                self::debug('['. self::$requestCounter . "] POST: {$url}" . PHP_EOL . $jsonParams);
    
                break;
    
            // Допустимые методы запроса только GET и POST
            default:
                throw new AmoAPIException("Недопустимый метод запроса {$type}");
        }

        // Устанавливаем URL запроса
        curl_setopt($curl, CURLOPT_URL, $url);

        // Отправляем запрос и сохраняем ответ
        self::$lastResult = self::throttleCurl($curl);
        $deltaTime = sprintf('%0.4f', microtime(true) - self::$lastRequestTime);
        $result = self::unescapeUnicode(self::$lastResult);

        // Извлекаем код статуса HTTP и номер ошибки cURL
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        self::debug('['. self::$requestCounter . "] RESPONSE {$deltaTime}s ({$code}):" .PHP_EOL . $result);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);

        // Проверяем наличие ошибки cURL
        if ($errno) {
            throw new AmoAPIException("Oшибка cURL ({$errno}): {$error} {$requestInfo}");
        }

        // Если код статуса HTTP 401 (401 Unauthorized), то выполняем, при необходимости, повторную авторизацию
        if ($code === 401) {
            if (self::$lastAuth[ $subdomain ]['is_oauth2']) { // Если авторизация по протоколу OAuth 2.0
                $response = self::reOAuth2();
            } else { // Если авторизация по API ключу пользователя
                $response = self::reAuth();
            }
            if ($response !== true) {
                return $response;
            }
        }

        // Проверяем код статуса HTTP
        if ($code !== 200 && $code !== 204) {
            throw new AmoAPIException(self::getErrorMessage($code) . ": {$requestInfo} (Response: {$result})", $code);
        }

        // Если код статуса HTTP 204 (204 No Content), то в ответе были переданы только заголовки без тела сообщения
        if ($code === 204) {
            return null;
        }

        // Декодируем тело ответа
        $response = json_decode(self::$lastResult, true);
        if (is_null($response)) {
            $errorMessage = json_last_error_msg();
            throw new AmoAPIException("Ошибка JSON-декодирования тела ответа ($errorMessage): {$result}");
        }

        // Проверяем наличие полей ошибок в ответе
        if (isset($response['_embedded']['errors']) && count($response['_embedded']['errors'])) {
            // Извлекаем массив ошибок и результатов
            $errors = $response['_embedded']['errors'];
            $items = $response['_embedded']['items'] ?? [];

            // Поиск полей кодов ошибок по ключу 'code' в массиве ошибок при обновлении сущностей
            $codes = [];
            if (isset($errors['update'])) {
                $codes = array_merge($codes, array_column($errors['update'], 'code'));
            }

            // Поиск полей кодов ошибок по ключу 'code' в массиве ошибок при добавлении сущностей
            if (isset($errors['add'])) {
                $codes = array_merge($codes, array_column($errors['add'], 'code'));
            }

            // Если поля кодов ошибок найдены, то сохраняем ошибки и результаты в \AmoAPIException
            if (count($codes)) {
                $errorMessage = self::getErrorMessage($codes);
                $exception = new AmoAPIException(
                    "Ошибки: {$errorMessage} {$requestInfo} (Response: {$result})",
                    reset($codes)
                );
                $exception->setItems($items);
                $exception->setErrors($errors);
                throw $exception;
            }

            // Поиск сообщений об ошибках при обновлении сущностей
            $errorMessages = [];
            if (isset($errors['update'])) {
                $errorMessages = array_merge(
                    $errorMessages,
                    array_map(function ($index, $value) {
                        return "{$index} -> {$value}";
                    }, array_keys($errors['update']), $errors['update'])
                );
            }

            // Поиск сообщений об ошибках при добавлении сущностей
            if (isset($errors['add'])) {
                $errorMessages = array_merge(
                    $errorMessages,
                    array_map(function ($index, $value) {
                        return "{$index} -> {$value}";
                    }, array_keys($errors['add']), $errors['add'])
                );
            }

            // Если в списке сообщений об ошибках есть сообщения, сохраняем ошибки и результаты в \AmoAPIException
            if (count($errorMessages)) {
                $exception = new AmoAPIException(
                    'Ошибки (ID сущности -> сообщение об ошибке): ' .
                    implode(', ', $errorMessages) . $requestInfo . " (Response: {$result})",
                    $code
                );
                $exception->setItems($items);
                $exception->setErrors($errors);
                throw $exception;
            }

            // Если неизвестные ошибки, то сохраняем ошибки и результаты в \AmoAPIException
            $exception = new AmoAPIException("Ошибка: Неизвестная ошибка {$requestInfo} (Response: {$result})", $code);
            $exception->setItems($items);
            $exception->setErrors($errors);
            throw $exception;
        }

        // Возврашаем декодированный результат
        return $response;
    }

    /**
     * Устанавливает НТТР заголовки GET/POST-запроса
     * @param resource $curl Ресурс cURL
     * @param string $subdomain Поддомен amoCRM
     * @param bool $isPost Метод запроса POST?
     * @return void
     */
    protected static function setHTTPHeaders($curl, string $subdomain, bool $isPost)
    {
        // Список НТТP-заголовков
        $headers = [];

        // Если авторизация по протоколу OAuth 2.0, то добавляем заголовок Authorization:
        if (self::$lastAuth[ $subdomain ]['is_oauth2']) {
            // Если установлен access token
            if (! empty(self::$lastAuth[ $subdomain ]['access_token'])) {
                $headers[] = 'Authorization: Bearer ' . self::$lastAuth[ $subdomain ]['access_token'];
            }
        }

        // Если метод запроса POST, то добавляем заголовок Content-Type:
        if ($isPost) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Возвращает сообщение об ошибках amoCRM, соответствующее коду ошибок
     * @param integer|array $codes Массив кодов ошибок
     * @return string
     */
    protected static function getErrorMessage($codes) :string
    {
        // Приведение к массиву
        if (! is_array($codes)) {
            $codes = [ $codes ];
        }

        // Объединяем сообщения об ошибках в строку через запятую
        $errorMessage = "Ошибка ";
        $errorMessage .= implode(', ', array_map(function ($code) {
            $message = self::$errorCodes[ $code ] ?? 'Неизвестная ошибка';
            return "{$code} {$message}";
        }, $codes));

        return $errorMessage;
    }

    /**
     * Обеспечивает троттлинг запросов к API amoCRM
     * @param resource $curl Ресурс curl
     * @return string|false - при ошибке curl
     */
    protected static function throttleCurl($curl)
    {
        do {
            // Вычисляем необходимое время задержки перед отправкой запроса, микросекунды
            $usleep = intval(1E6 * (self::$lastRequestTime + 1/self::$throttle - microtime(true)));
            if ($usleep <= 0) {
                break;
            }

            $throttleTime = sprintf('%0.4f', $usleep/1E6);
            self::debug('['. self::$requestCounter . '] THROTTLE (' . self::$throttle . ") {$throttleTime}s");
            usleep($usleep);
        } while (false);

        self::$lastRequestTime = microtime(true);

        return curl_exec($curl);
    }

    /**
     * Возвращает строку с декодированными символами unicode \uXXXX
     * @param string $string Строка с символами unicode \uXXXX
     * @return string
     * @see https://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
     */
    protected static function unescapeUnicode($string)
    {
        // Проверка на случай если cURL вернул false при ошибке cURL
        if ($string === false) {
            return '';
        }

        return preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            },
            (string) $string
        );
    }

    /**
     * Проверяет наличие каталога для сохранения файла и создает каталог при его отсутствии рекурсивно
     * @param string $directory Полный путь к каталогу
     * @return void
     * @throws AmoAPIException
     */
    protected static function checkDir(string $directory)
    {
        // Выходим, если каталог уже есть (is_dir кешируется PHP)
        if (is_dir($directory)) {
            return;
        }

        // Создаем новый каталог рекурсивно
        if (! mkdir($directory, $mode = 0755, $recursive = true)) {
            throw new AmoAPIException("Не удалось рекурсивно создать каталог: {$directory}");
        }
    }

    /**
     * Возвращает тело последниего ответа сервера amoCRM
     * @param  bool $unescapeUnicode Декодировать символы unicode \uXXXX
     * @return mixed
     */
    public static function getLastResponse(bool $unescapeUnicode = true)
    {
        return $unescapeUnicode ? self::unescapeUnicode(self::$lastResult) : self::$lastResult;
    }

    /**
     * Выводит отладочные сообщения
     * @param string $message Текст сообщения
     * @return void
     */
    protected static function debug(string $message = '')
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)));
        $timeZone = new DateTimeZone(date_default_timezone_get());
        $dateTime->setTimeZone($timeZone);
        $timeString = $dateTime->format('Y-m-d H:i:s.u P');

        $uniqId = self::getUniqId();
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
     * @param  int $length Длина ID
     * @return string
     */
    protected static function getUniqId(int $length = 7) :string
    {
        if (! isset(self::$uniqId)) {
            self::$uniqId = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $length);
        }
        return self::$uniqId;
    }

    /**
     * Выполняет блокировку сущности при обновлении (update) методом AmoObject::save()
     * @param object $amoObject Объект \AmoCRM\AmoObject
     * @return array|null
     * @throws AmoAPIException
     */
    public static function lockEntity($amoObject)
    {
        // Выходим, если блокировка отключена
        if (! self::$lockEntityAttempts) {
            return null;
        }

        // Проверяем каталог для хранения файлов блокировки
        $dir = __DIR__ . DIRECTORY_SEPARATOR . self::$lockEntityDir;
        self::checkDir($dir);
        
        // Формируем полное имя файла блокировки
        $fileName = $amoObject->id . '.' . substr(strtolower(get_class($amoObject)), 10);
        $file = $dir . $fileName;

        $attempt = 0;
        while ($attempt < self::$lockEntityAttempts) {
            $attempt++;

            $fh = @fopen($file, 'c');
            if ($fh === false) {
                throw new AmoAPIException("Не удалось открыть lock-файл {$fileName}");
            }

            if (flock($fh, LOCK_EX|LOCK_NB)) {
                return [ 'fh' => $fh, 'file' => $file, 'fileName' => $fileName ];
            }
            
            if (! fclose($fh)) {
                throw new AmoAPIException("Не удалось закрыть lock-файл {$fileName}");
            }

            self::debug(
                '['. self::$requestCounter . "] LOCK {$fileName} #{$attempt} (" . self::$lockEntityTimeout . 's)'
            );

            sleep(self::$lockEntityTimeout);
        }

        self::debug(
            '['. self::$requestCounter . "] LOCK {$fileName} attempts exceeded (" . self::$lockEntityAttempts . ')'
        );

        return null;
    }

    /**
     * Выполняет разблокировку сущности при обновлении (update) методом AmoObject::save()
     * @param array|null $lock Параметры блокировки сущности
     * @return void
     * @throws AmoAPIException
     */
    public static function unlockEntity($lock)
    {
        if (empty($lock)) {
            return;
        }

        if (! flock($lock['fh'], LOCK_UN)) {
            throw new AmoAPIException("Не удалось разблокировать lock-файл {$lock['fileName']}");
        }

        if (! fclose($lock['fh'])) {
            throw new AmoAPIException("Не удалось закрыть lock-файл {$lock['fileName']}");
        }

        if (! @unlink($lock['file'])) {
            throw new AmoAPIException("Не удалось удалить lock-файл {$lock['fileName']}");
        }
    }

    /**
     * Возвращает полное имя домена amoCRM
     * @param  string $subdomain Поддомен или полный домен амоCRM
     * @return string
     */
    public static function getAmoDomain(string $subdomain) :string
    {
        if (preg_match('/\.amocrm\.(ru|com)$/', $subdomain)) {
            return $subdomain;
        }

        return $subdomain . '.' . self::$amoDomain;
    }
}
