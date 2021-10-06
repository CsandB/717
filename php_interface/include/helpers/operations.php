<?

namespace Local;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Класс для работы с операциями доступа
 *
 * Class Operations
 *
 * @package Local\HAccessControl
 */
class Operations
{

}

/*
class AccessControl\Operations
{
    public const OPERATION_READ = 'read';
    public const OPERATION_UPDATE = 'update';
    public const OPERATION_DELETE = 'delete';

    private static $operationsType
        = [
            'read'   => self::OPERATION_READ,
            'update' => self::OPERATION_UPDATE,
            'delete' => self::OPERATION_DELETE,
        ];

    public static function checkAccess($operationType, $entityName): bool
    {
        $group = UserGroupTable->row['GROUP_ID'];
        if ( ! empty($group)) {
            $result = ENTITYTable::getList(
                [
                    'select' => [
                        'GROUP_ID',
                        'OPERATION' => 'TASK.OPERATION',
                        'ENTITY'    => 'ENTITY.ENTITY',
                    ],
                    'filter' => [
                        'GROUP_ID'         => $group,
                        'ENTITY.OPERATION' => $operationType,
                        'ENTITY.ENTITY'    => (string)$entityName,
                    ],
                    'cache'  => ['ttl' => 86400, 'cache_joins' => true],
                ]
            );

            if ($result->fetch()) {
                return true;
            }
        }
        return false;
    }
}
*/
