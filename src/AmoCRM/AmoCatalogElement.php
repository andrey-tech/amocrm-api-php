<?php
/**
 * Класс AmoCatalogElement. Содерит методы для работы с элементами каталога.
 *
 * @author    andrey-tech
 * @copyright 2019 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 0.1.0
 *
 * v1.0.0 (19.08.2019) Начальный релиз.
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
     * Конструктор
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (isset($data['subdomain'])) {
            $this->subdomain = $data['subdomain'];
            unset($data['subdomain']);
        }
        $this->fill($data);
    }

    /**
     * Приводит модель к формату для передачи в API
     * @return array
     */
    public function getParams() :array
    {
        $params = [];

        $properties = [ 'id', 'name', 'catalog_id' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->custom_fields)) {
            $params['custom_fields'] = $this->custom_fields;
        }

        return $params;
    }
}
