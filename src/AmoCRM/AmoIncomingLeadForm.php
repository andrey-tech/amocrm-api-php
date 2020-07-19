<?php
/**
 * Класс AmoIncomingLeadForm. Содержит методы для работы с неразорбанными заявками из веб-форм.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.1
 *
 * v1.0.0 (07.07.2020) Первоначальная версия
 * v1.0.1 (19.07.2020) Исправлен баг с namespace
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoIncomingLeadForm extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/incoming_leads/form';

    /**
     * @var array
     */
    public $incoming_lead_info = [];

    /**
     * @var array
     */
    public $incoming_entities = [];

    /**
     * @var string
     */
    public $source_name;

    /**
     * @var string
     */
    public $source_uid;

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

        $properties = [ 'source_name', 'source_uid' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->incoming_lead_info)) {
            $params['incoming_lead_info'] = $this->incoming_lead_info;
        }

        if (count($this->incoming_entities)) {
            $params['incoming_entities'] = $this->incoming_entities;
        }

        return array_merge(parent::getParams(), $params);
    }

    /**
     * Добавляет объект в amoCRM
     * @return array
     */
    public function save($returnResponse = true)
    {
        $params = [ 'add' => [ $this->getParams() ] ];
        return AmoAPI::request($this::URL, 'POST', $params, $this->subdomain);
    }
}
