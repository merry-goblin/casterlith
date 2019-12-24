<?php 

function cleanSqlLiteDB()
{
	$fileName = __DIR__ . "/sqllite-unit-tests-writable.db";
	if (file_exists($fileName)) {
		unlink($fileName);
	}
}

function getAReadOnlyOrmInstance()
{
	$params = array(
		'driver'  => 'pdo_sqlite',
		'path'    => __DIR__."/sqllite-unit-tests-readonly.db",
		'memory'  => false,
	);
	$config = new \Monolith\Casterlith\Configuration();

	$casterlith = new \Monolith\Casterlith\Casterlith($params, $config);

	return $casterlith;
}