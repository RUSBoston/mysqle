<?php
require('mysqle.php');
require('mysqle_sql_exception.php');
/**
* Test objects class for examles
*/
class test_object
{
    public $id = null;
    public $title = null;
    public $description = null;
    protected $owner = null;
    /**
    * @internal
    */
    public function __constructor($owner=null)
    {
        $this->owner = $owner;
    }
}
$host = 'localhost';
$username = 'root';
$password = 'admin';
$dbname = 'test';

$mysql = new mysqle($host,$username,$password,$dbname);
$stmt = $mysql->get_stmt('get_by_id',"SELECT * FROM objects WHERE id=?"); // create new mysqli_stmt object
$stmt->bind_params('s',$id);
$stmt->execute();
unset($stmt);
$stmt = $mysql->get_stmt('get_by_id'); // get already prepared statement with get_by_id alias

// example of simple using get_object()
$obj = null;
$result = $mysql->get_object("SELECT id,title FROM objects WHERE id=1", $obj);
print_r($obj); // print stdClass with two property

// example of advanced using get_object()
$obj = null;
$result = $mysql->get_object("SELECT * FROM objects WHERE id=1", $obj, 'test_object', ['myself']);
print_r($obj); // print test_class object with all property and $owner='myself'

// example of advanced using of get_objects()
$generator = $mysql->get_objects("SELECT * FROM objects", 'test_class',['myself']);
foreach ($generator as $indx=>$obj) {
    print_r($obj); // print every object
}

// example of advanced using get_row() with fetch_row method
$row = null;
$result = $mysql->get_row("SELECT * FROM objects WHERE id=1", $row, 'fetch_row');
print_r($row); // print test_class object with all property and $owner='myself'

// example of advanced using of get_rows() with fetch_row method
$generator = $mysql->get_rows("SELECT * FROM objects", 'fetch_row');
foreach ($generator as $indx=>$row) {
    print_r($row); // print every rows
}

// example of using mysqle_sql_exception
try {
    throw new myqle_sql_exception('Test exception',0,'SELECT * FROM objects');
} catch (mysqle_sql_exception $mse) {
    echo $mse->getQuery(); // print SELECT * FROM objects
}  
?>
