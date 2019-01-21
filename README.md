Casterlith DataMapper ORM
========================

[Github](https://github.com/merry-goblin/casterlith)

### Purpose

The main purpose of Casterlith is to cast your database, or a part of it, into associated PHP objects.

### Description

- Standalone
- Based on [Doctrine DBAL](https://github.com/doctrine/dbal)
- Inspired by [Sport DataMapper ORM](https://github.com/spotorm/spot2)
- Converts joins into object associations
- Can map your entire database into one single array
- Relations can go in both ways

### Supported databases

- MySQL
- Oracle
- Microsoft SQL Server
- PostgreSQL
- SAP Sybase SQL Anywhere
- SQLite
- Drizzle

### INSERT, UPDATE, DELETE

DBAL will do the job just fine

### Sample

This is an example on how map the [database](http://www.sqlitetutorial.net/sqlite-sample-database/) below :

[![chinook](config/sqlite-sample-database-color.jpg)](http://www.sqlitetutorial.net/sqlite-sample-database/)

```
<?php

require_once(__DIR__."/../vendor/autoload.php");

$params = array(
	'driver'    => 'pdo_sqlite',
	'path'      => __DIR__."/../config/chinook.db",
	'memory'    => false,
);
$config = new \Monolith\Casterlith\Configuration();
$config->setSelectionReplacer("_cl");

$orm = new \Monolith\Casterlith\Casterlith($params, $config);
$trackComposer = $orm->getComposer('Acme\Composers\Track');

$trackComposer
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
	->where("t.TrackId = 3247");

$tracks = $trackComposer->all();

var_dump($tracks);

```

And a sample of a mapper :

```
<?php

namespace Acme\Mappers;

use Monolith\Casterlith\Entity\EntityInterface;
use Monolith\Casterlith\Mapper\AbstractMapper;
use Monolith\Casterlith\Mapper\MapperInterface;
use Monolith\Casterlith\Relations\OneToMany;
use Monolith\Casterlith\Relations\ManyToOne;

use Acme\Mappers\Track as TrackMapper;
use Acme\Mappers\Artist as ArtistMapper;

class Album extends AbstractMapper implements MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = 'Acme\Entities\Album';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'AlbumId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'AlbumId'   => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Title'     => array('type' => 'string'),
				'ArtistId'  => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'tracks' => new OneToMany(new TrackMapper(), 'album', 'track', '`album`.AlbumId = `track`.AlbumId', 'album'),
				'artist' => new ManyToOne(new ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}

	/**
	 * @param  string  $relName
	 * @return Merry\Core\Services\Orm\Casterlith\Relations\RelationInterface
	 */
	public static function getRelation($relName)
	{
		if (is_null(self::$relations)) {
			self::getRelations();
		}

		return self::$relations[$relName];
	}
}
```

### Methods available

#### Monolith\Casterlith\Casterlith

- **getComposer(className) :**                       returns a specific composer instance
- **getQueryBuilder() :**                            returns a new DBAL query builder

#### Monolith\Casterlith\Composer\AbstractComposer

- **select(alias1, [alias2], [alias3], ...) :**      aliases of table to cast. reset selection
- **addSelect(alias1, [alias2], [alias3], ...) :**   aliases of table to cast. add to current selection
- **join(fromAlias, toAlias, relationName) :**       see innerJoin method
- **innerJoin(fromAlias, toAlias, relationName) :**  apply inner join between fromAlias' table and toAlias' table with relationName's condition
- **leftJoin(fromAlias, toAlias, relationName) :**   apply left join between fromAlias' table and toAlias' table with relationName's condition
- **where(condition) :**                             apply condition in query. to apply an or condition expressions of DBAL query builder must be used. reset selection
- **andWhere(condition) :**                          apply condition in query. to apply an or condition expressions of DBAL query builder must be used. add to current conditions
- **setParameter(key, value) :**                     parameters to send safely
- **order(sort, order) :**                           order query. reset order
- **addOrder(sort, order) :**                        order query. add to current order
- **limit(first, max) :**                            be carefull! It's not a sql limit at all. It limits selection of the composer's entity in the specified range and will load any related associations according the conditions request. to use only if needed because a second sql request is sent 
- **first() :**                                      returns one entity. it won't optimize your sql request
- **all() :**                                        returns an array of entities

--------------------------

[author](https://github.com/merry-goblin)
