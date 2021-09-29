<?php

namespace Local;

use Bitrix\Main\Page\Asset;
use CModule;
use CSite;
use CJSCore;

/**
 * Класс для работы с событиями главного модуля
 */
class MainEvent
{
    /*
     * События перед выводом буферизированного контента
     */
    function OnBeforeEndBufferContent()
    {
        global $APPLICATION;

    }

}
