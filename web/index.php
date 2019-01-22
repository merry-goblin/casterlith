<?php

require_once(__DIR__."/../vendor/autoload.php");

$params = array(
	'driver'    => 'pdo_sqlite',
	'path'      => __DIR__."/../config/chinook.db",
	'memory'    => false,
);
$config = new \Monolith\Casterlith\Configuration();
$config->setSelectionReplacer("_cl");

$orm            = new \Monolith\Casterlith\Casterlith($params, $config);
$trackComposer  = $orm->getComposer('Acme\Composers\Track');
$qb             = $trackComposer->getQueryBuilder();

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
	->where($qb->expr()->andX(
		$qb->expr()->like('t.Name', ':trackName'),
		$qb->expr()->eq('art.Name', ':artistName')
	))
	->setParameter('trackName', "%Princess%")
	->setParameter('artistName', "Accept")
	->all();

var_dump($tracks);