<?php
/**
* Extended mysqli_sql_exception class.
* Added new protected property $query and method getQuery() for return SQL-query text, wich throw this exception.
* 
* @package RUSBoston
* @subpackage mysqle
* @author Sergey Ivanov (RUSBoston)                         
* @see https://github.com/RUSBoston/mysqle/
* @version 0.1
* @copyright 06/11/2018
*/
class mysqle_sql_exception extends mysqli_sql_exception
{
    protected $query = null;
    /**
    * Method for getting SQL-query text
    * @example example.php
    * @return string
    */
    public function getQuery()
    {
        return $this->query;
    }
    /**
    * Override class constructor
    * 
    * @param string $message Exception string message
    * @param int $code Exception integer code value
    * @param string $query SQL-query, wich throw this exception
    * @param {Exception|null} $previous Object of previuos exception
    * @return Exception
    */
    public function __construct($message=null, $code=null, $query=null, $previous=null)
    {
        $this->query = $query;
        parent::__construct($message, $code, $previous);
    }
}
?>
