<!DOCTYPE html>
<html>
<body>
<h2>secret-server example task</h2>

<h3>Installation</h3>

<code>$ composer require sinkab/secret-server "dev-master"</code>

<h3>Basic Usage</h3>
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

</body>
</html>
