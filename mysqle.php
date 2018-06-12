<?php
/**
* Extended class MySQLi (MySQLe) for faster and convenient work with mysqli class, queries,
* obtaining all query result rows, generators, etc.
* 
* @package RUSBoston
* @subpackage mysqle
* @author Sergey Ivanov (RUSBoston)                         
* @see https://github.com/RUSBoston/mysqle/
* @version 0.1
* @copyright 06/11/2018
*/
class mysqle extends mysqli {
    /**
    * Array of prepared mysqli statements, idents by alias array key
    * @var mysqli_stmt[]
    */
    protected $stmt = [];
    /**
    * Override parent class constructor, added throw mysqle_sql_exception in case of connection error.
    * 
    * @throws mysqle_sql_exception In case of server connection error
    * @param {string|null} $host MySQL server hostname
    * @param {string|null} $username MySQL server username
    * @param {string|null} $passwd MySQL server user password
    * @param {string|null} $dbname MySQL server database name
    * @param {int|null} $port MySQL server port
    * @param {string|null} $socket MySQL server socket
    * @return mysqle
    */
    public function __construct($host=null, $username=null, $passwd=null, $dbname=null, $port=null, $socket=null)
    {
        parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
        if (mysqli_connect_errno()) {
            throw new mysqle_sql_exception(mysqli_connect_error(), mysqli_connect_errno());
        }
    }
    /**
    * Method for prepare or get aleady prepared mysqli statement from local object cache.
    * @throws mysqle_sql_exception In case of some SQL-error in query text
    * @param string $alias Alias of prepared statement
    * @param {string|null} $query Text of prepared SQL query
    * @return mysqli_stmt
    */
    public function &get_stmt($alias, $query=null)
    {
        if (!array_key_exists($alias,$this->stmt) || !is_a($this->stmt[$alias],'mysqli_stmt')) {
            $this->stmt[$alias] = $this->prepare($query);
            if ($this->errno) {
                throw new mysqle_sql_exception($this->error, $this->errno, $query);
            }
        }
        return $this->stmt[$alias];
    }
    /**
    * Method for fast get single object (first row result) from query result or from statement uxecute result.
    * Method return true if query result have 1 or more rows.
    * 
    * @throws mysqle_sql_exception In case of some SQL-error in query text 
    * @param {string|mysqli_stmt} $query Text of SQL-query or prepared statement
    * @param {mixed|stdClass|null} $object object for return
    * @param string $class_name Name of returning object class
    * @param {array|null} $construct_params Array of class constructor parameters
    * @return bool
    */
    public function get_object($query, &$object, $class_name='\stdClass', $construct_params=null)
    {
        $result = false;
        if ($query instanceof mysqli_stmt) {
            $query->execute();
            /**
            * @var mysqli_result
            */
            $res = $query->get_result();
        } else {
            /**
            * @var mysqli_result
            */
            $res = $this->query($query);
        }
        if ($result = ($res->num_rows > 0)) {
            $object = $res->fetch_object($class_name, $construct_params);
        }
        $res->free();
        return $result;
    }
    /**
    * Generator for getting all objects from SQL-query or prepared statement.
    * Can use in foreach(...) cycles
    * 
    * @see http://php.net/manual/en/language.generators.php
    * @throws mysqle_sql_exception In case of some SQL-error in query text
    * @param {string|mysqli_stmt} $query Text of SQL-query or prepared statement
    * @param string $class_name Name of returning object class
    * @param {array|null} $construct_params Array of class constructor parameters
    * @return bool
    */
    public function get_objects($query, $class_name='\stdClass', $construct_params=null)
    {
        if ($query instanceof mysqli_stmt) {
            $query->execute();
            /**
            * @var mysqli_result
            */
            $res = $query->get_result();
        } else {
            /**
            * @var mysqli_result
            */
            $res = $this->query($query);
        }
        if ($res->num_rows > 0) {
            while ($object = $res->fetch_object($class_name, $construct_params)) {
                yield $object;
            }
        }
        $res->free();
    }
    /**
    * Method for getting first row from query result into $row and return true/false.
    * 
    * @throws mysqle_sql_exception In case of some SQL-error in query text
    * @param {string|mysqli_stmt} $query Text of SQL-query or prepared statement
    * @param {array|stdClass|null} $row Returning array or stdClass with row columns
    * @param string $method метод Method for returned result: fetch_row, fetch_assoc, fetch_object
    * @return bool
    */
    public function get_row($query, &$row, $method='fetch_assoc')
    {
        if ($query instanceof mysqli_stmt) {
            $query->execute();
            /**
            * @var mysqli_result
            */
            $res = $query->get_result();
        } else {
            /**
            * @var mysqli_result
            */
            $res = $this->query($query);
        }
        $result = false;
        if ($result = ($res->num_rows > 0)) {
            switch ($method) {
                case 'fetch_row':
                    // no break
                case 'fetch_assoc':
                    // no break
                case 'fetch_object':
                    $row = call_user_func([$res,$method]);
                    break;
                default:
                    trigger_error("Unknown ".__METHOD__." method value: {$method}",E_USER_WARNING);
                    break;
            }            
        }
        $res->free();
        return $result;
    }
    /**
    * Generator for getting all query resulting rows consistently.
    * Can use in foreach(...) cycles
    * 
    * @see http://php.net/manual/en/language.generators.php
    * @throws mysqle_sql_exception In case of some SQL-error in query text
    * @param {string|mysqli_stmt} $query Text of SQL-query or prepared statement
    * @param string $method Method for returned result: fetch_row, fetch_assoc, fetch_object
    * @return mixed[]
    */
    public function get_rows($query, $method='fetch_assoc')
    {
        /**
        * One result row
        * @var mixed
        */
        $row = null;
        if ($query instanceof mysqli_stmt) {
            $query->execute();
            /**
            * @var mysqli_result
            */
            $res = $query->get_result();
        } else {
            /**
            * @var mysqli_result
            */
            $res = $this->query($query);
            if ($this->errno) {
                throw new mysqle_sql_exception($this->error,$this->errno,$query);
            }
        }
        if ($res->num_rows > 0) {
            switch ($method) {
                case 'fetch_row':
                    // no break
                case 'fetch_assoc':
                    // no break
                case 'fetch_object':
                    while ($row = call_user_func([$res,$method])) {
                        yield $row;
                    }
                    break;

                default:
                    trigger_error("Unknown ".__METHOD__." method value: {$method}",E_USER_WARNING);
                    break;
            }            
            
        }
        $res->free();
    }
    /**
    * @inheritdoc
    * @throws mysqle_sql_exception In case of some SQL-query errors
    */
    public function query($query, $resultmode=null)
    {
        $res = parent::query($query, $resultmode=null);
        if ($this->errno) {
            throw new mysqle_sql_exception($this->error, $this->errno, $query);
        } else {
            return $res;
        }        
    }   
}  
?>
