<?php
use CodeIgniter\CodeIgniter;

if (!function_exists('disableForeignKeyCheck')) {
    /**
     * Disable the foreign key check.
     *
     * @return boolean
     */
    function disableForeignKeyCheck(): bool
    {
        $db = \Config\Database::connect();
        $dbDriver = $db->DBDriver;
        if ($dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        }

        if ($dbDriver === 'SQLite3') {
            $db->query('PRAGMA foreign_keys = OFF');
        }
    
        return true;
    }
}

if (!function_exists('enableForeignKeyCheck')) {
    /**
     * Enable the foreign key check.
     *
     * @return boolean
     */
    function enableForeignKeyCheck(): bool
    {
        $db = \Config\Database::connect();
        $dbDriver = $db->DBDriver;
        if ($dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        }

        if ($dbDriver === 'SQLite3') {
            $db->query('PRAGMA foreign_keys = ON');
        }
    
        return true;
    }
}