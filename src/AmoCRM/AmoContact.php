<?php
/**
 * Класс AmoContact. Содержит методы для работы с контактами.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 1.2.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (16.05.2019) Добавлены свойства first_name, last_name
 * v1.2.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoContact extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/contacts';

    /**
     * @var array
     */
    public $company = [];

    /**
     * @var array
     */
    public $leads = [];

    /**
     * @var array
     */
    public $customers = [];

    /**
     * @var int
     */
    public $closest_task_at;

    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

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

        if (isset($this->closest_task_at)) {
            $params['closest_task_at'] = $this->closest_task_at;
        }
        
        if (count($this->company)) {
            $params['company_id'] = $this->company['id'];
        }
        
        if (count($this->leads)) {
            $params['leads_id'] = $this->leads['id'];
        }
        
        if (count($this->customers)) {
            $params['customers_id'] = $this->customers['id'];
        }
        
        return array_merge(parent::getParams(), $params);
    }

    /**
     * Добавляет сделки (не более 40 сделок у 1 контакта)
     * @param array | int $leads
     * @return AmoContact
     *
     */
    public function addLeads($leads) :AmoContact
    {
        if (! is_array($leads)) {
            $leads = [ $leads ];
        }
        
        if (isset($this->leads['id'])) {
            foreach ($leads as $id) {
                if (! in_array($id, $this->leads['id'])) {
                    $this->leads['id'][] = $id;
                }
            }
        } else {
            $this->leads['id'] = $leads;
        }

        return $this;
    }

    /**
     * Добавляет покупателей
     * @param array | int $customers
     * @return AmoCompany
     *
     */
    public function addCustomers($customers) :AmoCompany
    {
        if (! is_array($customers)) {
            $customers = [ $customers ];
        }
        
        if (isset($this->customers['id'])) {
            foreach ($customers as $id) {
                if (! in_array($id, $this->customers['id'])) {
                    $this->customers['id'][] = $id;
                }
            }
        } else {
            $this->customers['id'] = $customers;
        }

        return $this;
    }

    /**
     * Возвращает первый номер телефона из дополнительных полей контакта
     * @return string | null
     */
    public function getPhoneNumber()
    {
        $phoneCustomFields = array_filter(
            $this->custom_fields,
            function ($item) {
                return (isset($item['code']) && ($item['code'] === 'PHONE'));
            }
        );

        if (count($phoneCustomFields)) {
            $field = reset($phoneCustomFields);
            $result = $field['values'][0]['value'];
        } else {
            $result = null;
        }

        return $result;
    }
}
