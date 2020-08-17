<?php
/**
 * Класc AmoAPIDebugLogger. Простой логгер запросов/ответов к API amoCRM
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.1
 *
 * v1.0.0 (16.08.2020) Первоначальная версия
 * v1.0.1 (17.08.2020) Удален use AmoCRM\AmoAPI
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoAPIDebugLogger
{
    /**
     * Лог файл для сохранения отладочной информации
     * @var string
     */
    protected $logFile;

    /**
     * Конструктор
     * @param string $logFile Лог файл для сохранения отладочной информации
     */
    public function __construct(string $logFile = 'logs/debug.log')
    {
        $this->logFile = __DIR__ . DIRECTORY_SEPARATOR . $logFile;
        $this->checkDir(dirname($this->logFile));
    }

    /**
     * Сохраняет отладочное сообщение
     * @param string $message Текст сообщения
     * @return void
     * @throws AmoAPIException
     */
    public function debug(string $message)
    {
        if (! file_put_contents($this->logFile, $message, FILE_APPEND|LOCK_EX)) {
            throw new AmoAPIException("Не удалось записать в лог файл {$this->logFile}");
        }
    }

    /**
     * Проверяет наличие каталога для сохранения файла и создает каталог при его отсутствии рекурсивно
     * @param string $directory Полный путь к каталогу
     * @return void
     */
    protected function checkDir(string $directory)
    {
        if (is_dir($directory)) {
            return;
        }

        if (! mkdir($directory, $mode = 0755, $recursive = true)) {
            throw new AmoAPIException("Не удалось рекурсивно создать каталог: {$directory}");
        }
    }
}
