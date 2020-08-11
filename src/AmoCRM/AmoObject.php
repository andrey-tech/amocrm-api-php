<?php
/**
 * Класс AmoObject. Абстрактный базовый класс для работы с сущностями amoCRM.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.8.2
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.0.1 (09.08.2019) Добавлено 5 секунд к updated_at
 * v1.1.0 (19.08.2019) Добавлен метод delete()
 * v1.1.1 (13.11.2019) Добавлено исключение в метод fillById()
 * v1.2.0 (13.11.2019) Добавлен метод getCustomFieldValueById()
 * v1.2.1 (22.02.2020) Удален метод delete(), как более не поддерживаемый
 * v1.3.0 (10.05.2020) Добавлена проверка ответа сервера в метод save(). Добавлено свойство request_id
 * v1.4.0 (16.05.2020) Добавлен параметр $returnResponse в метод save()
 * v1.5.0 (19.05.2020) Добавлен параметр $subdomain в конструктор
 * v1.6.0 (21.05.2020) Добавлена поддержка параметра AmoAPI::$updatedAtDelta
 * v1.6.1 (25.05.2020) Рефракторинг
 * v1.7.0 (26.05.2020) Добавлена блокировка сущностей при обновлении (update) методом save()
 * v1.7.1 (23.07.2020) Исправлен тип параметра $returnResponse в методе save()
 * v1.8.0 (10.08.2020) Добавлены новые параметры в метод getCustomFieldValueById()
 * v1.8.1 (10.08.2020) Удалены неиспользуемые свойства. Рефракторинг
 * v1.8.2 (11.08.2020) Исправлена проверка ID в методе fillById()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

abstract class AmoObject
{
    /**
     * Путь для запроса к API (определяется в дочерних классах)
     * @var string
     */
    const URL = '';

    /**
     * Типы привязываемых элементов
     * @var int
     */
    const CONTACT_TYPE = 1;
    const LEAD_TYPE = 2;
    const COMPANY_TYPE = 3;
    const TASK_TYPE = 4;
    const CUSTOMER_TYPE = 12;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $responsible_user_id;

    /**
     * @var int
     */
    public $created_by;

    /**
     * @var int
     */
    public $updated_by;

    /**
     * @var int
     */
    public $created_at;

    /**
     * @var int
     */
    public $updated_at;

    /**
     * @var int
     */
    public $account_id;

    /**
     * @var array
     */
    public $custom_fields = [];

    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var int
     */
    public $group_id;

    /**
     * @var int
     */
    public $request_id;

    /**
     * Текущий поддомен для доступа к API
     * @var string
     */
    protected $subdomain;

    /**
     * Конструктор
     * @param array $params Параметры модели
     * @param string $subdomain Поддомен amoCRM
     */
    public function __construct(array $params = [], $subdomain = null)
    {
        $this->subdomain = $subdomain;
        $this->fill($params);
    }

    /**
     * Заполняет модель значениями из массива data
     * @param array $params Параметры модели
     * @return void
     */
    protected function fill(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Возвращает параметры модели в формате для передачи в API
     * @return array
     */
    public function getParams() :array
    {
        $params = [];
        $properties = [ 'id', 'name', 'responsible_user_id', 'created_by', 'created_at',
            'updated_by', 'account_id', 'group_id', 'request_id' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->custom_fields)) {
            $params['custom_fields'] = $this->custom_fields;
        }
        
        if (count($this->tags)) {
            $params['tags'] = array_column($this->tags, 'name');
        }

        // Если обновление сущности, то добавляем обязательный параметр 'updated_at'
        if (isset($this->id)) {
            $params['updated_at'] = time() + AmoAPI::$updatedAtDelta;
        }

        return $params;
    }

    /**
     * Заполняет модель по ID сущности
     * @param int|string $id ID сущности
     * @param array $params Дополнительные параметры запроса, передаваемые при GET-запросе к amoCRM
     * @return AmoObject
     * @throws AmoAPIException
     */
    public function fillById($id, array $params = [])
    {
        $params = array_merge([ 'id' => $id ], $params);
        $response = AmoAPI::request($this::URL, 'GET', $params, $this->subdomain);
        $items = AmoAPI::getItems($response);

        $className = get_class($this);
        if (empty($items)) {
            throw new AmoAPIException("Не найдена сущность {$className} с ID {$id}");
        }

        $item = array_shift($items);
        if ($item['id'] != $id) {
            throw new AmoAPIException("Нет сущности {$className} с ID {$id}");
        }

        $this->fill($item);

        return $this;
    }

    /**
     * Возвращает значение дополнительного поля по его ID
     * @param  int|string $id ID дополнительного поля
     * @param bool $returnFirst Вернуть только первое значение
     * @param string $returnValue Имя параметра, значение которого возвращается
     * @return mixed
     */
    public function getCustomFieldValueById($id, bool $returnFirst = true, string $returnValue = 'value')
    {
        $index = array_search($id, array_column($this->custom_fields, 'id'));
        if ($index === false) {
            return null;
        }

        $list = [];
        foreach ($this->custom_fields[$index]['values'] as $item) {
            if (is_array($item)) {
                if (isset($item[$returnValue])) {
                    $list[] = $item[$returnValue];
                }
            } else {
                $list[] = $item;
            }
        }

        if ($returnFirst) {
            return array_shift($list);
        }

        return $list;
    }

    /**
     * Возвращает массив дополнительных полей по их id
     * @param array|int $ids
     * @return array
     */
    public function getCustomFields($ids)
    {
        if (! is_array($ids)) {
            $ids = [ $ids ];
        }
        
        return array_intersect_key(
            $this->custom_fields,
            array_intersect(
                array_column($this->custom_fields, 'id'),
                $ids
            )
        );
    }

    /**
     * Устанавливает значение дополнительных полей
     * @param array $params Значения дополнительных полей
     * @return AmoObject
     */
    public function setCustomFields(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $field = [
                    'id' => $key,
                    'values' => $value
                ];
            } else {
                $field = [
                    'id' => $key,
                    'values' => [
                        [ 'value' => $value ]
                    ]
                ];
            }

            $i = array_search($key, array_column($this->custom_fields, 'id'));
            if ($i !== false) {
                $this->custom_fields[$i]['values'] = $field['values'];
            } else {
                $this->custom_fields[] = $field;
            }
        }

        return $this;
    }

    /**
     * Добавляет тэги
     * @param array | string $tags
     * @return AmoObject
     *
     */
    public function addTags($tags) :AmoObject
    {
        if (! is_array($tags)) {
            $tags = [ $tags ];
        }

        foreach ($tags as $value) {
            $tag = [
                'name' => $value
            ];

            if (! in_array($value, array_column($this->tags, 'name'))) {
                $this->tags[] = $tag;
            }
        }
    
        return $this;
    }

    /**
     * Удаляет тэги
     * @param array | string $tags
     * @return AmoObject
     *
     */
    public function delTags($tags) :AmoObject
    {
        if (! is_array($tags)) {
            $tags = [ $tags ];
        }
        $this->tags = array_diff_key($this->tags, array_intersect(array_column($this->tags, 'name'), $tags));

        return $this;
    }

    /**
     * Обновляет или добавляет объект в amoCRM
     * @param bool $returnResponse Вернуть ответ сервера вместо ID сущности
     * @return mixed
     *
     * @throws AmoAPIException
     */
    public function save(bool $returnResponse = false)
    {
        if (isset($this->id)) {
            $lock = AmoAPI::lockEntity($this);
            $params = [ 'update' => [ $this->getParams() ] ];
        } else {
            $lock = null;
            $params = [ 'add' => [ $this->getParams() ] ];
        }

        $response = AmoAPI::request($this::URL, 'POST', $params, $this->subdomain);
        AmoAPI::unlockEntity($lock);

        $items = AmoAPI::getItems($response);
        if (empty($items)) {
            $action = isset($this->id) ? 'обновить' : 'добавить';
            $className = get_class($this);
            throw new AmoAPIException(
                "Не удалось {$action} сущность {$className} (пустой ответ): " . print_r($params, true)
            );
        }

        if (! $returnResponse) {
            return $items[0]['id'];
        }

        return $response;
    }
}
