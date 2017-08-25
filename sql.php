<?php

/**
 * Class SQL
 * @Author Michael Milawski
 */


class SQL
{
    public $config = array();
    public $wokeUp = "no";
    private $DB = null;
    private $isConnected = false;

    public function getConfig ()
    {
        return $_SESSION[ 'config' ];
    }

    public function setConfig ($config)
    {
        $_SESSION[ 'config' ] = $config;
        $this->config = $config;
    }


    /**
     * Execute an SQL query
     *
     * @param $sql
     * @param array $params
     *
     * @return array|bool
     */
    public function query ($sql, $params = array())
    {
        if (!$this->isConnected) {

            $connected = $this->connect();

            if (is_array($connected)) {
                return $connected;
            }

            if (!$connected === true) {
                return $connected;
            }
        }

        try {
            //connect as appropriate as above
            $stmt = $this->DB->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $re = array(
                "status" => "OK",
                "data"   => $results,
                "count"  => $stmt->rowCount(),
            );

            //get count of rows when used limits
            if (stripos($sql, "SQL_CALC_FOUND_ROWS") !== false) {
                $cnt_stmt = $this->DB->prepare("SELECT FOUND_ROWS() as cnt");
                $cnt_stmt->execute();
                $cnt_res = $cnt_stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($cnt_res) && isset($cnt_res[ 'cnt' ])) {
                    $re[ 'FOUND_ROWS' ] = $cnt_res[ 'cnt' ];
                }
            }

            return $re;

        } catch (PDOException $ex) {
            return array(
                "status" => "ERROR",
                "error"  => $ex->getMessage()
            );

        }
    }


    /**
     * Connect to the database, config data is used from session
     * @return array|bool
     */
    private function connect ()
    {
        extract($_SESSION[ 'config' ]);

        $charset = isset($charset) ? $charset : "utf8mb4";
        if (isset($port)) {
            $host = $host . ":" . $port;
        }

        try {
            $db = new PDO("mysql:host={$host};dbname={$dbname};charset={$charset}", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $ex) {
            return array(
                "status" => "ERROR",
                "error"  => $ex->getMessage()
            );

        }

        $this->DB = $db;
        $this->isConnected = true;

        return true;
    }
}


try {
    session_start();
    $myurl = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

    $server = new SOAPServer(
        NULL,
        array(
            'uri' => $myurl
        )
    );

    $server->setPersistence(SOAP_PERSISTENCE_SESSION);
    $server->setClass('SQL');
    $server->handle();
} catch (SOAPFault $f) {
    print $f->faultstring;
}