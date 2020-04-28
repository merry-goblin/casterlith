<?php 

function cleanSqlLiteDB()
{
	$fileName = __DIR__."/sqlite-unit-tests-writable.db";
	if (file_exists($fileName)) {
		unlink($fileName);
	}
}

function getAReadOnlyOrmInstance($name = "unit-tests", \Monolith\Casterlith\Configuration $config = null)
{
	$dbName = "";
	switch ($name) {
		case "unit-tests":
			$dbName = "sqlite-unit-tests-readonly.db";
			break;
		case "types":
			$dbName = "sqlite-types-readonly.db";
			break;
		default:
			$dbName = "sqllite-unit-tests-readonly.db";
	}

	$params = array(
		'driver'  => 'pdo_sqlite',
		'path'    => __DIR__."/".$dbName,
		'memory'  => false,
	);
	if (is_null($config)) {
		$config = new \Monolith\Casterlith\Configuration($replacer);
	}

	$casterlith = new \Monolith\Casterlith\Casterlith($params, $config);

	return $casterlith;
}

function getPrivateValue($instance, $propertyName)
{
	$reflectionProperty = new \ReflectionProperty(get_class($instance), $propertyName);
	$reflectionProperty->setAccessible(true);
	$value = $reflectionProperty->getValue($instance);

	return $value;
}