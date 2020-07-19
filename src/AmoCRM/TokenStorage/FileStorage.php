<?php

/**
 * Класс FileStorage. Реализует хранение токенов в JSON-файле
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.2
 *
 * v1.0.0 (08.07.2020) Начальный релиз
 * v1.1.0 (17.07.2020) Добавлен метод hasTokens()
 * v1.1.1 (16.07.2020) Исправлена разделяемая блокировка JSON-файла. Рефракторинг
 * v1.1.2 (19.07.2020) Исправлено сообщение об ошибке
 *
 */

declare(strict_types = 1);

namespace AmoCRM\TokenStorage;

class FileStorage implements TokenStorageInterface
{
    /**
     * Каталог для хранения файлов с токенами
     * @var string
     */
    protected $storageFolder;

    /**
     * Конструктор
     * @param string $storageFolder Каталог для хранения файлов с токенами
     */
    public function __construct(string $storageFolder = 'tokens/')
    {
        $this->storageFolder = $storageFolder;
    }

    /**
     * Сохраняет токены в JSON-файл
     * @param  array  $tokens Токены для сохранения
     * @param  string $domain Домен amoCRM
     * @return void
     * @throws TokenStorageException
     */
    public function save(array $tokens, string $domain)
    {
        $tokensFile =  $this->getTokensFileName($domain);

        // Кодируем параметры в JSON
        $jsonTokens = json_encode($tokens, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        if ($jsonTokens === false) {
            $errorMessage = json_last_error_msg();
            throw new TokenStorageException("Ошибка JSON-кодирования токенов ({$errorMessage}): " . print_r($tokens, true));
        }

        // Записываем в файл
        if (! @file_put_contents($tokensFile, $jsonTokens, LOCK_EX)) {
            throw new TokenStorageException("Не удалось записать в файл токенов '{$tokensFile}'");
        }
    }

    /**
     * Загружает токены из JSON-файла
     * @param  string $domain Домен amoCRM
     * @return array|null
     * @throws TokenStorageException
     */
    public function load(string $domain)
    {
        $tokensFile =  $this->getTokensFileName($domain);

        // Выходим, если файл с токенами не существует
        if (! is_file($tokensFile)) {
            return null;
        }

        $fh = @fopen($tokensFile, 'r');
        if ($fh === false) {
            throw new TokenStorageException("Не удалось открыть файл токенов '{$tokensFile}'");
        }

        if (! flock($fh, LOCK_SH)) {
            throw new TokenStorageException("Не удалось получить разделяемую блокировку файла токенов '{$tokensFile}'");
        }

        // Загружаем содержимое файла с токенами
        $jsonTokens = @file_get_contents($tokensFile);
        if ($jsonTokens === false) {
            throw new TokenStorageException("Не удалось прочитать файл токенов '{$tokensFile}'");
        }

        if (! flock($fh, LOCK_UN)) {
            throw new TokenStorageException("Не удалось разблокировать файл токенов '{$tokensFile}'");
        }

        if (! fclose($fh)) {
            throw new TokenStorageException("Не удалось закрыть файл токенов '{$tokensFile}'");
        }

        // Декодируем содержимое файла
        $tokens = json_decode($jsonTokens, true);
        if (is_null($tokens)) {
            $errorMessage = json_last_error_msg();
            throw new TokenStorageException(
                "Ошибка JSON-декодирования содержимого файла токенов ({$errorMessage}) '{$jsonTokens}'"
            );
        }

        return $tokens;
    }

    /**
     * Проверяет существуют ли токены для заданного домена amoCRM
     * @param string $domain Домен амoCRM
     * @return bool
     */
    public function hasTokens(string $domain) :bool
    {
        $tokensFile =  $this->getTokensFileName($domain);
        return is_file($tokensFile);
    }

    /**
     * Возвращает абсолютное имя JSON-файла с токенами
     * @param  string $domain Домен amoCRM
     * @return string
     * @throws TokenStorageException
     */
    protected function getTokensFileName(string $domain) :string
    {
        $storageFolder = __DIR__ . DIRECTORY_SEPARATOR . $this->storageFolder;
        if (! is_dir($storageFolder)) {
            if (! mkdir($storageFolder, $mode = 0755, $recursive = true)) {
                throw new TokenStorageException("Не удалось рекурсивно создать каталог файлов токенов '{$storageFolder}'");
            }
        }

        $storageFolder = realpath($storageFolder);
        $tokensFile =  $storageFolder . DIRECTORY_SEPARATOR .  $domain . '.json';

        return $tokensFile;
    }
}
