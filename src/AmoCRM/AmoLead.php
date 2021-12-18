<?php
/**
 * Класс AmoLead. Содержит методы для работы со сделками.
 *
 * @author    andrey-tech
 * @copyright 2020-2021 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.4.1
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.1.0 (15.08.2019) Добавлен метод setCatalogElements
 * v1.2.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 * v1.3.0 (20.05.2020) Добавлен метод addCompany()
 * v1.4.0 (12.12.2021) Добавлены методы removeContacts() и removeCompany()
 * v1.4.1 (19.12.2021) Улучшены методы removeContacts() и removeCompany()
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
     * @var array
     */
    public $unlink = [];

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
    public function getParams(): array
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

        if (count($this->unlink)) {
            $params['unlink'] = $this->unlink;
        }

        return array_merge(parent::getParams(), $params);
    }

    /**
     * Добавляет контакты (не более 40 контактов у 1 сделки)
     * @param array|int $contacts ID контакта или массив ID контактов
     * @return AmoLead
     *
     */
    public function addContacts($contacts): AmoLead
    {
        if (! is_array($contacts)) {
            $contacts = [ $contacts ];
        }

        if (! isset($this->contacts['id'])) {
            $this->contacts['id'] = [];
        }

        $this->contacts['id'] = array_values(
            array_unique(
                array_merge($this->contacts['id'], $contacts)
            )
        );

        if (! $this->contacts['id']) {
            $this->contacts = [];
        }

        if (isset($this->unlink['contacts_id'])) {
            $this->unlink['contacts_id'] = array_values(
                array_diff($this->unlink['contacts_id'], $contacts)
            );

            if (! $this->unlink['contacts_id']) {
                unset($this->unlink['contacts_id']);
            }
        }

        return $this;
    }

    /**
     * Устанавливает элементы каталога
     * @param array $catalogElements Массив каталогов с их элементами
     * @return AmoLead
     */
    public function setCatalogElements(array $catalogElements): AmoLead
    {
        $this->catalog_elements = $catalogElements;
        
        return $this;
    }

    /**
     * Добавляет компанию
     * @param int $companyId ID компании
     * @return AmoLead
     */
    public function addCompany(int $companyId) :AmoLead
    {
        $this->company = [ 'id' => $companyId ];

        if (isset($this->unlink['company_id']) && $this->unlink['company_id'] === $companyId) {
            unset($this->unlink['company_id']);
        }

        return $this;
    }

    /**
     * Отвязывает контакты по ID контактов
     * @param array|int $contacts ID контакта или массив ID контактов
     * @return AmoLead
     */
    public function removeContacts($contacts): AmoLead
    {
        if (! is_array($contacts)) {
            $contacts = [ $contacts ];
        }

        if (! isset($this->unlink['contacts_id'])) {
            $this->unlink['contacts_id'] = [];
        }

        $this->unlink['contacts_id'] = array_values(
            array_unique(
                array_merge($this->unlink['contacts_id'], $contacts)
            )
        );

        if (! $this->unlink['contacts_id']) {
            unset($this->unlink['contacts_id']);
        }

        if (isset($this->contacts['id'])) {
            $this->contacts['id'] = array_values(
                array_diff($this->contacts['id'], $contacts)
            );

            if (! $this->contacts['id']) {
                $this->contacts = [];
            }
        }

        return $this;
    }

    /**
     * Отвязывает компанию по ID компании
     * @param int $companyId ID компании
     * @return AmoLead
     */
    public function removeCompany(int $companyId): AmoLead
    {
        $this->unlink['company_id'] = $companyId;

        if (isset($this->company['id']) && $this->company['id'] === $companyId) {
            $this->company = [];
        }

        return $this;
    }
}
