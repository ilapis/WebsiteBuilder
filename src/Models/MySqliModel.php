<?php declare(strict_types = 1);

namespace WB\Models;

class MySqliModel
{
    private $_connection;
    private static $_instance; //The single instance

    public static function getInstance()
    {
        if (!self::$_instance) { // If no instance then make one
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Constructor
    public function __construct()
    {
        if (null === DB_DATABASE) {
            $this->_connection = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
        } else {
            $this->_connection = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        }
    
        // Error handling
        if (\mysqli_connect_error()) {
            trigger_error("Failed to conencto to MySQL: " . \mysqli_connect_error(), E_USER_ERROR);
        } else {
            $this->_connection->set_charset('utf8');
        }
    }

    public function select_db(string $dbname) {
        return $this->_connection->select_db($dbname);
    }
    
    // Get mysqli connection
    public function getConnection()
    {
        return $this->_connection;
    }
    
    // Magic method clone is empty to prevent duplication of connection
    private function __clone()
    {
    }
}
