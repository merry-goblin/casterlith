<?php

require_once(__DIR__."/../vendor/autoload.php");

//	Parameters to connect on SQLite database
$params = array(
	'driver'    => 'pdo_sqlite',
	'path'      => __DIR__."/../config/chinook.db",
	'memory'    => false,
);
$config = new \Monolith\Casterlith\Configuration();
$config->setSelectionReplacer("_cl"); // The replacer insures that table's aliases won't be equal to real database's table names

$orm  = new \Monolith\Casterlith\Casterlith($params, $config);  // Casterlith helps to create new instances of composers
$dbal = $orm->getDBALConnection();

$sql = "
	UPDATE albums
	SET   Title   = :title
	WHERE AlbumId = :id
";
$values = array(
	'id'    => 3,
	'title' => "Restless and Wild (updated ".time().")",
);

$numberOfUpdatedRows = $dbal->executeUpdate($sql, $values);
if ($numberOfUpdatedRows === false) {
	echo "An error occured";
}
else {
	echo "Update successful";
}