<?php
/**
 * Класс TokenStorageException. Обрабатывает исключения в классах пространства имен \AmoCRM\TokenStorage
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

class TokenStorageException extends \Exception
{
    /**
     * Конструктор
     * @param string $message Сообщение об исключении
     * @param int $code Код исключения
     * @param \Exception|null $previous Предыдущее исключение
     */
    public function __construct(string $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct("AmoCRM TokenStorage: " . $message, $code, $previous);
    }
}
