<?php

if ( ! defined("STOP_STATISTICS")) {
    define("STOP_STATISTICS", true);
}
if ( ! defined("NO_AGENT_STATISTIC")) {
    define("NO_AGENT_STATISTIC", "Y");
}
if ( ! defined("NO_AGENT_CHECK")) {
    define("NO_AGENT_CHECK", true);
}
if ( ! defined("NO_KEEP_STATISTIC")) {
    define("NO_KEEP_STATISTIC", true);
}

require_once($_SERVER["DOCUMENT_ROOT"]
    ."/bitrix/modules/main/include/prolog_before.php");

/*

bitrix/modules/crm/classes/general/file_proxy.php

*/
global $USER;

use Bitrix\Main\Context;
use Local\ExternalFileTable;
use Local\UserPerms($USER->GetID());
$boolChekPerms = false;

$request = Context::getCurrent()->getRequest();

$options['owner_token'] = $request->get('owner_token');
$options['fileId'] = $request->get('fileId');


$options = is_array($options) ? $options : array();

//Override owner if owner_token is specified
$ownerToken = isset($options['owner_token']) ? $options['owner_token'] : '';
if ($ownerToken !== '') {
    $ownerMap = unserialize(base64_decode($ownerToken),
        array('allowed_classes' => false));
    if (is_array($ownerMap) && isset($ownerMap[$fileID])
        && $ownerMap[$fileID] > 0
    ) {
        $ownerID = (int)$ownerMap[$fileID];
    }
}

$fileData = ExternalFileTable::getList(
    [
        'filter' => ['B_FILE_ID' => $options['fileId']]
        //         , 'select' => ['MODULE', 'ENTITY']
        ,
        'select' => ['*'],
    ]
)->fetch();
/*array (
    'ID' => '3',
    'B_FILE_ID' => '130',
    'MODULE_ID' => NULL,
    'ENTITY_ID' => '6',
    'MODULE' => 'crm',
    'ENTITY' => 'deal',
    'COMMENTS' => 'http://crdevmo/crm/deal/details/6/',
)*/

$boolChekPerms = UserPerms::CheckReadPermission($fileData);

// 'deal' => [1, 1, 1, 4],
