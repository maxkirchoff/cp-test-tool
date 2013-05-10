<?php
namespace NightStalker;
use \PDO;

/**
 * Very thin wrapper around the PDO driver
 * Borrowed from sam keen
 */

class PdoDriver
{
    /**
    * @var PDO
    */
    protected $db_handle = null;

    /**
    * @var array
    */
    protected static $config = array();
    /**
    * @var PdoDriver
    */
    private static $instance = null;

    /**
    * Singleton static accessor
    * Usage: $db_handle = PdoDriver::instance()->handle()
    * @return PdoDriver
    */
    static function instance()
    {
        self::$instance = self::$instance ?: new self();
        return self::$instance;
    }

    function __construct()
    {
        $this->connect();
    }

    /**
     * Setter for the Db config
     *
     * @throws \InvalidArgumentException
     */
    static function init(Array $config)
    {
        $missing_keys = array_diff_key(
            array('host', 'name', 'username', 'password'),
            array_keys($config['db'])
        );
        if($missing_keys)
        {
            throw new \InvalidArgumentException("Missing required keys: ".implode(', ', $missing_keys));
        }
        self::$config = $config;
    }

    /**
    * @throws \PDOException
    */
    protected function connect()
    {
        $this->db_handle = new PDO(
            $this->create_dsn_string(self::$config),
            self::$config['db']['username'],
            self::$config['db']['password']
        );
        $this->db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
    * @return PDO
    */
    function handle()
    {
        return $this->db_handle;
    }

    /**
    * @return string
    */
    function __toString()
    {
        $to_string_config = self::$config;
        $to_string_config['password'] = '##########';
        return print_r($to_string_config, true);
    }

    /**
    * @param array $conf
    * @return string
    */
    private function create_dsn_string($conf)
    {
        // ex: mysql:dbname=tab;host=127.0.0.1
        return "mysql:dbname={$conf['db']['name']};host={$conf['db']['host']}";
    }
}
