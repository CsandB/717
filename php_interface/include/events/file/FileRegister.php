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
    public static function onOnAfterFileSaveCustom(
        &$arFile,
        $strFileName = '',
        $strSavePath = '',
        $bForceMD5 = false,
        $bSkipExt = false,
        $dirAdd = ""
    ) {


        self::setEntity();
        self::setModule();


        $fields = [
            'B_FILE_ID' => (int)$arFile['ID'],
            'MODULE_ID' => self::$module_id,
            'ENTITY_ID' => self::$entity_id,
            'MODULE'    => self::$module,
            'ENTITY'    => self::$entity,
            'COMMENTS'  => htmlspecialcharsback(
                self::JSEscape(
                    SERVENTITY_DEBUGBACKTRACE_REFLECTIONCLASSVAR
                )),
        ];

        return self::add($fields);

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

    public static function JSEscape($s)
    {
        static $aSearch = array(
            "\xe2\x80\xa9",
            "\\",
            "'",
            "\"",
            "\r\n",
            "\r",
            "\n",
            "\xe2\x80\xa8",
            "*/",
            "</",
        );
        static $aReplace = array(
            " ",
            "\\\\",
            "\\'",
            '\\"',
            "\n",
            "\n",
            "\\n",
            "\\n",
            "*\\/",
            "<\\/",
        );
        $val = str_replace($aSearch, $aReplace, $s);

        return $val;
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

    /**
     * Returns $Array with instead values by key.
     *
     * @param array  $Array
     * @param string $Find
     * @param string $Replace
     *
     * @return array $Array
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function arrayReplace($Array, $Find, $Replace)
    {
        if (is_array($Array)) {
            foreach ($Array as $Key => $Val) {
                if (is_array($Array[$Key])) {
                    $Array[$Key] = ArrayReplace($Array[$Key], $Find, $Replace);
                } else {
                    if ($Key === $Find) {
                        $Array[$Key] = $Replace;
                    }
                }
            }
        }

        return $Array;
    }
}
