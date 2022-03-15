<?php

require_once(__DIR__."/../vendor/autoload.php");

//	Parameters to connect on SQLite database
$params = array(
	'driver'    => 'pdo_sqlite',
	'path'      => __DIR__."/../config/chinook-insert.db",
	'memory'    => false,
);
$config = new \Monolith\Casterlith\Configuration();
$config->setSelectionReplacer("_cl"); // The replacer insures that table's aliases won't be equal to real database's table names

$orm            = new \Monolith\Casterlith\Casterlith($params, $config);  // Casterlith helps to create new instances of composers
$trackComposer  = $orm->getComposer('Acme\Composers\Track');              // Each table has its own query composer

//	Selection of the last inserted track
$track = $trackComposer
	->select("t")
	->order("t.TrackId", "desc")
	->first()
;

//	Removal of this track
$query = $trackComposer
	->delete($track)
;

//	Execute is separate of the update method to allow you to custom your query
if ($query->execute()) {
	echo "Delete is successful";
}
else {
	echo "An error occured";
}
