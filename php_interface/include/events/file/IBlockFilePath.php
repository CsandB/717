<?php

namespace Local\File;

use Bitrix\Main\Loader;
use Bitrix\Main\FileTable;
use Bitrix\Main\Application;
use Bitrix\Iblock;
use CBXVirtualIo;
use CDiskQuota;
use CFile;
use COption;
use Exception;

/**
 * Class IBlockFilePath
 *
 * @package Local
 */
class IBlockFilePath
{
    /**
     * @var null|self
     */
    protected static $instance = null;
    /**
     * @var array $paths
     */
    protected $paths = [];

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->loadPaths();
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if ( ! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Загружает из внешнено источника (в данном случае просто массив) список
     * путей для каждого инфоблока Ключи - ID инфоблока, значения - subdir для
     * сохранения файлов
     */
    public function loadPaths()
    {
        $this->paths = [
            8 => "iblock/documents/questionnaire",
            5 => "iblock/documents/nda",
            7 => "iblock/documents/passport",
            4 => "iblock/documents/contract",
            3 => "iblock/documents/employmentHistory",
        ];
    }

    /**
     * Получить список путей для инфоблока
     *
     * @return array
     */
    public function getPaths()
    {
        if ( ! is_array($this->paths)) {
            $this->paths = [];
        }

        return $this->paths;
    }


    /**
     * Короткая функция сохранения
     *
     * Позволяет не дублировать код из \CFile
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
    public function saveFileHack(
        &$arFile,
        $strFileName,
        $strSavePath,
        $bForceMD5 = false,
        $bSkipExt = false,
        $dirAdd = ""
    ) {
        try {
            $fileId = CFile::SaveFile($arFile, $strSavePath, $bForceMD5,
                $bSkipExt, $dirAdd);
            if ($fileId > 0) {
                $fileFields = CFile::GetByID($fileId)->Fetch();
                $arFile = array_merge($arFile, [
                    "SUBDIR"    => $fileFields["SUBDIR"],
                    "FILE_NAME" => $fileFields["FILE_NAME"],
                    "WIDTH"     => $fileFields["WIDTH"],
                    "HEIGHT"    => $fileFields["HEIGHT"],
                ]);

                $deleteSize = $fileFields["FILE_SIZE"];

                $fileFields["ID"] = intval($fileFields["ID"]);
                if ($fileFields["ID"] > 0) {
                    $fileTableName = FileTable::getTableName();
                    $connection
                        = Application::getConnection(FileTable::getConnectionName());
                    $connection->query("DELETE FROM ".$fileTableName
                        ." WHERE ID=".$fileFields["ID"]);

                    CFile::CleanCache($fileFields["ID"]);

                    if ($deleteSize > 0
                        && COption::GetOptionInt("main", "disk_space") > 0
                    ) {
                        CDiskQuota::updateDiskQuota("file", $deleteSize,
                            "delete");
                    }
                }

                return true;
            }
        } catch (Exception $e) {

        }

        return false;
    }

    public function saveFile(
        &$arFile,
        $strFileName,
        $strSavePath,
        $bForceMD5 = false,
        $bSkipExt = false,
        $dirAdd = ""
    ) {
        $uploadDir = COption::GetOptionString("main", "upload_dir", "upload");
        $io = CBXVirtualIo::GetInstance();
        if ($bForceMD5 != true
            && COption::GetOptionString("main", "save_original_file_name", "N")
            == "Y"
        ) {
            $dirAddEx = $dirAdd;
            if ($dirAddEx == "") {
                $i = 0;
                while (true) {
                    $dirAddEx = substr(md5(uniqid("", true)), 0, 3);
                    if ( ! $io->FileExists($_SERVER["DOCUMENT_ROOT"]."/"
                        .$uploadDir."/".$strSavePath."/".$dirAddEx."/"
                        .$strFileName)
                    ) {
                        break;
                    }
                    if ($i >= 25) {
                        $j = 0;
                        while (true) {
                            $dirAddEx = substr(md5(mt_rand()), 0, 3)."/"
                                .substr(md5(mt_rand()), 0, 3);
                            if ( ! $io->FileExists($_SERVER["DOCUMENT_ROOT"]."/"
                                .$uploadDir."/".$strSavePath."/".$dirAddEx."/"
                                .$strFileName)
                            ) {
                                break;
                            }
                            if ($j >= 25) {
                                $dirAddEx = substr(md5(mt_rand()), 0, 3)."/"
                                    .md5(mt_rand());
                                break;
                            }
                            $j++;
                        }
                        break;
                    }
                    $i++;
                }
            }
            if (substr($strSavePath, -1, 1) <> "/") {
                $strSavePath .= "/".$dirAddEx;
            } else {
                $strSavePath .= $dirAddEx."/";
            }
        } else {
            $strFileExt = ($bSkipExt == true
            || ($ext
                = GetFileExtension($strFileName)) == "" ? "" : ".".$ext);
            while (true) {
                if (substr($strSavePath, -1, 1) <> "/") {
                    $strSavePath .= "/".substr($strFileName, 0, 3);
                } else {
                    $strSavePath .= substr($strFileName, 0, 3)."/";
                }

                if ( ! $io->FileExists($_SERVER["DOCUMENT_ROOT"]."/".$uploadDir
                    ."/".$strSavePath."/".$strFileName)
                ) {
                    break;
                }

                //try the new name
                $strFileName = md5(uniqid("", true)).$strFileExt;
            }
        }

        $arFile["SUBDIR"] = $strSavePath;
        $arFile["FILE_NAME"] = $strFileName;
        $strDirName = $_SERVER["DOCUMENT_ROOT"]."/".$uploadDir."/".$strSavePath
            ."/";
        $strDbFileNameX = $strDirName.$strFileName;
        $strPhysicalFileNameX = $io->GetPhysicalName($strDbFileNameX);

        CheckDirPath($strDirName);

        if (is_set($arFile, "content")) {
            $f = fopen($strPhysicalFileNameX, "w");
            if ( ! $f) {
                return false;
            }
            if (fwrite($f, $arFile["content"]) === false) {
                return false;
            }
            fclose($f);
        } elseif ( ! copy($arFile["tmp_name"], $strPhysicalFileNameX)
            && ! move_uploaded_file($arFile["tmp_name"],
                $strPhysicalFileNameX)
        ) {
            CFile::DoDelete($arFile["old_file"]);

            return false;
        }

        if (isset($arFile["old_file"])) {
            CFile::DoDelete($arFile["old_file"]);
        }

        @chmod($strPhysicalFileNameX, BX_FILE_PERMISSIONS);

        //flash is not an image
        $flashEnabled = ! CFile::IsImage($arFile["ORIGINAL_NAME"],
            $arFile["type"]);

        $imgArray = CFile::GetImageSize($strDbFileNameX, false, $flashEnabled);

        if (is_array($imgArray)) {
            $arFile["WIDTH"] = $imgArray[0];
            $arFile["HEIGHT"] = $imgArray[1];

            if ($imgArray[2] == IMAGETYPE_JPEG) {
                $exifData = CFile::ExtractImageExif($strPhysicalFileNameX);
                if ($exifData && isset($exifData["Orientation"])) {
                    //swap width and height
                    if ($exifData["Orientation"] >= 5
                        && $exifData["Orientation"] <= 8
                    ) {
                        $arFile["WIDTH"] = $imgArray[1];
                        $arFile["HEIGHT"] = $imgArray[0];
                    }

                    $properlyOriented
                        = CFile::ImageHandleOrientation($exifData["Orientation"],
                        $io->GetPhysicalName($strDbFileNameX));
                    if ($properlyOriented) {
                        $jpgQuality = intval(COption::GetOptionString("main",
                            "image_resize_quality", "95"));
                        if ($jpgQuality <= 0 || $jpgQuality > 100) {
                            $jpgQuality = 95;
                        }

                        imagejpeg($properlyOriented, $strPhysicalFileNameX,
                            $jpgQuality);
                        clearstatcache(true, $strPhysicalFileNameX);
                    }

                    $arFile["size"] = filesize($strPhysicalFileNameX);
                }
            }
        } else {
            $arFile["WIDTH"] = 0;
            $arFile["HEIGHT"] = 0;
        }

        return true;
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
        if ($strSavePath != "iblock") {
            return false;
        }

        try {
            $trace = debug_backtrace();

            $iblockId = 0;
            foreach ($trace as $traceItem) {
                if ($traceItem["function"] == "SetPropertyValues"
                    && $traceItem["class"] == "CIBlockElement"
                ) {
                    if ($traceItem["args"][1] > 0) {
                        $iblockId = intval($traceItem["args"][1]);
                        break;
                    } else {
                        return false;
                    }
                }

                if ($traceItem["function"] == "SetPropertyValuesEx"
                    && $traceItem["class"] == "CAllIBlockElement"
                ) {
                    $elementId = intval($traceItem["args"][0]);
                    if ($elementId <= 0) {
                        return false;
                    }

                    $iblockId = intval($traceItem["args"][1]);

                    if ($iblockId <= 0 && Loader::includeModule("iblock")) {
                        $row = Iblock\ElementTable::getRow([
                            "filter" => [
                                "=ID" => $elementId,
                            ],
                            "select" => [
                                "IBLOCK_ID",
                            ],
                        ]);
                        if ($row && $row["IBLOCK_ID"]) {
                            $iblockId = intval($row["IBLOCK_ID"]);
                        }
                    }


                    if ($iblockId > 0) {
                        break;
                    } else {
                        return false;
                    }
                }

                if ($traceItem["function"] == "SaveForDB"
                    && $traceItem["class"] == "CAllFile"
                ) {
                    if ($traceItem["args"][0]["IBLOCK_ID"] > 0) {
                        $iblockId = intval($traceItem["args"][0]["IBLOCK_ID"]);
                        break;
                    }
                }
            }

            if ($iblockId > 0) {
                $instance = self::getInstance();

                $paths = $instance->getPaths();
                if ( ! array_key_exists($iblockId, $paths)) {
                    return false;
                }

                $strNewSavePath = $paths[$iblockId];
                if ($strNewSavePath == "iblock") {
                    return false;
                }

                //Если бы $strSavePath передавался в обработчик по ссылке, то достаточно было бы его просто изменить
                return $instance->saveFile($arFile, $strFileName,
                    $strNewSavePath, $bForceMD5, $bSkipExt, $dirAdd);
            }
        } catch (Exception $e) {

        }

        return false;
    }
}
