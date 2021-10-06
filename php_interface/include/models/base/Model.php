<?php

namespace Local\Base;

use CDBResult;
use CIBlockResult;
use Bitrix\Main\Loader;

/**
 * Class Model
 *
 * @package Local\Base
 *
 * @property-read integer $id    Идентификатор объекта
 * @property-read string  $title Заголовок объекта
 */
abstract class Model
{
    /**
     * Идентификатор объекта
     *
     * @var int $id
     */
    protected $id;

    /**
     * Дополнительные параметры
     *
     * @var array $parameters
     */
    protected $parameters;

    /**
     * Массив данных объекта
     *
     * @var array $arFields
     */
    protected $arFields;

    /**
     * Список необходимых модулей
     *
     * @var array $necessaryModules
     */
    protected static $necessaryModules = [];

    /**
     * Поле, являющееся заголовком сущности
     *
     * @var string
     */
    protected static $titleField = 'NAME';

    /**
     * Шаблон заголовка сущности, если имеется
     * Необходимо имена полей заключать в #...#
     * прим.: #NAME# [#ID#]
     *
     * @var string
     */
    protected static $titleTemplate = '';

    use ErrorTrait;

    /**
     * Model constructor.
     *
     * @param integer $id         ID сущности
     * @param array   $parameters Дополнительные параметры
     */
    public function __construct($id, $parameters = [])
    {
        self::includeNecessaryModules();
        $this->id = (int)$id;
        $this->parameters = (array)$parameters;
        static::$errors[static::class] = [];
    }

    /**
     * Magic getter
     *
     * @param string $property
     *
     * @return mixed
     */
    function __get($property)
    {
        if ($this->$property) {
            return $this->$property;
        }
        $getterMethod = 'get'.toPascalCase($property);
        if (method_exists($this, $getterMethod)) {
            return $this->$getterMethod();
        }

        return $this->get($property);
    }

    /**
     * Magic setter
     *
     * @param string $property
     * @param mixed  $value
     */
    function __set($property, $value)
    {
        $setterMethod = 'set'.toPascalCase($property);
        if (method_exists($this, $setterMethod)) {
            $this->$setterMethod($value);
        } else {
            $this->set($property, $value);
        }
    }

    /**
     * Получить значение свойства объекта
     *
     * @param string $property
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($property, $defaultValue = null)
    {
        $property = strtoupper($property);
        if ( ! isset($this->arFields)) {
            $this->fill();
        }
        if (isset($this->arFields[$property])) {
            return $this->arFields[$property];
        }

        return $defaultValue;
    }

    /**
     * Установить значение свойства объекта
     *
     * @param string $property
     * @param mixed  $value
     */
    public function set($property, $value)
    {
        $property = strtoupper($property);
        if ( ! isset($this->arFields)) {
            $this->arFields = [];
        }
        $this->arFields[$property] = $value;
    }

    /**
     * Универсальный метод получения Заголовка объекта
     *
     * @return string
     */
    public function getTitle()
    {
        $title = '';

        if ( ! empty(static::$titleTemplate)) {
            $title = static::$titleTemplate;
            $fields = self::parseTitleTemplate($replaces);
            foreach ($fields as $key => $fieldName) {
                $title = str_replace($replaces[$key], $this->get($fieldName),
                    $title);
            }
        } elseif ( ! empty(static::$titleField)) {
            $title = $this->get(static::$titleField);
        }

        return $title;
    }

    /**
     * Получить ссылку на сущность
     *
     * @param bool $asBB Сыылка в формате BB-кода
     *
     * @return string
     */
    public function getLink($asBB = false)
    {
        $url = str_replace('#VALUE#', $this->id, static::getUrl());

        return $asBB
            ? '[url='.$url.']'.$this->title.'[/url]'
            : '<a href="'.$url.'">'.htmlspecialcharsbx($this->title).'</a>';
    }

    /**
     * Сохранить объект
     *
     * @param array $arFields Поля к сохранению
     *
     * @return bool
     */
    public function save($arFields = null)
    {
        if ( ! isset($arFields)) {
            $arFields = $this->arFields;
        }
        if ($this->id > 0) {
            return $this->update($arFields);
        } else {
            return $this->add($arFields);
        }
    }

    /**
     * Добавить объект
     * Функция-заглушка, требует реализации в наследуемом классе
     *
     * @param array $arFields Поля к сохранению
     *
     * @return bool
     */
    public function add($arFields)
    {
        $this->arFields = array_merge($this->arFields, $arFields);

        return true;
    }

