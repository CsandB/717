<?php

if ( ! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var CCrmEntityProgressBarComponent $component */

$bitrixTemplate = $component->getPath().'/templates/'.$this->GetName().'/'
    .$this->GetPageName().'.php';
require_once(Application::getDocumentRoot().$bitrixTemplate);


?>
