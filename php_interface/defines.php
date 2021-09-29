<?php

// лог
use Bitrix\Main\Application;

define('LOG_FILENAME', Application::getDocumentRoot().'/log.txt');


// Подразделения
define('DEPARTMENT_DIRECTORATE', 1);            // Дирекция
define('DEPARTMENT_PURCHASING', 3);             // Отдел закупок


// Группы
define('GROUP_ID_MAKING_OFFER', 12);            // Подготовка КП
define('GROUP_ID_CLIENT_DOCUMENTS', 13);        // Документы поставщика

// Пользователи
define('CRON_ID', 39);                          // Cron
define('ASSORTMENT_AND_QUALITY_BOSS_ID', 7);    // Сидор Петрович
define('OFFICE_MANAGER_ID', 45);                // Илизар Федорович

// Группы пользователей
define('USER_GROUP_MAIL_INVITED', 5);           // Почтовые пользователи

// Бизнес-процессы
