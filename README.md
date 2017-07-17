<!DOCTYPE html>
<html>
<body>
<h2>secret-server example task</h2>

<h3>Installation</h3>

<code>$ composer require sinkab/secret-server "dev-master"</code>

<h3>Basic Usage</h3>
<p>Create an index.php file into your web folder</p>
<p>Copy this code and actualize that</p>
<pre>
&lt;?php

    require __DIR__.'/vendor/autoload.php';
    //setting up your database parameters
    $config['db'] = [
        'host' => 'localhost',
        'user' => 'dbuser',
        'password' => 'dbuserpassword',
        'database_name' => 'secret_server'
    ];
    
    $secret = new \Sinkab\SecretServer($config);
</pre>
<p>Copy .htaccess file into your web folder</p>

</body>
</html>
