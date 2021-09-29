<?php

namespace Local\File;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Local\ExternalFileTable;


/**
 * Class FileRegister - регистрация файла в отдельной таблице
 *
 * @package Local\File
 */
class FileRegister
{
    const SERVENTITY_DEBUGBACKTRACE_REFLECTIONCLASSVAR = '';

    /** @var  string */
    static $entity = '';
    static $module = '';
    /** @var  int */
    static $entity_id = null;
    static $module_id = null;
    /** @var array */
    static $thatentities = ['group', 'deal', 'task'];
    static $thatmodules = ['workgroups', 'company', 'crm'];

    /**
     * Событие OnFileSave
     *
     * @param        $arFile
     * @param        $strFileName
     * @param        $strSavePath
     * @param bool   $bForceMD5
     * @param bool   $bSkipExt
     * @param string $dirAdd
     *
     * @return bool
     */
    public static function onFileSaveCustom(
        &$arFile,
        $strFileName,
        $strSavePath,
        $bForceMD5 = false,
        $bSkipExt = false,
        $dirAdd = ""
    ) {

        $var = SERVENTITY_DEBUGBACKTRACE_REFLECTIONCLASSVAR;
        self::setEntity();
        self::setModule();


        return false;
    }

    /**
     *
     */
    public function setEntity(): void
    {
        $parts = explode('/',
            trim(SERVENTITY_DEBUGBACKTRACE_REFLECTIONCLASSVAR, '/'));
        foreach (self::$thatentities as $i => $item) {
            if (empty($item)) {
                continue;
            }
            $key = array_search($item, $parts);
            if ($key !== false) {
                self::$entity = $item;
                self::$entity_id = $item == 'group' ? $parts[$key + 1]
                    : $parts[$key + 2];
                break;
            }
        }
    }

    /**
     *
     */
    public function setModule(): void
    {
        $parts = explode('/',
            trim(SERVENTITY_DEBUGBACKTRACE_REFLECTIONCLASSVAR, '/'));
        foreach (self::$thatmodules as $i => $item) {
            if (empty($item)) {
                continue;
            }
            $key = array_search($item, $parts);
            if ($key !== false) {
                self::$module = $item;
                break;
            }
        }
    }

    /**
     * Событие OnFileSave
     *
     * @param        $arFile
     * @param        $strFileName
     * @param        $strSavePath
     * @param bool   $bForceMD5
     * @param bool   $bSkipExt
     * @param string $dirAdd
     *
     * @return bool
     */
    public static function onFileSave(
        &$arFile,
        $strFileName,
        $strSavePath,
        $bForceMD5 = false,
        $bSkipExt = false,
        $dirAdd = ""
    ) {

        if ($strSavePath != "services") {
            return false;
        }

        try {
            $trace = debug_backtrace();

            foreach ($trace as $traceItem) {

                if ($traceItem["function"] == "processActionHandleFile"
                ) {
                    if ($traceItem["args"][1] > 0) {
                        break;
                    } else {
                        return false;
                    }
                }

            }


        } catch (Exception $e) {

        }

        return false;
    }

    /**
     * Adds record.
     *
     * @param array $fields
     *
     * @return int|bool return entity id or false.
     * @throws Exception
     */
    public static function add(array $fields)
    {
        $result = ExternalFileTable::add($fields);
        if ($result->isSuccess()) {
            return $result->getId();
        } else {
            // $this->result->addErrors($result->getErrors());
            return false;
        }
    }

    /**
     * Returns record fields.
     *
     * @param int $fileId
     *
     * @return array $fields
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getFields($fileId)
    {
        $queryObject = ExternalFileTable::getById($fileId);

        return (($fields = $queryObject->fetch()) ? $fields : []);
    }
}