    /**
     * Обновить объект
     * Функция-заглушка, требует реализации в наследуемом классе
     *
     * @param array $arFields Поля к сохранению
     *
     * @return bool
     */
    public function update($arFields)
    {
        $this->arFields = array_merge($this->arFields, $arFields);

        return true;
    }

    /**
     * Удалить объект
     * Функция-заглушка, требует реализации в наследуемом классе
     *
     * @return bool
     */
    public function delete()
    {
        $this->id = 0;
        $this->arFields = [];

        return true;
    }

    /**
     * Получить экземпляр класса по id или списку параметров
     *
     * @param integer|array $param            Параметры фильтрации
     * @param array         $additionalParams Дополнительные параметры
     *
     * @return static|null
     */
    public static function getOne($param, $additionalParams = [])
    {
        if ( ! $param) {
            return null;
        }

        self::includeNecessaryModules();

        if (is_array($param)) {
            $params = [
                'filter' => $param,
            ];
            if (array_key_exists('select', $additionalParams)) {
                $params['select'] = $additionalParams['select'];
            }

            $arRes = static::getList($params, $additionalParams)->Fetch();
            if (is_array($arRes)) {
                $id = $arRes['ID'];
                $model = new static($id, $additionalParams);
                $model->arFields = $arRes;
                if ($model->arFields) {
                    return $model;
                }
            }
        } else {
            $id = $param;
            $model = new static($id, $additionalParams);
            if ($model->fill()) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Получить массив объектов согласно указанным параметрам
     *
     * @param array $params           Параметры запроса
     * @param array $additionalParams Дополнительные параметры
     *
     * @return static[]
     */
    public static function getAll($params = [], $additionalParams = [])
    {
        $res = static::getList($params, $additionalParams);

        $list = [];
        while ($arFields = $res->getNext(false, false)) {
            $model = new static($arFields['ID'], $additionalParams);
            $model->arFields = $arFields;
            $list[$arFields['ID']] = $model;
        }

        return $list;
    }

    /**
     * Получить массив вида ключ => значение согласно указанным параметрам
     *
     * @param array $params           Параметры запроса
     * @param array $keys             Ключи
     * @param array $additionalParams Дополнительные параметры
     *
     * @return array
     */
    public static function getMap(
        $params = [],
        $keys = ['ID', 'VALUE'],
        $additionalParams = []
    ) {
        $params['select'] = array_merge((array)$params['select'], ['ID'],
            self::parseTitleTemplate());

        $res = static::getList($params, $additionalParams);
        $list = [];
        while ($arFields = $res->GetNext(false, false)) {
            $object = new static($arFields['ID']);
            $object->arFields = $arFields;
            if ( ! empty($keys)) {
                $list[] = array_combine($keys, [
                    $object->id,
                    htmlspecialcharsback($object->title),
                ]);
            } else {
                $list[$object->id] = htmlspecialcharsback($object->title);
            }
        }

        return $list;
    }

    /**
     * Подключить необходимые для работы модули
     *
     * @return boolean
     */
    protected static function includeNecessaryModules()
    {
        $result = true;
        foreach (static::$necessaryModules as $moduleName) {
            if ( ! static::includeModule($moduleName)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Подключить модуль
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public static function includeModule($moduleName)
    {
        try {
            if (Loader::includeModule($moduleName)) {
                return true;
            }

            //$err = Loc::getMessage('MODULE_NOT_INSTALL', ['#MODULE_NAME#' => $moduleName]);

            return false;
        } catch (LoaderException $e) {
            //MessageHelper::showError($e);
            return false;
        }
    }

    /**
     * Распарсить шаблон заголовка на массив полей
     *
     * @param array $replaces Области для замены в шаблоне
     *
     * @return array
     */
    private static function parseTitleTemplate(&$replaces = [])
    {
        $matches = [];
        preg_match_all('/#(\S+)#/', static::$titleTemplate, $matches);
        if (count($matches) > 1) {
            $replaces = $matches[0];

            return $matches[1];
        }

        return [];
    }

    /**
     * Получить url просмотра объектов
     * При реализации метода укажите #VALUE# как идентификатор объекта
     *
     * @return string
     */
    abstract public static function getUrl();

    /**
     * Заполнить объект данными из Битрикса
     *
     * @return bool - удалось ли найти в Битриксе данную запись
     */
    abstract protected function fill();

    /**
     * Получить результат запроса списка согласно указанным параметрам
     *
     * @param array $params           Параметры запроса
     * @param array $additionalParams Дополнительные параметры
     *
     * @return array|bool|integer|CDBResult|CIBlockResult
     */
    abstract public static function getList(
        $params = [],
        $additionalParams = []
    );
}
