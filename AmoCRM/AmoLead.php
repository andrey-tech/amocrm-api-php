<?php
/**
 * Класс AmoLead. Содержит методы для работы со сделками.
 *
 * @author    andrey-tech
 * @copyright 2019 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз
 * v1.1.0 (15.08.2019) Добавлен метод setCatalogElements
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoLead extends AmoObject
{

    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/leads';

    /**
     * @var bool
     */
    public $is_deleted;

    /**
     * @var array
     */
    public $main_contact = [];

    /**
     * @var array
     */
    public $company = [];

    /**
     * @var int
     */
    public $closed_at;

    /**
     * @var int
     */
    public $closest_task_at;

    /**
     * @var array
     */
    public $contacts = [];

    /**
     * @var int
     */
    public $status_id;

    /**
     * @var int
     */
    public $sale;

    /**
     * @var array
     */
    public $pipeline = [];

    /**
     * @var array
     */
    public $catalog_elements = [];

    /**
     * Конструктор
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Приводит модель к формату для передачи в API
     * @return array
     */
    public function getParams() :array
    {
        $params = [];
        $properties = [ 'is_deleted', 'closed_at', 'closest_task_at', 'status_id', 'sale' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->pipeline)) {
            $params['pipeline_id'] = $this->pipeline['id'];
        }

        if (count($this->company)) {
            $params['company_id'] = $this->company['id'];
        }
        
        if (count($this->contacts)) {
            $params['contacts_id'] = $this->contacts['id'];
        }

        if (count($this->catalog_elements)) {
            $params['catalog_elements_id'] = $this->catalog_elements;
        }
        
        return array_merge(parent::getParams(), $params);
    }

    /**
     * Добавляет контакты (не более 40 контактов у 1 сделки)
     * @param array | int $contacts
     * @return AmoLead
     *
     */
    public function addContacts($contacts) :AmoLead
    {
        if (! is_array($contacts)) {
            $contacts = [ $contacts ];
        }
        
        if (isset($this->contacts['id'])) {
            foreach ($contacts as $id) {
                if (! in_array($id, $this->contacts['id'])) {
                    $this->contacts['id'][] = $id;
                }
            }
        } else {
            $this->contacts['id'] = $contacts;
        }

        return $this;
    }

    /**
     * Добавляет элементы каталога
     * @param array $catalogElements Массив каталогов с их элементами
     */
    public function setCatalogElements($catalogElements) :AmoLead
    {
        $this->catalog_elements = $catalogElements;
        
        return $this;
    }
}