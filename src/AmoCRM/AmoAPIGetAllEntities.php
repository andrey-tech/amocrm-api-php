<?php
/**
 * Трейт AmoAPIGetAllEntities. Содержит методы для получения списка всех сущностей.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.2
 *
 * v1.0.0 (24.04.2019) Начальный релиз
 * v1.2.0 (16.05.2020) Добавлен параметр $returnResponse во все методы
 * v1.2.1 (18.05.2020) Рефракторинг
 * v1.2.2 (22.05.2020) Исправлен метод getAllCatalogElements()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIGetAllEntities
{
    /**
     * Кол-во выбираемых строк за один запрос (не более 500)
     * @var int
     */
    public static $limitRows = 500;

    /**
     * Загружает все компании
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllCompanies(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        return self::getAll(AmoCompany::URL, $params, $subdomain, $returnResponse);
    }

    /**
     * Загружает все сделки
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllLeads(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        return self::getAll(AmoLead::URL, $params, $subdomain, $returnResponse);
    }

    /**
     * Загружает все контакты
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllContacts(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        return self::getAll(AmoContact::URL, $params, $subdomain, $returnResponse);
    }

    /**
     * Загружает все задачи
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllTasks(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        return self::getAll(AmoTask::URL, $params, $subdomain, $returnResponse);
    }

    /**
     * Загружает все события
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllNotes(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        return self::getAll(AmoNote::URL, $params, $subdomain, $returnResponse);
    }

    /**
     * Загружает полный список сущностей
     * @param string $url URL для загрузки сущностей
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    protected static function getAll(
        string $url,
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        $params['limit_rows'] = self::$limitRows;
        $params['limit_offset'] = $params['limit_offset'] ?? 0;

        while (true) {
            $response = self::request($url, 'GET', $params, $subdomain, $returnResponse);
            if (is_null($response)) {
                break;
            }
            $items = self::getItems($response);

            yield $returnResponse ? $response : $items;

            if (count($items) < self::$limitRows) {
                break;
            }

            $params['limit_offset'] += self::$limitRows;
        }
    }

    /**
     * Загружает полный список элементов каталога
     * @param array $params Параметры для фильтрации
     * @param string $subdomain Поддомен amoCRM
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @return \Generator
     */
    public static function getAllCatalogElements(
        array $params = [],
        $subdomain = null,
        bool $returnResponse = false
    ) :\Generator {
        while (true) {
            $params['page'] = $params['page'] ?? 1;
            $response = self::request('/api/v2/catalog_elements', 'GET', $params, $subdomain);
            if (is_null($response)) {
                break;
            }

            $items = self::getItems($response);
            if (is_null($items)) {
                break;
            }

            yield $returnResponse ? $response : $items;

            $params['page']++;
        }
    }
}
