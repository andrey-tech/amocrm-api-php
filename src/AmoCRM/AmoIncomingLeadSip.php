<?php
/**
 * Класс AmoIncomingLeadSip. Содержит методы для работы с неразобранными сделками (заявками) с типом входящий звонок (sip)
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (23.07.2020) Первоначальная версия
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoIncomingLeadSip extends AmoIncomingLead
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/incoming_leads/sip';
}
