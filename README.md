# SimplePDOClass
<h3>Database Credentials</h3>
<pre>
  Open config.php and enter credentials
</pre>

<h3>Calling the Dbconnect class</h3>
<pre>
  $db = new Dbconnect();
</pre>

<h3>Select</h3>
<pre>
  $db->Select[$table](  );
</pre>
`$table` must be capitalized.  Example you have a `table` named `users`,  you should do `$db->SelectUsers( )`.

<h3>Select with conditions</h3>
<pre>
  $conditions = array(
  	'id' => 1,
	'created >' => '2016-04-25'
  );
  $db->SelectUsers( $conditions );
</pre>
Above will produce the query string
<pre>
	Select * from users where id = '1' and created > '2016-04-25'
</pre>

<h3>Update</h3>
<pre>
  $conditions = array(
  	'id' => 1,
	'status' => 2
  );
  $db->UpdateUsers( $conditions );
</pre>
Above will produce the query string
<pre>
	update users set status = '2' where id = '1'
</pre>
`Update[$table]()` always need an `id` to work

<h3>Update where</h3>
<pre>
  $columns = array(
	'name' => 'John Doe',
	'email' => 'email@email.com'
  );
  $conditions = array(
  	'id' => 1,
	'status !=' => 2
  );
  $db->UpdatewhereUsers( $columns , $conditions );
</pre>
Above will produce the query string
<pre>
	update users set name = 'John Doe', email = 'email@email.com' where id = '1' and status != 2
</pre>

<h3>Delete</h3>
<pre>
  $conditions = array(
  	'id' => 1,
	'status' => 2
  );
  $db->DeleteUsers( $conditions );
</pre>
Above will produce the query string
<pre>
	Delete from users where status = '2' and id = '1'
</pre>

<h3>Insert</h3>
<pre>
  $columns = array(
	'status' => 1,
	'name' => 'John Doe',
	'email' => 'email@email.com',
	'created' => '2016-04-25'
  );
  $db->InsertUsers( $columns );
</pre>
Above will produce the query string
<pre>
	Insert into users (status,name,email,created) values ('1','John Doe','email@email.com','2016-04-25')
</pre>
`$db->InsertUsers( $columns );` will return the last inserted id

<h3>Join</h3>
<pre>
  $db
  ->joins(array(
    'type' => 'left',
    'table' => 'images',
	'on' => 'images.user_id = users.id'
  ))
  ->SelectUsers(  );
</pre>
Above will produce the query string
<pre>
	Select * from users left join images on images.user_id = users.id
</pre>
Default `type` is `left` so no need to add it in the options.  You can also add an alias to the `joins` option like
<pre>
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
</pre>
Above will produce the query string
<pre>
	Select * from users right join images as im on im.user_id = users.id where im.type = '3'
</pre>

<h3>Select with specific columns</h3>
<pre>
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
</pre>
Above will produce the query string
<pre>
	Select users.*, images.url, images.type from users left join images on images.user_id = users.id
</pre>

<h3>Group</h3>
<pre>
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
</pre>
Above will produce the query string
<pre>
	Select users.*, images.url, images.type from users left join images on images.user_id = users.id group by images.type
</pre>

<h3>Order</h3>
<pre>
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
</pre>
Above will produce the query string
<pre>
	Select users.*, images.url, images.type from users left join images on images.user_id = users.id order by images.created group by images.type
</pre>

<h3>Limit</h3>
<pre>
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
  ->SelectUsers(  );
</pre>
Above will produce the query string
<pre>
	Select users.*, images.url, images.type from users left join images on images.user_id = users.id order by images.created group by images.type limit 5
</pre>

<h3>Paging</h3>
<pre>

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
</pre>
You can get the pagination information after doing like this
<pre>
	$db->$pgntion
</pre>

<h3>Or condition</h3>
<pre>
  $conditions = array(
    'type' => 2,
	'or' => array(
	  'name like' => '%John%',
	  'email like' => '%john@email.com%',
	  'id in' => '(1,3,5)'
	)
  );
  $db->SelectUsers( $conditions );
</pre>
Above will produce the query string
<pre>
	Select * from users where type = '2' and (name like '%John%' or email like '%john@email.com%' or id in (1,3,5))
</pre>
<br /><br />
<h1>Side note</h1>
Some cases are not considered thats why you can use the normal prepared statements
<h2>Insert</h2>
<pre>
  $query = "Insert into users ( name , email , type ) values ( :name , :email , :type )";
  $data = array(
    'name' => 'John Doe',
	'email' => 'john@doe.com',
	'type' => 2
  );
  $db->insertRow( $query , $data );
</pre>

<h2>Select</h2>
<pre>
  $query = "Select * users where id = :id";
  $data = array(
    'id' => 1
  );
  $db->getRow( $query , $data ); // for single result
  $db->getRows( $query , $data ); // for multiple results
</pre>

<h2>Update</h2>
<pre>
  $query = "Update users set name = :name where type = :type";
  $data = array(
    'name' => 'John Doe',
	'type' => 2
  );
  $db->updateRow( $query , $data );
</pre>

<h2>Delete</h2>
<pre>
  $query = "Delete from users where type = :type";
  $data = array(
	'type' => 2
  );
  $db->deleteRow( $query , $data );
</pre>