<?php
/**
 * Класс AmoContact. Содержит методы для работы с контактами.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.3.1
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (16.05.2019) Добавлены свойства first_name, last_name
 * v1.2.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 * v1.3.0 (20.05.2020) Добавлены методы addCompany(), getPhone(), getEmail()
 * v1.3.1 (19.07.2020) Исправлен баг с типом возвращаемого значения в методе addCustomers()
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
    public function addCustomers($customers) :AmoContact
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
     * Добавляет компанию
     * @param int $companyId ID компании
     * @return AmoContact
     */
    public function addCompany($companyId) :AmoContact
    {
        $this->company = [ 'id' => $companyId ];
        return $this;
    }

    /**
     * Возвращает первый номер телефона из дополнительных полей
     * @return string|null
     */
    public function getPhone()
    {
        foreach ($this->custom_fields as $customField) {
            if (! isset($customField['code']) || $customField['code'] !== 'PHONE') {
                continue;
            }
            return $customField['values'][0]['value'];
        }

        return null;
    }

    /**
     * Возвращает первый адрес электронной почты из дополнительных полей
     * @return string|null
     */
    public function getEmail()
    {
        foreach ($this->custom_fields as $customField) {
            if (! isset($customField['code']) || $customField['code'] !== 'EMAIL') {
                continue;
            }
            return $customField['values'][0]['value'];
        }

        return null;
    }
}
