<?php 

function cleanSqlLiteDB()
{
	$fileName = __DIR__ . "/sqllite-unit-tests-writable.db";
	if (file_exists($fileName)) {
		unlink($fileName);
	}
}

function getAReadOnlyOrmInstance(\Monolith\Casterlith\Configuration $config = null)
{
	$params = array(
		'driver'  => 'pdo_sqlite',
		'path'    => __DIR__."/sqllite-unit-tests-readonly.db",
		'memory'  => false,
	);
	if (is_null($config)) {
		$config = new \Monolith\Casterlith\Configuration($replacer);
	}

	$casterlith = new \Monolith\Casterlith\Casterlith($params, $config);

	return $casterlith;
}