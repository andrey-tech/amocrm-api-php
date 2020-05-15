<?php
/**
 * Класс AmoAPI. amoCRM REST API wrapper
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api
 * @license   MIT
 *
 * @version 2.1.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз
 * v1.1.0 (02.06.2019) Добавлены новые параметры, рефракторинг.
 * v1.2.0 (19.08.2019) Добавлен метод deleteObjects()
 * v1.2.1 (19.02.2020) Удален метод deleteObjects(), как неработающий
 * v2.0.0 (06.04.2020) Добавлена авторизация по протоколу OAuth 2.0.
 *                     Добавлены трейты AmoAPIAuth, AmoAPIOAuth2
 * v2.1.0 (10.05.2020) Добавлена проверка ответа сервера в метод saveObjects()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoAPI
{
    // Трейт, формирующий GET/POST запросы к amoCRM
    use AmoAPIRequest;

    // Трейт методов для получения информации по сущностям
    use AmoAPIGetInfo;

    // Трейт методов для получения сущностей
    use AmoAPIGetEntities;

    // Трейт методов для получения всех сущностей
    use AmoAPIGetAllEntities;

    // Трейт методов для авторизации по API-ключам пользователя
    use AmoAPIAuth;

    // Трейт методов для авторизации по протоколу OAuth 2.0
    use AmoAPIOAuth2;

    /**
     * Добавляет или обновляет объекты AmoObject в AmoCRM
     * @param array | AmoObject $objects
     * @param string $subdomain
     * @return array
     */
    public static function saveObjects($amoObjects, $subdomain = null) :array
    {
        if (! is_array($amoObjects)) {
            $amoObjects = [ $amoObjects ];
        }
        
        $parameters = [];
        foreach ($amoObjects as $object) {
            if (isset($object->id)) {
                $parameters[$object::URL]['update'][] = $object->getParams();
            } else {
                $parameters[$object::URL]['add'][] = $object->getParams();
            }
        }

        $responses = [];
        foreach ($parameters as $url => $params) {
            $response = AmoAPI::request($url, 'POST', $params, $subdomain);
            if (empty($response)) {
                throw new AmoAPIException(
                    "Не удалось пакетно добавить/обновить сущности (пустой ответ) по запросу {$url}: " . print_r($params, true)
                );
            }
            $responses[] = $response;
        }
        
        return $responses;
    }
}
