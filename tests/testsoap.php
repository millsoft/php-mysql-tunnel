<?php

/**
 * Simple Test for the SOAP client
 */


/**
 * @param $tunnel_url
 * @param $config
 *
 * @return \SoapClient
 */
function getDbTunnel($tunnel_url, $config){

    try {
        $db = new SoapClient(null, array(
            'location' => $tunnel_url,
            'uri' => $tunnel_url,
            'trace' => 1,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
        ));

        $db->setConfig($config);

    } catch (SoapFault $fault) {
        trigger_error("SOAP-Error: (Error No: {$fault->faultcode}, "
                      . "Error: {$fault->faultstring})", E_USER_ERROR);
    }
    return $db;
}

$tunnel_url = "http://localhost/sqltunnel/sql.php";

$config = array(
    "host" => "YourHost",
    "dbname" => "db",
    "username" => "username",
    "password" => "password",
);

$S = getDbTunnel($tunnel_url, $config);
$data = $S->query("SELECT SQL_CALC_FOUND_ROWS id,name_en FROM events LIMIT 3");
print_r($data);
