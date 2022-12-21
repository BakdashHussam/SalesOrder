<?php

return [
    'class' => 'yii\db\Connection',
	//'dsn' => 'sqlsrv:Server=172.168.2.3,1433;Database=th_v3_cbz_transaction',
	'dsn' => 'sqlsrv:Server=192.168.1.3,1433;Database=th_v3_cbz_transaction',
    'username' => 'thV3Operator',
    'password' => 'k@0G4M00P0w3r#',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
