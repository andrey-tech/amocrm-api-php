<?php
/**
 * Трейт AmoAPIGetEntities. Содержит методы для получения списка сущностей.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.2
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (08.08.2019) Добавлен метод getCatalogElementsQuantityInLead().
 * v1.1.1 (24.02.2020) Удален метод getCatalogElementsQuantityInLead() как более не поддерживаемый.
 * v1.2.0 (16.05.2020) Добавлен параметр $returnResponse во все методы
 * v1.2.1 (14.07.2020) Изменен порядок параметров $subdomain и $returnResponse в методах
 * v1.2.2 (11.08.2020) Исправлен метод getIncomingLeadsSummary()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIGetEntities
{
    /**
     * Загружает компании
     * @return array | null
     */
    public static function getCompanies(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoCompany::URL, 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает сделки
     * @return array | null
     */
    public static function getLeads(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoLead::URL, 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает контакты
     * @return array | null
     */
    public static function getContacts(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoContact::URL, 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает задачи
     * @return array | null
     */
    public static function getTasks(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoTask::URL, 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает события
     * @return array | null
     */
    public static function getNotes(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoNote::URL, 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает WebHooks
     * @return array | null
     */
    public static function getWebhooks(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/webhooks', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает Виджеты
     * @return array | null
     */
    public static function getWidgets(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/widgets/list', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает неразобранные сделки
     * @return array | null
     */
    public static function getIncomingLeads(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/incoming_leads', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает сводную информацию о неразобранных сделках
     * @return array | null
     */
    public static function getIncomingLeadsSummary(array $params = [], bool $returnResponse = true, $subdomain = null)
    {
        return self::request('/api/v2/incoming_leads/summary', 'GET', $params, $subdomain);
    }

    /**
     * Загружает воронки продаж
     * @return array | null
     */
    public static function getPipelines(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/pipelines', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает перечень каталогов аккаунта
     * @return array | null
     */
    public static function getCatalogs(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/catalogs', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает перечень элементов каталога
     * @return array | null
     */
    public static function getCatalogElements(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/catalog_elements', 'GET', $params, $subdomain);
        if (! $returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }
}
