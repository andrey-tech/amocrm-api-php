<?php
/**
 * Класс AmoCatalogElement. Содерит методы для работы с элементами списка (каталога).
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.1
 *
 * v1.0.0 (19.08.2019) Начальный релиз.
 * v1.1.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 * v1.1.1 (25.05.2020) Добавлено свойство $is_deleted
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoCatalogElement extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/catalog_elements';

    /**
     * @var int
     */
    public $catalog_id;

    /**
     * @var bool
     */
    public $is_deleted;

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

        $properties = [ 'id', 'name', 'catalog_id', 'is_deleted' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->custom_fields)) {
            $params['custom_fields'] = $this->custom_fields;
        }

        return array_merge(parent::getParams(), $params);
    }
}
