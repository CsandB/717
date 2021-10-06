<?php

namespace Local;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ModuleManager;
use CDBResult;
use CEvent;
use CIBlockElement;
use CIBlockPropertyEnum;
use CIntranetUtils;
use CUser;
use Local\Base\Model;

//use Local\Helpers\DateHelper;

/**
 * Класс для проверки доступа и работы с пользователями
 * Class UserPerms
 *
 * @package Local\Helpers
 *
 * @property-read string $fullName Фамилия Имя пользователя
 * @property-read string $email    E-mail пользователя
 */
class UserPerms
{
    protected static $titleTemplate = '#NAME# #LAST_NAME#';

    protected static $CRON_LOGIN = 'cron';
    protected static $CRON_PASSWORD = 'LocalCron1';

    // instance : user in group , user have a role
    private static $USER_PERMISSIONS = null;

    // custom solution an as exemple
    /*
     * Формат таблицы
     * [Ответственный по этой сущности,HR специалист,user,user,...]
     *
     * */
    private static $userPermissions
        = [
            'deal' => [1, 1, 1, 4],
        ];

    public static function CheckReadPermission(
        $fileData,
        $entityType = '',
        $entityID = '',
        $userPermissions = null,
        $entityAttrs = null
    ) {
        $entityTypeName = 'DEAL';
        /*
         is_numeric($entityType)
            ? CCrmOwnerType::ResolveName($entityType)
            : mb_strtoupper(strval($entityType));
        */

        // num of entlity , e.g deal/6/
        $entityID = (int)($entityID);

        if ( ! $userPermissions && 1 == 0) {
            $userPermissions = self::GetUserPermissions();
        } else {
            $userPermissions = self::$userPermissions;
        }

        /*array (
            'ID' => '3',
            'B_FILE_ID' => '130',
            'MODULE_ID' => NULL,
            'ENTITY_ID' => '6',
            'MODULE' => 'crm',
            'ENTITY' => 'deal',
            'COMMENTS' => 'http://crdevmo/crm/deal/details/6/',
        )*/


    }

