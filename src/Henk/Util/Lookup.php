<?php

namespace Henk\Util;

class Lookup
{
    private static $status = array(
        10009 => 'To UAT',
        10006 => 'To verify',
        10005 => 'Ready for dev',
        3 => 'In progress'
    );

    private static $priority = array(
        1 => 'Blocker',
        3 => 'Major',
        4 => 'Minor',
        6 => 'Normal',
    );

    public static function lookupStatus($statusCode)
    {
        $statusMessage = self::$status[$statusCode];
        if (!$statusMessage)
        {
            return 'NONE';
        }

        return $statusMessage;
    }

    public static function lookupPriority($priorityCode)
    {
        $priorityMessage = self::$priority[$priorityCode];
        if (!$priorityMessage)
        {
            return 'NONE';
        }

        return $priorityMessage;
    }
}
