<?php
/**
* Расширенный класс MySQLi Extended для более быстрой и удобной работы с запросами,
* получение всех строк запроса через генераторы и т.д.
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
    * Can use in foreach(...)
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
    * Метод получения одной строки по запросу, в качестве результата метод отдаёт true/false
    * 
    * @param {string|mysqli_stmt} $query SQL-запрос
    * @param {array|stdClass|null} $row возвращаемый массив с результатами
    * @param string $method метод возврата результата: fetch_row, fetch_assoc, fetch_object
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
    * Метод-генератор для получения всех результатов запроса последовательно.
    * Используется в конструкциях foreach(...)
    * 
    * @param {string|mysqli_stmt} $query SQL-запрос в виде текста или в виде подготовленного запроса
    * @param string $method метод возврата результата: fetch_row, fetch_assoc, fetch_object
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
