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
$trackComposer  = $orm->getComposer('Acme\Composers\Track');              // Each table has its own query composer

$tracks = $trackComposer
	->select("t", "alb", "it", "g", "m", "pt", "p", "art", "inv", "c", "sub", "sup")
	->join("t", "alb", "album")
	->join("t", "it", "invoiceItems")
	->join("t", "g", "genre")
	->join("t", "m", "mediaType")
	->join("t", "pt", "playlistTracks")
	->join("pt", "p", "playlist")
	->join("alb", "art", "artist")
	->join("it", "inv", "invoice")
	->join("inv", "c", "customer")
	->join("c", "sub", "employee")
	->join("sub", "sup", "reportsTo")
	->where($trackComposer->expr()->andX(
		$trackComposer->expr()->like('t.Name', ':trackName'),
		$trackComposer->expr()->eq('art.Name', ':artistName')
	))
	->setParameter('trackName', "%Princess%")
	->setParameter('artistName', "Accept")
	->all();

// To see the entire dump you can uncomment the 3 lines below
/*ini_set('xdebug.var_display_max_depth', '20');
ini_set('xdebug.var_display_max_children', '65536');
ini_set('xdebug.var_display_max_data', '1048576');*/

var_dump($tracks);