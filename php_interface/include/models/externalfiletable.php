<?php

namespace Local;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

/**
 * Class IblockRssTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> B_FILE_ID reference to {@link \Bitrix\Main\FileTable}
 * <li> MODULE_ID int mandatory
 * <li> ENTITY_ID int mandatory
 * <li> MODULE string(50) mandatory
 * <li> ENTITY string(50) mandatory
 * <li> DATE_CREATE datetime optional
 * <li> COMMENTS FOR DEBUG string optional
 * </ul>
 *
 * @package Bitrix\Iblock
 **/
class ExternalFileTable extends Entity\DataManager
{
    /**
     * Returns DB table name for entity
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'a_file';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        global $DB;

        return array(
            'ID'          => array(
                'data_type'    => 'integer',
                'primary'      => true,
                'autocomplete' => true,
            ),
            /*'B_FILE' => array(
                'data_type' => '\Bitrix\Main\FileTable',
                'reference' => array('=this.B_FILE_ID' => 'ref.ID'),
            ),*/
            'B_FILE_ID'   => array(
                'data_type' => 'integer',
            ),
            'MODULE_ID'   => array(
                'data_type' => 'integer',
            ),
            'ENTITY_ID'   => array(
                'data_type' => 'integer',
            ),
            'MODULE'      => array(
                'data_type' => 'string',
                'required'  => true,
            ),
            'ENTITY'      => array(
                'data_type' => 'string',
            ),
            'DATE_CREATE' => array(
                'data_type'  => 'datetime',
                'expression' => array(
                    $DB->datetimeToDateFunction('%s'),
                    'DATE_CREATE',
                ),
            ),
            'COMMENTS'    => array(
                'data_type' => 'string',
            ),
        );
    }

    /**
     * Returns validators for ModuleID field.
     *
     * @return array
     */
    public static function validateModuleID()
    {
        return array(
            new Entity\Validator\Length(null, 50),
        );
    }

    /**
     * Returns validators for MODULE_VALUE field.
     *
     * @return array
     */
    public static function validateModuleValue()
    {
        return array(
            new Entity\Validator\Length(null, 250),
        );
    }


}
