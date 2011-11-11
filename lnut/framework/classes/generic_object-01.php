<?php

class generic_object extends pager {

    // private properties
    private $uid = null;
    private $table = '';
    private $valid = false;
    private $data = array();
    public $TableData = array();
    protected $arrFields = array();
    protected $arrHelpers = array();

    // default public methods of the class
    public function get_uid() {
        return $this->uid;
    }

    public function get_valid() {
        return $this->valid;
    }

    /**
     * Fetch one row from the table using the fields requested
     */
    public function load($fields = array(), $where = array()) {
        if (count($fields) < 1) {
            $fields = $this->arrFields;
        } else {
            foreach ($fields as $fieldName) {
                if (!array_key_exists($fieldName, $this->arrFields)) {
                    return false;
                }
            }
        }

        $whereclose = '';

        if (count($where) > 0) {
            foreach ($where as $idx => $val) {
                $whereclose .=" AND `{$idx}` = '{$val}'";
            }
        } else {
            $whereclose .=" AND `uid`={$this->uid} ";
        }
        $query = "SELECT `" . implode("`, `", array_keys($fields)) . "` FROM `{$this->table}` WHERE 1=1 {$whereclose} LIMIT 1";
        $result = database::query($query);
        if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {

            $row = mysql_fetch_assoc($result);
            $columns = array_keys($row);
            $count = count($columns);

            for ($i = $count - 1; $i >= 0; $i--) {
                //if($columns[$i] != 'uid') {
                $method = 'set_' . $columns[$i];
                $this->$method(stripslashes($row[$columns[$i]]));
                //}
            }
        }
        $this->TableData = $this->arrFields;
    }

    /**
     * Requires the $table to be a single database table
     * Requires the $set array to consist of arrays of field=>$data array pairs
     * Example:
     * array('name'=>array('city','string'))
     */
    public function save($set=array()) {

        $response = false;

        $setdata = array();
        $query = "UPDATE `{$this->table}` SET ";

        if (count($set) < 1) {
            $set = $this->arrFields;
        }

        // this foreach loop needs to be updated to match
        // the format of the arrFields array
        foreach ($set as $key => $data) {
            $setdata[] = "`$key`=" . format::mysql_prepare(array('value' => $data['Value'], 'type' => $data['Type']['Data']));
        }
        $query.= implode(", ", $setdata) . " WHERE `uid`={$this->uid} LIMIT 1";
        database::query($query);

        if (!database::$error) {
            $response = $this->uid;
        } else {
            $response = database::$error;
        }

        return $response;
    }

    public function delete() {

        $response = false;

        $query = "DELETE FROM `{$this->table}` WHERE `uid`={$this->uid} LIMIT 1";
        database::query($query);

        if (!database::$error) {
            $response = true;
        } else {
            $response = database::$error;
        }
    }

    public function where_delete($where) {
        $response = false;
        $whereclose = 'WHERE 1 = 1';

        if (count($where) > 0) {
            foreach ($where as $idx => $val) {
                $whereclose .=" AND `{$idx}` = '{$val}'";
            }

            if ($whereclose != 'WHERE 1 = 1') {

                $query = "DELETE FROM `{$this->table}` {$whereclose}  LIMIT 1";
                database::query($query);

                if (!database::$error) {
                    $response = true;
                } else {
                    $response = database::$error;
                }
            }
        }
    }

    public function insert() {
        $query = "INSERT INTO `{$this->table}` (";
        $fields = array();
        $values = array();

        foreach ($this->arrFields as $key => $data) {
            $fields[] = "`{$key}`";
            $values[] = format::mysql_prepare(array('value' => $data['Value'], 'type' => $data['Type']['Data']));
        }

        if (count($fields) > 0 && count($values) > 0) {
            $query.= implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
        }

        return database::insert($query);
    }

   

    public function __construct($uid = 0, $table = '', $takeautoid = false) {

        if ($table != "") {
            $this->table = format::toTableName($table);
        }
        if (is_numeric($uid) && (int) $uid > 0) {
            $this->uid = $uid;
        }

        if (strlen($this->table) > 0) {

            $query = "SHOW COLUMNS FROM `{$this->table}`";
            $result = database::query($query);

            if (mysql_error() == '' && mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    $type = $row['Type'];
                    $data = '';
                    $size = null;

                    if (preg_match('/(.*)\((\d+)\)/', $type, $matches)) {
                        $data = strtoupper($matches[1]);
                        $size = $matches[2];
                    } else if (preg_match('/(.*)\((\d+,\d+)\)/', $type, $matches)) {
                        $data = strtoupper($matches[1]);
                        $size = $matches[2];
                    } else {
                        $data = strtoupper($type);
                    }
                    if ($row['Field'] != "uid" || $takeautoid) {
                        $this->arrFields[$row['Field']] = array(
                            'Type' => array(
                                'Size' => $size,
                                'Data' => $data
                            ),
                            'Null' => $row['Null'],
                            'Key' => $row['Key'],
                            'Default' => $row['Default'],
                            'Extra' => $row['Extra'],
                            'Value' => null
                        );
                    }
                }
            }

            if ($this->is_valid($this->uid, $this->table)) {
                $this->valid = true;
            }
        }
    }

    public function is_valid($uid = 0, $table = '') {
        /* function to check a valid record exists in the table for that UID, sanity checking mostly */
        $valid = false;

        if ($uid && is_numeric($uid) && (int) $uid > 0) {
            if (strlen($table) > 0) {
                $this->table = format::toTableName($table);
            }
            $query = "SELECT `uid` FROM `$table` WHERE `uid` = $uid LIMIT 1;";
            $result = database::query($query);

            if (!database::$error && $result && mysql_num_rows($result) > 0) {
                $valid = true;
            }
        }
        return $valid;
    }

    public function __call($method, $arguments) {
        $prefix = strtolower(substr($method, 0, 4));
        $method = substr($method, 4);

        if (!empty($prefix) && !empty($method)) {

            if (in_array($prefix, array('get_', 'set_'))) {
                if (array_key_exists($method, $this->arrFields)) {

                    $fieldName = $method;

                    if ('get_' == $prefix && isset($this->arrFields[$fieldName]['Value'])) {

                        return $this->arrFields[$fieldName]['Value'];
                    } else if ('set_' == $prefix) {

                        $value = $arguments[0];

                        $this->arrFields[$fieldName]['Value'] = $value;
                    }
                } else {

                    $helper = $method;

                    if (count($this->arrHelpers) > 0) {
                        if (isset($this->arrHelpers[$helper])) {
                            $objHelper = new $this->arrHelpers[$helper];
                            return $objHelper->run($arguments);
                        }
                    }
                }
            }
        }
    }

    public static function redirectTo($url) {
        if (!headers_sent($filename, $linenum)) {
            header('Location: ' . config::url($url));
            exit();
        } else {
            echo "Headers already sent in $filename on line $linenum\n";
        }
    }

}

?>