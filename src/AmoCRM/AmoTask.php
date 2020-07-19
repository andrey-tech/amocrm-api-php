<?php
/**
 * Класс AmoTask. Содержит методы для работы с задачами.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.1.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoTask extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/tasks';

    /**
     * Типы задач
     * @var int
     */
    const CALL_TASKTYPE = 1;
    const MEET_TASKTYPE = 2;
    const MAIL_TASKTYPE = 3;

    /**
     * @var int
     */
    public $element_id;

    /**
     * @var int
     */
    public $element_type;

    /**
     * @var int
     */
    public $complete_till_at;

    /**
     * @var int
     */
    public $task_type;

    /**
     * @var string
     */
    public $text;

    /**
     * @var bool
     */
    public $is_completed;

    /**
     * @var array
     */
    public $result = [];

    /**
     * Конструктор
     * @param array $data Параметры модели
     * @param string $subdomain Поддомен amoCRM
     */
    public function __construct(array $data = [], $subdomain = null)
    {
        parent::__construct($data, $subdomain);
    }

    /**
     * Приводит модель к формату для передачи в API
     * @return array
     */
    public function getParams() :array
    {
        $params = [];

        $properties = [ 'element_id', 'element_type', 'complete_till_at', 'task_type', 'text', 'is_completed' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->result)) {
            $params['result'] = $this->result;
        }

        return array_merge(parent::getParams(), $params);
    }

    /**
     * Привязывает контакт к задаче
     * @param int $contactId
     * @return AmoTask
     *
     */
    public function addContact($contactId) :AmoTask
    {
        $this->element_id = $contactId;
        $this->element_type = self::CONTACT_TYPE;
        return $this;
    }

    /**
     * Привязывает сделку к задаче
     * @param int $leadId
     * @return AmoTask
     *
     */
    public function addLead($leadId) :AmoTask
    {
        $this->element_id = $leadId;
        $this->element_type = self::LEAD_TYPE;
        return $this;
    }
}
