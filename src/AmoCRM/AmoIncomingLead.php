<?php
/**
 * Класс AmoIncomingLead. Содержит методы для работы с неразобранными сделками
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (23.07.2020) Первоначальная версия
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

abstract class AmoIncomingLead extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '';

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
     * Добавляет сделку в неразобранное в amoCRM
     * @param  bool $returnResponse Вернуть ответ сервера вместо массива ID добавленных заявок
     * @return mixed
     */
    public function save(bool $returnResponse = false)
    {
        $params = [ 'add' => [ $this->getParams() ] ];
        $response = AmoAPI::request($this::URL, 'POST', $params, $this->subdomain);

        $status = $response['status'] ?? null;
        if ($status != 'success') {
            throw new AmoAPIException(
                "Не удалось добавить сделку в неразобранное: " . print_r($response, true)
            );
        }

        if (! $returnResponse) {
            return $response['data'];
        }

        return $response;
    }
}
