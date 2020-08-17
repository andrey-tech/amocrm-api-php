<?php
/**
 * Класс AmoIncomingLead. Содержит методы для работы с неразобранными сделками (заявками)
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.2
 *
 * v1.0.0 (23.07.2020) Первоначальная версия
 * v1.1.0 (11.08.2020) Добавлены новые методы setIncomingLeadInfo(), addIncomingLead(),
 *                     addIncomingContact(), addIncomingCompany()
 * v1.1.1 (11.08.2020) Исправлены значения параметров в методах addIncomingLead(),
 *                     addIncomingContact(), addIncomingCompany()
 * v1.1.2 (17.08.2020) Исправлен баг с возвратом значения из метода addIncomingLead()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

/**
 * Class AmoIncomingLead
 * @package AmoCRM
 */
abstract class AmoIncomingLead extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/incoming_leads';

    /**
     * @var int
     */
    public $uid;

    /**
     * @var string
     */
    public $category;

        /**
     * @var string
     */
    public $source_name;

    /**
     * @var string
     */
    public $source_uid;

    /**
     * @var int
     */
    public $pipeline_id;

    /**
     * @var array
     */
    public $incoming_lead_info = [];

    /**
     * @var array
     */
    public $incoming_entities = [];

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

        $properties = [ 'uid', 'source_name', 'source_uid', 'category', 'pipeline_id' ];
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

        if (! isset($this->created_at)) {
            $this->created_at = time();
        }

        return array_merge(parent::getParams(), $params);
    }

    /**
     * Заполняет модель по UID сущности
     * @param int|string $uid UID сущности
     * @param array $params Дополнительные параметры запроса, передаваемые при GET-запросе к amoCRM
     * @return AmoObject
     * @throws AmoAPIException
     */
    public function fillByUid($uid, array $params = [])
    {
        $params = array_merge([ 'uid' => $uid ], $params);
        $response = AmoAPI::request(self::URL, 'GET', $params, $this->subdomain);
        $items = AmoAPI::getItems($response);

        $className = get_class($this);
        if (empty($items)) {
            throw new AmoAPIException("Не найдена сущность {$className} с UID {$uid}");
        }

        $item = array_shift($items);
        if ($item['uid'] != $uid) {
            throw new AmoAPIException("Нет сущности {$className} с UID {$uid}");
        }

        $this->fill($item);

        return $this;
    }

    /**
     * Устанавливает параметры неразобранного
     * @param array $params Параметры неразобранного
     * @return $this AmoIncomingLead
     */
    public function setIncomingLeadInfo(array $params)
    {
        $this->incoming_lead_info = $params;
        return $this;
    }

    /**
     * Добавляет информацию о сделке
     * @param AmoLead|array $lead Объект класса AmoLead или массив параметров сделки
     * @return $this AmoIncomingLead
     */
    public function addIncomingLead($lead)
    {
        if (is_array($lead)) {
            $this->incoming_entities['leads'][] = $lead;
        } else if (is_object($lead) && is_a($lead, '\AmoCRM\AmoLead')) {
            $this->incoming_entities['leads'][] = $lead->getParams();
        } else {
            throw new AmoAPIException("В параметрах ожидается объект класса AmoLead или массив");
        }
        return $this;
    }

    /**
     * Добавляет информацию о контакте
     * @param AmoContact|array $contact Объект класса AmoContact или массив параметров контакта
     * @return $this AmoIncomingLead
     */
    public function addIncomingContact($contact)
    {
        if (is_array($contact)) {
            $this->incoming_entities['contacts'][] = $contact;
        } else if (is_object($contact) && is_a($contact, '\AmoCRM\AmoContact')) {
            $this->incoming_entities['contacts'][] = $contact->getParams();
        } else {
            throw new AmoAPIException("В параметрах ожидается объект класса AmoContact или массив");
        }
        return $this;
    }

    /**
     * Добавляет информацию о компании
     * @param AmoCompany|array $company Объект класса AmoCompany или массив параметров компании
     * @return $this AmoIncomingLead
     */
    public function addIncomingCompany($company)
    {
        if (is_array($company)) {
            $this->incoming_entities['companies'][] = $company;
        } else if (is_object($company) && is_a($company, '\AmoCRM\AmoContact')) {
            $this->incoming_entities['companies'][] = $company->getParams();
        } else {
            throw new AmoAPIException("В параметрах ожидается объект класса AmoCompany или массив");
        }
        return $this;
    }

    /**
     * Сохраняет сделку в amoCRM
     * @param  bool $returnResponse Вернуть ответ сервера вместо массива UID добавленных заявок
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
