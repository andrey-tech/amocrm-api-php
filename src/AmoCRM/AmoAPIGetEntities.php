<?php
/**
 * Трейт AmoAPIGetEntities. Содержит методы для получения списка сущностей.
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (08.08.2019) Добавлен метод getCatalogElementsQuantityInLead().
 * v1.1.1 (24.02.2020) Удален метод getCatalogElementsQuantityInLead() как более не поддерживаемый.
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIGetEntities
{
    /**
     *  Загружает компании
     * @return array | null
     */
    public static function getCompanies(array $params = [], $subdomain = null)
    {
        return self::request(AmoCompany::URL, 'GET', $params, $subdomain);
    }

    /**
     * Загружает сделки
     * @return array | null
     */
    public static function getLeads(array $params = [], $subdomain = null)
    {
        return self::request(AmoLead::URL, 'GET', $params, $subdomain);
    }

    /**
     * Загружает контакты
     * @return array | null
     */
    public static function getContacts(array $params = [], $subdomain = null)
    {
        return self::request(AmoContact::URL, 'GET', $params, $subdomain);
    }

    /**
     * Загружает задачи
     * @return array | null
     */
    public static function getTasks(array $params = [], $subdomain = null)
    {
        return self::request(AmoTask::URL, 'GET', $params, $subdomain);
    }

    /**
     * Загружает события
     * @return array | null
     */
    public static function getNotes(array $params = [], $subdomain = null)
    {
        return self::request(AmoNote::URL, 'GET', $params, $subdomain);
    }

    /**
     * Загружает WebHooks
     * @return array | null
     */
    public static function getWebhooks(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/webhooks', 'GET', $params, $subdomain);
    }

    /**
     * Загружает Виджеты
     * @return array | null
     */
    public static function getWidgets(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/widgets/list', 'GET', $params, $subdomain);
    }

    /**
     * Загружает неразобранные сделки
     * @return array | null
     */
    public static function getIncomingLeads(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/incoming_leads', 'GET', $params, $subdomain);
    }

    /**
     * Загружает сводную информацию о неразобранных сделках
     * @return array | null
     */
    public static function getIncomingLeadsSummary(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/incoming_leads/summary', 'GET', $params, $subdomain);
    }

    /**
     * Загружает воронки продаж
     * @return array | null
     */
    public static function getPipelines(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/pipelines', 'GET', $params, $subdomain);
    }

    /**
     * Загружает перечень каталогов аккаунта
     * @return array | null
     */
    public static function getCatalogs(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/catalogs', 'GET', $params, $subdomain);
    }

    /**
     * Загружает перечень элементов каталога
     * @return array | null
     */
    public static function getCatalogElements(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/catalog_elements', 'GET', $params, $subdomain);
    }
}
