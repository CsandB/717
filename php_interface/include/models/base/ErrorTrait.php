<?php

namespace Local\Base;

use Bitrix\Main\Error;

/**
 * Трейт для обработки ошибок
 *
 * @package Local\Base
 */
trait ErrorTrait
{
    /**
     * Ошибки
     *
     * @var array<Error[]> $errors
     */
    protected static $errors = [];

    /**
     * Очистить массив с ошибками
     */
    public static function cleanErrors()
    {
        static::$errors[static::class] = [];
    }

    /**
     * Записать ошибку выполнения
     *
     * @param string $errorText
     * @param string $errorTrace
     */
    public static function addError($errorText, $errorTrace = null)
    {
        $e = new Error($errorText, 0, ['trace' => $errorTrace]);
        static::$errors[static::class][] = $e;
    }

    /**
     * Добавить массив ошибок выполнения
     *
     * @param array $errorArr
     */
    public static function addErrors($errorArr)
    {
        static::$errors[static::class]
            = array_merge(static::$errors[static::class], $errorArr);
    }

    /**
     * Получить все ошибки класса
     *
     * @return array
     */
    public static function getAllErrors()
    {
        return static::$errors[static::class] ?? [];
    }

    /**
     * Получить все ошибки класса
     *
     * @return array
     */
    public static function getAllErrorsAsArray()
    {
        $allErrors = static::$errors[static::class] ?? [];
        $arErrors = [];
        foreach ($allErrors as $error) {
            if ($error instanceof Error) {
                $arErrors[] = $error->getMessage();
            } else {
                $arErrors[] = $error;
            }
        }

        return $arErrors;
    }

    /**
     * Проверить, были ли ошибки
     *
     * @return integer
     */
    public static function hasErrors()
    {
        $classErrors = static::$errors[static::class] ?? [];

        return count($classErrors);
    }

    /**
     * Получить текст последней ошибки
     *
     * @return string
     */
    public static function getLastError()
    {
        $classErrors = static::getAllErrors();
        $lastError = end($classErrors);
        if ($lastError instanceof Error) {
            return $lastError->getMessage();
        }

        return $lastError;
    }

    /**
     * Получить текст последней ошибки
     *
     * @return string
     */
    public static function getLastErrorTrace()
    {
        $classErrors = static::getAllErrors();
        $lastError = end($classErrors);
        $customData = $lastError->getCustomData();
        if (is_array($customData) && array_key_exists('trace', $customData)) {
            return $customData['trace'];
        }

        return null;
    }
}
