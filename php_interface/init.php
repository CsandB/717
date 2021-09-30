<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/* Composer */
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');
}

/* Регистрируем обработчики событий */
$eventManager = EventManager::getInstance();

$eventManager->addEventHandler('main',
    'OnBeforeEndBufferContent', [
        'Local\MainEvent',
        'OnBeforeEndBufferContent',
    ]);

$eventManager->addEventHandler('main',
    'OnAfterFileSave', [
        'Local\File\FileRegister',
        'onOnAfterFileSaveCustom',
    ]);


//file
if (file_exists($_SERVER["DOCUMENT_ROOT"]
    .'/local/php_interface/include/events/file/handler.php')
) {
    require $_SERVER["DOCUMENT_ROOT"]
        .'/local/php_interface/include/events/file/handler.php';
}

