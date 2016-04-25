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
`$table` must be capitalized.  Example you have a `table` named `users`,  you should do `$db->SelectUsers( $conditions )`
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