# SimplePDOClass

## Installation

  - Clone this repo to your project
  - Open `config.php` and configure it with your database credentials
  - Import the class into your app

    ```php
    require_once 'Dbconnect.php';
    ```

  - Call the class

    ```php
    // Initializing the class
    $db = new Dbconnect();

    // Retrieve everything from the users table
    $db->SelectUsers();
    ```

  - And you're all set!

## Usage

### Select
```php
$db->Select[$table](  );
```
`$table` must be capitalized.  Example you have a `table` named `users`,  you should do `$db->SelectUsers( )`.

### Select with conditions
```php
$conditions = array(
  'id' => 1,
  'created >' => '2016-04-25'
);
$db->SelectUsers( $conditions );
```
Above will produce the query string
```php
Select * from users where id = '1' and created > '2016-04-25'
```

### Update
```php
$conditions = array(
  'id' => 1,
  'status' => 2
);
$db->UpdateUsers( $conditions );
```
Above will produce the query string
```php
update users set status = '2' where id = '1'
```
`Update[$table]()` always need an `id` to work

### Update where
```
$columns = array(
  'name' => 'John Doe',
  'email' => 'email@email.com'
);
$conditions = array(
  'id' => 1,
  'status !=' => 2
);
$db->UpdatewhereUsers( $columns , $conditions );
```
Above will produce the query string
```
update users set name = 'John Doe', email = 'email@email.com' where id = '1' and status != 2
```

### Delete
```php
  $conditions = array(
    'id' => 1,
    'status' => 2
  );
  $db->DeleteUsers( $conditions );
```
Above will produce the query string
```php
Delete from users where status = '2' and id = '1'
```

### Insert
```php
$columns = array(
  'status' => 1,
  'name' => 'John Doe',
  'email' => 'email@email.com',
  'created' => '2016-04-25'
);
$db->InsertUsers( $columns );
```
Above will produce the query string
```
Insert into users (status,name,email,created) values ('1','John Doe','email@email.com','2016-04-25')
```
`$db->InsertUsers( $columns );` will return the last inserted id

### Join
```php
$db
->joins(array(
  'type' => 'left',
  'table' => 'images',
  'on' => 'images.user_id = users.id'
))
->SelectUsers(  );
```
Above will produce the query string
```php
Select * from users left join images on images.user_id = users.id
```
Default `type` is `left` so no need to add it in the options.  You can also add an alias to the `joins` option like
```php
$db
->joins(array(
  'type' => 'right',
  'table' => 'images',
  'as' => 'im',
  'on' => 'im.user_id = users.id'
))
->SelectUsers( array(
  'im.type' => 3
) );
```
Above will produce the query string
```php
Select * from users right join images as im on im.user_id = users.id where im.type = '3'
```

### Select with specific columns
```php
$db
->columns(array(
  'users.*',
  'images.url',
  'images.type'
))
->joins(array(
  'type' => 'left',
  'table' => 'images',
  'on' => 'images.user_id = users.id'
))
->SelectUsers(  );
```
Above will produce the query string
```
Select users.*, images.url, images.type from users left join images on images.user_id = users.id
```

### Group
```php
$db
->group('images.type')
->columns(array(
  'users.*',
  'images.url',
  'images.type'
))
->joins(array(
  'type' => 'left',
  'table' => 'images',
  'on' => 'images.user_id = users.id'
))
->SelectUsers(  );
```

Above will produce the query string
```php
Select users.*, images.url, images.type from users left join images on images.user_id = users.id group by images.type
```

### Order
```php
$db
->order('images.created desc')
->group('images.type')
->columns(array(
  'users.*',
  'images.url',
  'images.type'
))
->joins(array(
  'type' => 'left',
  'table' => 'images',
'on' => 'images.user_id = users.id'
))
->SelectUsers(  );
```

Above will produce the query string
```php
Select users.*, images.url, images.type from users left join images on images.user_id = users.id order by images.created group by images.type
```

### Limit
```php
$db
->limit(5)
->order('images.created desc')
->group('images.type')
->columns(array(
  'users.*',
  'images.url',
  'images.type'
))
->joins(array(
  'type' => 'left',
  'table' => 'images',
  'on' => 'images.user_id = users.id'
))
->SelectUsers();
```
Above will produce the query string
```php
Select users.*, images.url, images.type from users left join images on images.user_id = users.id order by images.created group by images.type limit 5
```

### Paging
```php
$page = 1;  // will get page 1

$db
->limit( 5 , $page )
->order('images.created desc')
->group('images.type')
->columns(array(
  'users.*',
  'images.url',
  'images.type'
))
->joins(array(
  'type' => 'left',
  'table' => 'images',
  'on' => 'images.user_id = users.id'
))
->SelectUsers(  );
```
You can get the pagination information after doing like this
```php
$db->$pgntion
```

### Or condition
```php
$conditions = array(
'type' => 2,
'or' => array(
    'name like' => '%John%',
    'email like' => '%john@email.com%',
    'id in' => '(1,3,5)'
  )
);
$db->SelectUsers( $conditions );
```
Above will produce the query string
```php
Select * from users where type = '2' and (name like '%John%' or email like '%john@email.com%' or id in (1,3,5))
```
<br /><br />
# Side note
Some cases are not considered thats why you can use the normal prepared statements
## Insert
```php
$query = "Insert into users ( name , email , type ) values ( :name , :email , :type )";
$data = array(
  'name' => 'John Doe',
  'email' => 'john@doe.com',
  'type' => 2
);
$db->insertRow( $query , $data );
```

## Select
```php
$query = "Select * users where id = :id";
$data = array(
  'id' => 1
);
$db->getRow( $query , $data ); // for single result
$db->getRows( $query , $data ); // for multiple results
```

## Update
```php
$query = "Update users set name = :name where type = :type";
$data = array(
  'name' => 'John Doe',
  'type' => 2
);
$db->updateRow( $query , $data );
```

## Delete
```php
$query = "Delete from users where type = :type";
$data = array(
  'type' => 2
);
$db->deleteRow( $query , $data );
```