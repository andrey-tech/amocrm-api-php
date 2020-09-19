<?php

/**
 * Интерфейс TokenStorageInterface. Определяет методы для сохранения и загрузки токенов oAuth 2.0
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (08.07.2020) Начальный релиз
 * v1.1.0 (19.09.2020) Добавлен метод hasTokens()
 *
 */

declare(strict_types = 1);

namespace AmoCRM\TokenStorage;

interface TokenStorageInterface
{
    /**
     * Загружает токены
     * @param  string $domain Полный домен amoCRM
     * @return array|null
     */
    public function load(string $domain);

    /**
     * Сохраняет токены
     * @param  array $tokens Токены для сохранения
     * @param  string $domain Полный домен amoCRM
     * @return void
     */
    public function save(array $tokens, string $domain);

    /**
     * Проверяет существуют ли токены
     * @param  string  $domain Полный домен amoCRM
     * @return boolean
     */
    public function hasTokens(string $domain) :bool;
}
