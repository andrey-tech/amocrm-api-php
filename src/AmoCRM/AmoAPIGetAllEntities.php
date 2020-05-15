<?php
/**
 * Трейт AmoAPIGetAllEntities. Содержит методы для получения списка всех сущностей.
 *
 * @author    andrey-tech
 * @copyright 2019 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * v1.0.0 (24.04.2019) Начальный релиз
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIGetAllEntities
{
    /**
     * Кол-во выбираемых строк за один запрос (системное ограничение 500)
     * @var int
     */
    protected static $limitRows = 500;

    /**
     * Загружает все компании
     * @return \Generator
     */
    public static function getAllCompanies(array $params = [], $subdomain = null) :\Generator
    {
        return self::getAll(AmoCompany::URL, $params, $subdomain);
    }

    /**
     * Загружает все сделки
     * @return \Generator
     */
    public static function getAllLeads(array $params = [], $subdomain = null) :\Generator
    {
        return self::getAll(AmoLead::URL, $params, $subdomain);
    }

    /**
     * Загружает все контакты
     * @return \Generator
     */
    public static function getAllContacts(array $params = [], $subdomain = null) :\Generator
    {
        return self::getAll(AmoContact::URL, $params, $subdomain);
    }

    /**
     * Загружает все задачи
     * @return \Generator
     */
    public static function getAllTasks(array $params = [], $subdomain = null) :\Generator
    {
        return self::getAll(AmoTask::URL, $params, $subdomain);
    }

    /**
     * Загружает все события
     * @return \Generator
     */
    public static function getAllNotes(array $params = [], $subdomain = null) :\Generator
    {
        return self::getAll(AmoNote::URL, $params, $subdomain);
    }

    /**
     *  Загружает полный список сделок/компаний/контактов/...
     * @return \Generator
     */
    protected static function getAll(string $url, array $params = [], $subdomain = null) :\Generator
    {
        $params['limit_rows'] = self::$limitRows;
        $limitOffset = 0;

        while (true) {
            $params['limit_offset'] = $limitOffset;

            $response = self::request($url, 'GET', $params, $subdomain);
            if (is_null($response)) {
                break;
            }

            yield $response;

            $items = $response['_embedded']['items'];
            if (count($items) < self::$limitRows) {
                break;
            }

            $limitOffset += self::$limitRows;
        }
    }


    /**
     *  Загружает полный список элементов каталога
     * @return \Generator
     */
    public static function getAllCatalogElements(array $params = [], $subdomain = null) :\Generator
    {
        $page = 0;

        while (true) {
            $requestParams = array_merge($params, [ 'PAGEN_1' => $page ]);
            $response = self::request('/api/v2/catalog_elements', 'GET', $requestParams, $subdomain);
            if (is_null($response) || !isset($response['_embedded'])) {
                break;
            }

            yield $response;

            $items = $response['_embedded']['items'];
            if (count($items) < 50) {
                break;
            }

            $page++;
        }
    }
}
