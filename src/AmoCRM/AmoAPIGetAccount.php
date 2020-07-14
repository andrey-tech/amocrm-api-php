<?php
/**
 * Трейт AmoAPIGetAccount. Содержит методы для получения информации об аккаунте.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (18.05.2020) Параметр $params замен на $with в методе getInfo()
 * v1.2.0 (14.07.2020) Добавлен метод getAccountDomain()
 */

declare(strict_types = 1);

namespace AmoCRM;

/**
 * Trait AmoAPIGetAccount
 * @package AmoCRM
 */
trait AmoAPIGetAccount
{
    /**
     * Возвращает информацию по аккаунту
     * @param string $with Разделенный запятими список дополнительных парамтров запроса
     * @param string $subdomian Поддомен amoCRM
     * @return array|null
     */
    public static function getAccount(string $with = '', $subdomain = null)
    {
        $params = empty($with) ? [] : [ 'with' => $with ];
        return self::request('/api/v2/account', 'GET', $params, $subdomain);
    }

    /**
     * Возвращает информацию о домене аккаунта amoCRM
     * @param string $subdomian Поддомен amoCRM
     * @return array
     * @see https://www.amocrm.ru/developers/content/oauth/account-subdomain-info
     */
    public static function getAccountDomain($subdomain = null)
    {
        return self::request('/oauth2/account/subdomain', 'GET', $params = [], $subdomain);
    }
}
