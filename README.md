<!DOCTYPE html>
<html>
<body>
<h2>secret-server example task</h2>

<h3>Installation</h3>

<code>$ composer require sinkab/secret-server "dev-master"</code>

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
<h3>Basic Usage</h3>
<p>This API contain two feathure</p>
<p>1. add a secret: yourdomain/<b>v1/xml | json/secret</b> use POST method</p>
<p><b>Params:</b></p>
<pre>
    secret - This is the secret text (string /required)
    expireAfterViews - How many times the secret can be viewed (integer, > 0, require)
    expireAfter - The secret cannot be reached after this time (integer, 0 - it does not expire, expire time in mintues, require)
</pre>
<p>The params send in the "application/x-www-form-urlencoded" format<b></b>, e.g. </p>
<pre>secret=big%20%secret&AMP;expireAfterViews=10&AMP;expireAfter=10</pre>
<p>2. get a secret: yourdomain/<b>v1/xml | json/secret/secret_hash_code</b> use GET method</p>
<p>The type of answer is depending of the URL's part <b>xml | json</b></p>
</body>
</html>
