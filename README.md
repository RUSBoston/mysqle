# RUSBoston\mysqle
Extended mysqli PHP extension for simple and fastest development.

This is my vision of extended functionality of mysqli PHP extension.
Use RUSBoston\mysqli classes for fastest way developing MySQL connections, queryies, statements.

## Extended class mysqle
### Method mysqle->get_stmt($id, $query)
Method for prepare new **\mysqli_stmt** object or get already prepared from local object cache.



## Extended class mysqle_sql_exception
### Constructor mysqle_sql_exception->__construct($message=null, $code=null, $query=null, $previous=null)
New constructor argument - SQL query text for debugging. Later you can get query text by call **mysqle_sql_exception->getQuery()** method.

### Method mysqle_sql_exception->getQuery()
Method for get last query text, wich generate this exception.
