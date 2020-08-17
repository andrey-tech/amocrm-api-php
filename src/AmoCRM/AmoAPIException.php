<?php
/**
 * Клсаа AmoCRMAPIException. Обрабатывает исключения в AmoAPI.
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.1
 *
 * v1.0.0 (24.05.2019) Начальный релиз
 * v1.1.0 (13.11.2019) Добавлены методы getItems, setItems, getErrors, setErrors
 * v1.1.1 (18.09.2020) Добавлен use Exception
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

use Exception;

class AmoAPIException extends Exception
{
    /**
     * Массив ошибок из $response['_embedded']['errors']
     * @var array
     */
    protected $errors = [];

    /**
     * Массив ответов из $response['_embedded']['items']
     * @var array
     */
    protected $items = [];

    /**
     * Конструктор
     * @param string $message Сообщение об исключении
     * @param int $code Код исключения
     * @param Exception|null $previous Предыдущее исключение
     */
    public function __construct(string $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct("AmoCRM API: " . $message, $code, $previous);
    }

    /**
     * Устанавливает массив сообщений об ошибках
     * @param array $errors Массив сообщений об ошибках
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Возвращает массив сообщений об ошибках
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Устанавливает массив ответов items
     * @param array $errors Массив ответов items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Возвращает массив ответов items
     */
    public function getItems()
    {
        return $this->items;
    }
}
