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

$track = new \Acme\Entities\Track();
$track->TrackId       = null;
$track->Name          = "Acme+".time();
$track->AlbumId       = 1;
$track->MediaTypeId   = 1;
$track->GenreId       = 1;
$track->Composer      = "Acme+".time();
$track->Milliseconds  = 343719;
$track->Bytes         = 11170334;
$track->UnitPrice     = 0.99;

$query = $trackComposer
	->insert($track)
;

if ($query->execute()) {
	echo "Insert is successful";
}
else {
	echo "An error occured";
}