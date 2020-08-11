<?php
/**
 * Класс AmoIncomingLeadForm. Содержит методы для работы с неразобранными сделками (заявками), созданными из веб-форм
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.1
 *
 * v1.0.0 (07.07.2020) Первоначальная версия
 * v1.0.1 (19.07.2020) Исправлен баг с namespace
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoIncomingLeadForm extends AmoIncomingLead
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v2/incoming_leads/form';
}
