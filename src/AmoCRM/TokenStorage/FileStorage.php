<?php

/**
 * Класс FileStorage. Реализует хранение токенов в JSON-файле
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (08.07.2020) Начальный релиз
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
     */
    public function save(array $tokens, string $domain)
    {
        // Формируем полное имя файла c токенами
        $tokensFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->storageFolder;
        $this->checkDir($tokensFilePath);
        $tokensFile =  $tokensFilePath . $domain . '.json';

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
     */
    public function load(string $domain)
    {
        // Формируем полное имя файла c токенами
        $tokensFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->storageFolder;
        $tokensFile =  $tokensFilePath . $domain . '.json';

        // Выходим, если файл с токенами не существует
        if (! is_file($tokensFile)) {
            return null;
        }

        // Загружаем содержимое файла с токенами
        $jsonTokens = @file_get_contents($tokensFile);
        if ($jsonTokens === false) {
            throw new TokenStorageException("Не удалось прочитать файл токенов '{$tokensFile}'");
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
     * Проверяет наличие каталога для сохранения файла и создает каталог при его отсутствии рекурсивно
     * @param string $directory Полный путь к каталогу
     * @return void
     */
    protected function checkDir(string $directory)
    {
        // Выходим, если каталог уже есть (is_dir кешируется PHP)
        if (is_dir($directory)) {
            return;
        }

        // Создаем новый каталог рекурсивно
        if (! mkdir($directory, $mode = 0755, $recursive = true)) {
            throw new TokenStorageException("Не удалось рекурсивно создать каталог '{$directory}'");
        }
    }
}
