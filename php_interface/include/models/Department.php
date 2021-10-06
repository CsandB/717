<?php

namespace Local;

use CDBResult;
use CIBlockResult;
use CIBlockSection;
use Local\Base\Model;

/**
 * Класс для работы с Подразделениями Организации
 *
 * @package Local
 *
 * @property-read string $name Название департамента
 */
class Department extends Model
{
    const IBLOCK_ID = 5;

    protected static $necessaryModules = ['iblock'];

    /**
     * @inheritDoc
     */
    public static function getUrl()
    {
        return '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#VALUE#';
    }

    /**
     * Получить Руководителя подразделения
     *
     * @return integer
     */
    public function getHeadId()
    {
        return $this->get('UF_HEAD');
    }

    /**
     */
    public function myfunc()
    {
        die(__FILE__);
    }

    /**
     * Получить головную организацию
     *
     * @return self
     */
    public function getHeadOrganization()
    {
        $department = $this;
        while ($headDpartmentId = $department->getHeadDepartmentId()) {
            if ($headDpartmentId == DEPARTMENT_LOCALEXAMPLE_GROUP) {
                break;
            }
            $department = self::getOne($headDpartmentId);
        }

        return $department;
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
        $promoDepartmentIds = [
            $this->id,
        ];
        if ($withChild) {
            $allPromoDepartments = $this->getChildDepatments();
            foreach ($allPromoDepartments as $promoDepartment) {
                $promoDepartmentIds[] = $promoDepartment->id;
            }
        }

        return User::getAll(['filter' => ['UF_DEPARTMENT' => $promoDepartmentIds]]);
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
     * @param array $params           Параметры запроса
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
