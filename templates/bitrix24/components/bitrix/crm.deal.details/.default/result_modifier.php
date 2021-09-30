<?php

use Local\File\FileRegister;

$arResult['ENTITY_FIELDS'] = FileRegister::arrayReplace(
    $arResult['ENTITY_FIELDS'],
    'URL_TEMPLATE',
    '/local/getFile/?fileId=#file_id#'
);
