<?php
/**
 * Трейт AmoAPIIncomingLead. Содержит методы для принятия или отклонения неразобранных сделок (заявок)
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (11.08.2020) Начальный релиз
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIIncomingLeads
{
    /**
     * Принимает неразобранные сделки (заявки)
     * @param array $params Параметры заявок
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @param null $subdomain Поддомен amoCRM
     * @return array
     */
    public static function acceptIncomingLeads(array $params, bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/incoming_leads/accept', 'POST', $params, $subdomain);

        if (! $returnResponse) {
            return $response['data'];
        }

        return $response;
    }

    /**
     * Отклоняет неразобранные сделки (заявки)
     * @param array $params Параметры заявок
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @param null $subdomain Поддомен amoCRM
     * @return array
     */
    public static function declineIncomingLeads(array $params, bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v2/incoming_leads/decline', 'POST', $params, $subdomain);

        if (! $returnResponse) {
            return $response['data'];
        }

        return $response;
    }

    /**
     * Сохраняет (добавляет) объекты классов-моделей сделок из неразобранного с ограничением на число сущностей в одном запросе к API amoCRM
     * @param array|object $amoObjects Массив объектов AmoIncomingLead или объект AmoIncomingLead
     * @param bool $returnResponses Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей
     * @param string $subdomain Поддомен amoCRM
     * @param int $limit Максимальное число сущностей в одном запросе к API amoCRM
     * @return array
     * @throws AmoAPIException
     */
    public static function saveIncomingObjectsWithLimit(
        $amoObjects,
        bool $returnResponses = false,
        $subdomain = null,
        $limit = 250
    ):array {
        if (! is_array($amoObjects)) {
            $amoObjects = [$amoObjects];
        }

        if (count($amoObjects) < $limit) {
            return self::saveIncomingObjects($amoObjects, $returnResponses, $subdomain);
        }

        $responses = [];
        $amoObjectsChunks = array_chunk($amoObjects, $limit);
        foreach ($amoObjectsChunks as $amoObjectsChunk) {
            $responses = array_merge($responses, self::saveIncomingObjects($amoObjectsChunk, $returnResponses, $subdomain));
        }

        return $responses;
    }

    /**
     * Сохраняет (добавляет) объекты классов-моделей сделок из неразобранного
     * @param array|object $amoObjects Массив объектов AmoIncomingLead или объект AmoIncomingLead
     * @param bool $returnResponses Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей
     * @param string $subdomain Поддомен amoCRM
     * @return array
     * @throws AmoAPIException
     */
    public static function saveIncomingObjects($amoObjects, bool $returnResponses = false, $subdomain = null) :array
    {
        if (! is_array($amoObjects)) {
            $amoObjects = [ $amoObjects ];
        }

        $parameters = [];
        foreach ($amoObjects as $object) {
            $parameters[$object::URL]['add'][] = $object->getParams();
        }

        $responses = [];
        foreach ($parameters as $url => $params) {
            $response = AmoAPI::request($url, 'POST', $params, $subdomain);
            if (empty($response)) {
                throw new AmoAPIException(
                    "Не удалось пакетно добавить сущности (пустой ответ) по запросу {$url}: " . print_r($params, true)
                );
            }
            $responses[] = $response;
        }

        if (! $returnResponses) {
            $items = [];
            foreach ($responses as $response) {
                $items = array_merge($items, $response['data']);
            }
            return $items;
        }

        return $responses;
    }
}
