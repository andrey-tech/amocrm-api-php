<?php
/**
 * Класс AmoCatalog. Содерит методы для работы со списками (каталогами).
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (25.05.2019) Начальный релиз.
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoCatalog extends AmoObject
{

    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/catalogs';

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     */
    public $can_add_elements;

    /**
     * @var bool
     */
    public $can_show_in_cards;

    /**
     * @var bool
     */
    public $can_link_multiple;

    /**
     * @var int
     */
    public $sort;

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

        $properties = [ 'id', 'name', 'type', 'can_add_elements', 'can_show_in_cards', 'can_link_multiple', 'sort' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        return array_merge(parent::getParams(), $params);
    }
}
