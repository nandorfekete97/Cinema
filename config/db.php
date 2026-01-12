<?php

return [
    // project/db specific credentials should come from environment variables
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=cinema',
    'username' => 'cinema',
    'password' => 'Passw0rd!',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
