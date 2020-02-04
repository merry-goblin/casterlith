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
$trackComposer  = $orm->getComposer('Acme\Composers\Track');              // Each table has its own composer
$qb             = $trackComposer->getDBALQueryBuilder();                  // DBAL's query builder for expressions

$tracks = $trackComposer
	->selectAsRaw("t", "count(t.TrackId) as nb")
	->first();

// To see the entire dump you can uncomment the 3 lines below
/*ini_set('xdebug.var_display_max_depth', '20');
ini_set('xdebug.var_display_max_children', '65536');
ini_set('xdebug.var_display_max_data', '1048576');*/

var_dump($tracks);