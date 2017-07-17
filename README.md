<!DOCTYPE html>
<html>
<body>
<h2>secret-server example task</h2>

<h3>Installation</h3>

<code>$ composer require sinkab/secret-server "dev-master"</code>

<h3>Basic Usage</h3>
<pre>
&lt;?php

    require_once __DIR__.'/config/settings.inc';<br>
    require __DIR__.'/vendor/autoload.php';
    
    $secret = new \Sinkab\SecretServer($config);
</pre>

<h3>Configure databse</h3>

<p>Edit config/settings.inc, set up your databse informations</p>
<pre>
$config['db'] = [
    'host' => 'localhost',
    'user' => 'dbuser',
    'password' => 'dbuserpassword',
    'database_name' => 'secret_server'
];
</pre>
</body>
</html>
