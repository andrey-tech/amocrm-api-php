<?php
/**
 * Трейт AmoAPIGetInfo. Содержит методы для получения информации об аккаунте amoCRM.
 *
 * @author    andrey-tech
 * @copyright 2019 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIGetInfo
{
    /**
     * Получает информацию по аккаунту
     * @return array | null
     */
    public static function getInfo(array $params = [], $subdomain = null)
    {
        return self::request('/api/v2/account', 'GET', $params, $subdomain);
    }

    /**
     * Получает информацию по всем дополнительным полям в аккаунте
     * @return array | null
     */
    public static function getInfoCustomFields(array $params = [], $subdomain = null)
    {
        $params = array_merge([ 'with' => 'custom_fields' ], $params);
        return self::getInfo($params, $subdomain);
    }
}
