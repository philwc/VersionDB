<?php

namespace philwc\Classes;

/**
 * DB
 *
 * @author Philip Wright- Christie <philwc@gmail.com>
 */
class DB
{

    private $host;
    private $user;
    private $password;
    private $db;
    private $pdo;
    private $sql;
    private $params;
    private $lastError;

    /**
     * Constructor
     */
    public function __construct()
    {
        $config         = new \philwc\Classes\Config();
        $this->host     = $config->getSetting('database', 'host');
        $this->user     = $config->getSetting('database', 'user');
        $this->password = $config->getSetting('database', 'password');
        $this->db       = $config->getSetting('database', 'name');

        $this->params = array();
        $this->connect();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Disconnect
     */
    public function disconnect()
    {
        $this->pdo = null;
    }

    /**
     * Connect
     * @return PDO
     */
    private function connect()
    {
        if ($this->pdo == null) {
            try {
                $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db;

                $this->pdo = new \PDO($dsn, $this->user, $this->password);
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw new \Exception('Database Connect Error! ' . $e->getMessage());
            }
        }

        return $this->pdo;
    }

    /**
     * Set SQL
     * @param string $sql
     *
     * @return \philwc\Classes\DB
     */
    public function setSql($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Set Parameters
     * @param string $params
     *
     * @return \philwc\Classes\DB
     */
    public function setParameter($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get Last Error
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Run Prepared Statement
     *
     * @return array|boolean
     */
    public function run()
    {
        if ($this->pdo == null) {
            $this->connect();
        }

        try {
            $statement = $this->pdo->prepare($this->sql);
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
            $statement->execute($this->params);
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();

            return false;
        }

        return $statement;
    }

    /**
     * Show Query
     *
     * @return string
     */
    public function getQuery()
    {
        $keys   = array();
        $values = array();

        // build a regular expression for each parameter
        foreach ($this->params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_numeric($value)) {
                $values[] = intval($value);
            } else {
                $values[] = '"' . $value . '"';
            }
        }

        return preg_replace($keys, $values, $this->sql, 1, $count);
    }

}
