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
  $db->Select( $table );
</pre>

<h3>Select with conditions</h3>
<pre>
  $conditions = array(
  	'id' => 1,
	'created >' => '2016-04-25'
  );
  $db->Select( $table , $conditions );
</pre>
Alternatively you can use
<pre>
  $db->Select[$table]( $conditions );
</pre>
`$table` must be capitalized.  Example you have a `table` named `users`,  you should do `$db->SelectUsers( $conditions )`