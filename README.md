# RUSBoston\mysqle
Extended mysqli PHP extension for simple and fastest development.

This is my vision of extended functionality of mysqli PHP extension.
Use RUSBoston\mysqli classes for fastest way developing MySQL connections, queryies, statements.

## Extended class mysqle
### Method mysqle->get_stmt($alias, $query=null)
Method for prepare new **\mysqli_stmt** object or get already prepared from local object cache.

#### Example
```
    $mysql = new mysqle($host,$username,$password,$dbname);
    $stmt = $mysql->get_stmt('get_by_id',"SELECT * FROM objects WHERE id=?"); // greate new mysqli_stmt object
    $stmt->bind_params('s',$id);
    $stmt->execute();
    unset($stmt);
    $stmt = $mysql->get_stmt('get_by_id'); // get already prepared statement with get_by_id alias
```

### Method mysqle->get_object($query, &$obj, $class_name='\stdClass', $construct_params=null)
Method for fast get single object (first row result) from query result or from statement uxecute result.
Method return true if query result have 1 or more rows.

#### Example
```
    $obj = null;
    $result = $mysql->get_object("SELECT * FROM objects WHERE id=1", $obj, 'test_object', ['myself']);
    print_r($obj); // print test_class object with all property and $owner='myself'
```

### Method mysqle->get_objects($query, $class_name='\stdClass', $construct_params=null)
[Generator](http://php.net/manual/en/language.generators.php) for getting all objects from SQL-query or prepared statement.
Can use in foreach(...) cycles

#### Example
```
    $generator = $mysql->get_objects("SELECT * FROM objects", 'test_class',['myself']);
    foreach ($generator as $indx=>$obj) {
        print_r($obj); // print every object
    }
```

### Method mysqle->get_row($query, &$row, $method='fetch_assoc')
Method for getting first row from query result into $row and return true/false.

#### Example
```
    $row = null;
    $result = $mysql->get_row("SELECT * FROM objects WHERE id=1", $row, 'fetch_row');
    print_r($row); // print test_class object with all property and $owner='myself'
```

### Method mysqle->get_rows($query, $method='fetch_assoc')
Generator for getting all query resulting rows consistently.
Can use in foreach(...) cycles
```
    $generator = $mysql->get_rows("SELECT * FROM objects", 'fetch_row');
    foreach ($generator as $indx=>$row) {
        print_r($row); // print every rows
    }
```

## Extended class mysqle_sql_exception
### Constructor mysqle_sql_exception->__construct($message=null, $code=null, $query=null, $previous=null)
New constructor argument - SQL query text for debugging. Later you can get query text by call **mysqle_sql_exception->getQuery()** method.

### Method mysqle_sql_exception->getQuery()
Method for get last query text, wich generate this exception.

#### Example
```
    try {
        throw new myqle_sql_exception('Test exception',0,'SELECT * FROM objects');
    } catch (mysqle_sql_exception $me) {
        echo $me->getQuery(); // print SELECT * FROM objects
    }
```
