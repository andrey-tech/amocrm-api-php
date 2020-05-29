<?php
/**
 * Класс AmoNote. Содержит методы для работы с примечаниями.
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

class AmoNote extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/notes';

    /**
     * Типы событий
     * @var int
     */
    const LEAD_CREATED_NOTETYPE = 1;
    const CONTACT_CREATED_NOTETYPE = 2;
    const LEAD_STATUS_CHANGED_NOTETYPE = 3;
    const COMMON_NOTETYPE = 4;
    const COMPANY_CREATED_NOTETYPE = 12;
    const TASK_RESULT_NOTETYPE = 13;
    const SYSTEM_NOTETYPE = 25;
    const SMS_IN_NOTETYPE = 102;
    const SMS_OUT_NOTETYPE = 103;

    /**
     * @var bool
     */
    public $is_editable;

    /**
     * @var int
     */
    public $element_id;

    /**
     * @var int
     */
    public $element_type;

    /**
     * @var string
     */
    public $text;

    /**
     * @var int
     */
    public $note_type;

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

        $properties = [ 'is_editable', 'element_id', 'element_type', 'text', 'note_type' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if ($this->note_type == $this::SYSTEM_NOTETYPE ||
            $this->note_type == $this::SMS_IN_NOTETYPE ||
            $this->note_type == $this::SMS_OUT_NOTETYPE
        ) {
            $params['params'] = [ 'text' => $this->text ];
        }

        return array_merge(parent::getParams(), $params);
    }
}
