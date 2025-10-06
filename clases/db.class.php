<?php

include_once "response.class.php";

class DataBaseConection
{
    const CON_MOVESA = "movesa";
    const CON_MOVESA_WEB_1_3 = "movesa_web_1_3";
    const CON_MOVESA_WEB_1_70 = "movesa_web_1_70";
    const CON_SYT_SCORING = "syt_scoring_cs";
    const CON_SKG_BP = "skg_bp";
    private static $conections;
    public $current_conn;
    public $transaction_in_process = false;
    public $conn;

    function __construct()
    {
        self::$conections = array(
            "movesa" => array(
                "serverName" => "192.168.1.3",
                "Database" => "MOVESA",
                "UID" => "Consultor01",
                "PWD" => "Sql1sapphp@!"
            ),
            "movesa_web_1_3" => array(
                "serverName" => "192.168.1.3",
                "Database" => "MOVESAweb",
                "UID" => "Consultor01",
                "PWD" => "Sql1sapphp@!"
            ),
            "movesa_web_1_70" => array(
                "serverName" => "192.168.1.70",
                "Database" => "MOVESAweb",
                "UID" => "sa",
                "PWD" => "S@pB1Sql"
            ),
            "syt_scoring_cs" => array(
                "serverName" => "192.168.1.60",
                "Database" => "sytScoringCS",
                "UID" => "sa",
                "PWD" => "S@pB1Sql"
            ),
            "skg_bp" => array(
                "serverName" => "192.168.1.60",
                "Database" => "SKG_BP",
                "UID" => "sa",
                "PWD" => "S@pB1Sql"
            ),
        );
    }

    public static function getConnection($_conection)
    {
        $response = new HTTPResponse();
        $_self = new self();

        $conection_data = self::$conections[$_conection];

        $dsn = "sqlsrv:server={$conection_data['serverName']};Database={$conection_data['Database']}";
        try {
            $conn = new PDO($dsn, $conection_data['UID'], $conection_data['PWD']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $response->ok($conn);
        } catch (PDOException $e) {
            $message_error = "DataBase Connection Error: " . $_self->convert_utf8($e->getMessage());
            $response->Error(500, $message_error);
        }

        return $response;
    }

    public function executeQuery(
        $_conection,
        $_sqlQuery,
        $_params = array(),
        $_returnIdentity = false,
        $_returnColumns = false,
        $_initTransaction = false
    ) {
        $response = new HTTPResponse();

        if (empty($this->conn) || $this->current_conn != $_conection) {
            $this->conn = $this->getConnection($_conection);
            $this->current_conn = $_conection;
        }

        if ($this->conn->Success) {

            if ($_initTransaction) {
                $this->beginTransaction($this->current_conn);
            }

            try {
                $stmt = $this->conn->Data->prepare($_sqlQuery);
                $stmt->execute($_params);

                $data = $_returnIdentity ? $this->fetchIdentity($_conection)->Data : null;

                if ($_returnColumns) {
                    $headers = array();
                    for ($i = 0; $i < $stmt->columnCount(); $i++) {
                        $columnMeta = $stmt->getColumnMeta($i);
                        $headers[] = $columnMeta['name'];
                    }
                    $data = array("queryData" => $data, "queryHeaders" => $headers);
                }

                $response->Ok($data);
            } catch (PDOException $e) {
                $response->Error(500, $this->convert_utf8($e->getMessage()));
            }
        } else {
            $response->Error(500, $this->conn->Message);
        }

        return $response;
    }

    public function fetchQuery(
        $_conection,
        $_sqlQuery,
        $_params = array(),
        $_returnFirst = false,
        $_returnColumns = false,
        $_initTransaction = false
    ) {
        $response = new HTTPResponse();

        if (empty($this->conn) || $this->current_conn != $_conection) {
            $this->conn = $this->getConnection($_conection);
            $this->current_conn = $_conection;
        }

        if ($this->conn->Success) {

            if ($_initTransaction) {
                $this->beginTransaction($this->current_conn);
            }

            try {
                $stmt = $this->conn->Data->prepare($_sqlQuery);
                $stmt->execute($_params);

                if ($_returnFirst) {
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $data = $this->map_object_utf8($data ? $data : array());
                } else {
                    $data = array();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $this->map_object_utf8($row);
                    }
                }

                if ($_returnColumns) {
                    $headers = array();
                    for ($i = 0; $i < $stmt->columnCount(); $i++) {
                        $columnMeta = $stmt->getColumnMeta($i);
                        $headers[] = $columnMeta['name'];
                    }
                    $data = array("queryData" => $data, "queryHeaders" => $this->map_object_utf8($headers));
                }

                $response->Ok($data);
            } catch (PDOException $e) {
                $response->Error(500, $this->convert_utf8($e->getMessage()));
            }
        } else {
            $response->Error(500, $this->conn->Message);
        }

        return $response;
    }

    private function fetchIdentity($_conection)
    {
        $response = new HTTPResponse();
        if (empty($this->conn) || $this->current_conn != $_conection) {
            $this->conn = $this->getConnection($_conection);
            $this->current_conn = $_conection;
        }

        try {
            $stmt = $this->conn->Data->query("SELECT @@IDENTITY AS [_IDENTITY]");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $response->Ok($data);
        } catch (PDOException $e) {
            $response->Error(500, $this->convert_utf8($e->getMessage()));
        }

        return $response;
    }

    public function beginTransaction($_conection = null)
    {
        if (empty($this->conn) || $this->current_conn != $_conection) {
            $this->transaction_in_process = false;
            $this->conn = $this->getConnection($_conection);
            $this->current_conn = $_conection;
        }

        if (!$this->transaction_in_process) {
            $this->transaction_in_process = true;
            $this->conn->Data->beginTransaction();
        }
    }

    public function finishTransaction($transactionStatus)
    {
        $this->transaction_in_process = false;
        if ($transactionStatus) {
            return $this->conn->Data->commit();
        } else {
            return $this->conn->Data->rollBack();
        }
    }

    public function convert_utf8($str)
    {
        return utf8_encode($str);
    }

    public function map_object_utf8($object)
    {
        $response = array();
        foreach ($object as $key => $value) {

            $key = $this->convert_utf8($key);
            if (is_object($value) || is_array($value)) {
                $response[$key] = $this->map_object_utf8($value);
            } else {
                $response[$key] = $this->convert_utf8($value);
            }
        }

        return $response;
    }
}
