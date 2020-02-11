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

$orm            = new \Monolith\Casterlith\Casterlith($params, $config);  // Casterlith helps to create new instances of composers
$albumComposer  = $orm->getComposer('Acme\Composers\Album');              // Each table has its own query composer

$album = $albumComposer
	->select("t")
	->where("t.AlbumId = :id")
	->setParameter('id', 3)
	->first()
;

$album->Title = "Restless and Wild (updated ".time().")";
$fieldsToUpdate = array(
	'Title',
);

$query = $albumComposer
	->update($album, $fieldsToUpdate)
	->where("AlbumId = :id")
	->setParameter("id", 3)
;

if ($query->execute()) {
	echo "Update is successful";
}
else {
	echo "An error occured";
}
