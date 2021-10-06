<?php

namespace Local;

use CDBResult;
use CIBlockResult;
use CIBlockSection;
use Local\Base\Model;

/**
 * Класс для работы с Альтернативной структурой Организации
 *
 * @package Local
 *
 * @property-read string $name Название департамента
 */
class AltDepartment extends Model
{
    const IBLOCK_ID = 54;

    protected static $necessaryModules = ['iblock'];

    /**
     * @inheritDoc
     */
    public static function getUrl()
    {
        return '/company/structure.php?set_filter_structure=Y&structure_UF_ALT_STRUCT=#VALUE#';
    }

    /**
     * Получить Руководителя подразделения
     *
     * @return integer
     */
    public function getHeadId()
    {
        return $this->get('UF_USER_ID');
    }

    /**
     * Получить ID головного подразделения
     *
     * @return int|null
     */
    public function getHeadDepartmentId()
    {
        return $this->get('IBLOCK_SECTION_ID');
    }

    /**
     * Получить список дочерних подразделений
     *
     * @return Department[]
     */
    public function getChildDepatments()
    {
        $childs = self::getAll([
            'filter' => [
                'SECTION_ID' => $this->id,
            ],
        ]);
        foreach ($childs as $child) {
            $childs = array_merge($childs, $child->getChildDepatments());
        }

        return $childs;
    }

    /**
     * Получить пользователей, состоящих в подразделении
     *
     * @param bool $withChild Включая дочерние подразделения
     *
     * @return User[]
     */
    public function getDepartmentUsers($withChild = true)
    {
        $departmentIds = [
            $this->id,
        ];
        if ($withChild) {
            $childDepatments = $this->getChildDepatments();
            foreach ($childDepatments as $depatment) {
                $departmentIds[] = $depatment->id;
            }
        }

        return User::getAll([
            'filter' => [
                'UF_ALT_STRUCT' => $departmentIds,
                'ACTIVE'        => 'Y',
            ],
        ]);
    }

    /**
     * Заполнить объект данными из Битрикса
     *
     * @return bool - удалось ли найти в Битриксе данную запись
     */
    protected function fill()
    {
        $res = self::getList([
            'filter' => ['ID' => $this->id],
        ]);
        if ($this->arFields = $res->getNext(false, false)) {
            return true;
        }

        return false;
    }

    /**
     * Получить результат запроса списка согласно указанным параметрам
     *
     * @param array $params Параметры запроса
     * @param array $additionalParams Дополнительные параметры
     *
     * @return array|bool|CDBResult|CIBlockResult
     */
    public static function getList($params = [], $additionalParams = [])
    {
        self::includeNecessaryModules();

        $filter = array_key_exists('filter', $params) ? $params['filter']
            : ['ACTIVE' => 'Y'];
        $filter = array_merge(['IBLOCK_ID' => self::IBLOCK_ID], $filter);

        $select = array_key_exists('select', $params) ? $params['select']
            : ['UF_*'];

        return CIBlockSection::GetTreeList($filter, $select);
    }
}
