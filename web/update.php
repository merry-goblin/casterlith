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

//	Selection of an album
$album = $albumComposer
	->select("t")
	->where("t.AlbumId = :id")
	->setParameter('id', 3)
	->first()
;

//	Modification of this album
$album->Title = "Restless and Wild (updated ".time().")";

//	Update of database
$fieldsToUpdate = array(
	'Title',
);
$query = $albumComposer
	->update($album, $fieldsToUpdate)
;

//	Execute is separate of the update method to allow you to custom your query
if ($query->execute()) {
	echo "Update is successful";
}
else {
	echo "An error occured";
}