    public static function GetUserPermissions()
    {
        if (self::$USER_PERMISSIONS === null) {
            self::$USER_PERMISSIONS = array(
                'LEAD'    =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'DEAL'    =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'CONTACT' =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'COMPANY' =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'QUOTE'   =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'INVOICE' =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'ORDER'   =>
                    array(
                        'READ'   =>
                            array(
                                '-' => 'X',
                            ),
                        'EXPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'IMPORT' =>
                            array(
                                '-' => 'X',
                            ),
                        'ADD'    =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE'  =>
                            array(
                                '-' => 'X',
                            ),
                        'DELETE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'WEBFORM' =>
                    array(
                        'READ'  =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
                'BUTTON'  =>
                    array(
                        'READ'  =>
                            array(
                                '-' => 'X',
                            ),
                        'WRITE' =>
                            array(
                                '-' => 'X',
                            ),
                    ),
            );
            //[];
            //CCrmPerms::GetCurrentUserPermissions();
        }

        return self::$USER_PERMISSIONS;
    }

    /**
     * @inheritDoc
     */
    public static function getUrl()
    {
        return '/company/personal/user/#VALUE#/';
    }

    /**
     * Текущий пользователь
     *
     * @return int
     */
    public static function currentUser()
    {
        return static::currentUserId();
    }

    /**
     * Идентификатор текущего пользователя
     *
     * @return int
     */
    public static function currentUserId()
    {
        global $USER;

        return $USER->GetID();
    }

    /**
     * Идентификатор администратора
     *
     * @return int
     */
    public static function adminUserId()
    {
        return 1;
    }


    /**
     * Авторизовать пользователя
     *
     * @param string $login
     * @param string $password
     *
     * @return array|bool
     */
    public static function login($login, $password)
    {
        global $USER;

        return $USER->Login($login, $password);
    }

    /**
     * Авторизоваться как Cron
     *
     * @return array|bool
     */
    public static function loginAsCron()
    {
        global $USER;

        return $USER->Login(self::$CRON_LOGIN, self::$CRON_PASSWORD);
    }

    /**
     * Получить результат запроса списка согласно указанным параметрам
     *
     * @param array $params           Параметры запроса
     * @param array $additionalParams Дополнительные параметры
     *
     * @return bool|CDBResult
     */
    public static function getList($params = [], $additionalParams = [])
    {
        $order = array_key_exists('order', $params)
            ? array_keys($params['order']) : ['ID'];
        $sort = array_key_exists('order', $params)
            ? array_values($params['order']) : ['ASC'];
        $filter = array_key_exists('filter', $params) ? $params['filter']
            : ['ACTIVE' => 'Y'];

        $fields = [];
        $select = [];
        if (isset($params['select'])) {
            foreach ($params['select'] as $item) {
                if (substr($item, 0, 3) == 'UF_') {
                    $select[] = $item;
                } else {
                    $fields[] = $item;
                }
            }
        }
        if ( ! empty($fields)) {
            $fields = array_merge($fields, ['ID']);
        }

        if (empty($select)) {
            $select = ['UF_*'];
        }

        return CUser::GetList(
            $order,
            $sort,
            $filter,
            array_merge([
                'SELECT' => $select,
                'FIELDS' => $fields,
            ], $additionalParams)
        );
    }

    /**
     * Дата в формате Битрикса
     *
     * @param string $date Дата строкой
     *
     * @return string|null
     */
    public static function bxDate($date = 'now')
    {
        return self::formatDate($date, self::BITRIX_DATE_FORMAT);
    }

    /**
     * Получить полное имя
     *
     * @return string
     */
    public function getFullName()
    {
        return trim($this->get('NAME').' '.$this->get('LAST_NAME'));
    }

    /**
     * Получить Фамилию и инициалы
     *
     * @return string
     */
    public function getShortFIO()
    {
        return trim(
            $this->get('LAST_NAME').' '.
            substr($this->get('NAME'), 0, 1).'. '.
            substr($this->get('SECOND_NAME'), 0, 1).'. '
        );
    }

    /**
     * Получить имя головной организации
     *
     * @return bool|string
     */
    public function getHeadOrganizationName()
    {
        $departments = $this->get('UF_DEPARTMENT');
        if (count($departments) <= 0) {
            return false;
        }

        $departmentId = $departments[0];
        $department = Department::getOne($departmentId);

        return $department->getHeadOrganization()->name;
    }

    /**
     * Получить начальника пользователя
     *
     * @return int
     */
    public function getUserBossId()
    {
        self::includeModule('intranet');

        $userDepartment = CIntranetUtils::GetUserDepartments($this->id);

        $arBoss = CIntranetUtils::GetDepartmentManager(
            $userDepartment,
            $this->id,
            true
        );

//        return array_pop(array_keys($arBoss));
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
            $err = Loc::getMessage('MODULE_NOT_INSTALL',
                ['#MODULE_NAME#' => $moduleName]);

            return false;
        } catch (LoaderException $e) {

            return false;
        }
    }

    /**
     * Получить департамент, в котором состоит пользователь
     *
     * @return int
     */
    public function getDepartmentId()
    {
        self::includeModule('intranet');
        $departments = CIntranetUtils::GetUserDepartments($this->id);

        return $departments[0];
    }

    /**
     * Получить начальника департамента, в котором состоит пользователь
     *
     * @return int
     */
    public function getDepartmentBossId()
    {
        self::includeModule('intranet');

        $userDepartment = CIntranetUtils::GetUserDepartments($this->id);

        $arBoss = CIntranetUtils::GetDepartmentManager(
            $userDepartment,
            false,
            false
        );

//        return array_pop(array_keys($arBoss));
    }

    /**
     * Принадлежность группе
     *
     * @param int $groupId
     *
     * @return bool
     */
    public function inGroup($groupId)
    {
        $userGroups = static::getUserGroup();

        return in_array($groupId, $userGroups);
    }

    /**
     * Получить список групп пользователя
     *
     * @return array
     */
    public function getUserGroup()
    {
        return CUser::GetUserGroup($this->id);
    }

    /**
     * Принадлежность группе Администраторов
     *
     * @return bool
     */
    public function isAdmin()
    {
        global $USER;

        return $USER->IsAdmin;
    }

    /**
     * Добавить пользователя
     *
     * @param array $arFields Поля к сохранению
     *
     * @return bool
     */
    public function add($arFields)
    {
        $user = new CUser();
        if ($id = $user->Add($arFields)) {
            $this->id = $id;
        } else {
            self::$errors[] = $user->LAST_ERROR;
        }

        return $id;
    }

    /**
     * Обновить пользователя
     *
     * @param array $arFields Поля к сохранению
     *
     * @return bool
     */
    public function update($arFields)
    {
        $user = new CUser();
        $res = $user->Update($this->id, $arFields);
        if ( ! $res) {
            self::$errors[] = $user->LAST_ERROR;
        }

        return $res;
    }

    /**
     * Отправить письмо
     *
     * @param string $template - шаблон
     * @param array  $content  - содержимое
     *
     * @return int
     */
    public function sendEmail($template, $content)
    {
        $arEventFields = [];
        $arEventFields['EMAIL_TO'] = $this->getEmail();
        $arEventFields = array_merge($arEventFields, $content);

        $result = Event::send([
            'EVENT_NAME' => $template,
            'LID'        => 's1',
            'C_FIELDS'   => $arEventFields,
        ]);

        return $result->isSuccess();
    }

    /**
     * Получить email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->get('EMAIL');
    }

    /**
     * Отправить простое уведомление на портале
     *
     * @param string $msg - содержимое сообщения
     */
    public function sendSimpleNotice($msg)
    {
        $msg = str_replace(['<br>', '\r\n'], '[br]', $msg);

        $notification = new Notification();
        $notification->addMessage($msg);
        $notification->sendSystemNotification($this->id);
    }

    /**
     * Загрузить данные о пользователе из Битрикса
     *
     * @return bool;
     */
    protected function fill()
    {
        $bitrixUser = CUser::GetByID($this->id);
        if ($this->arFields = $bitrixUser->Fetch()) {
            return true;
        }

        return false;
    }

}
