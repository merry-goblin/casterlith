Casterlith DataMapper ORM
========================

[Github](https://github.com/merry-goblin/casterlith)

### Purpose

The main purpose of Casterlith is to cast your database, or a part of it, into associated PHP objects.

### Description

- Standalone
- Based on [Doctrine DBAL](https://github.com/doctrine/dbal)
- Inspired by [Spot DataMapper ORM](https://github.com/spotorm/spot2)
- Converts joins into object associations
- Can map your entire database into one single array
- Relations can go in both ways
- Compatible PHP >= 5.3 
- Possible to have properties of entities != fields of tables

### Supported databases

- MySQL
- Oracle
- Microsoft SQL Server
- PostgreSQL
- SAP Sybase SQL Anywhere
- SQLite
- Drizzle

### Easy to install

- composer require merry-goblin/casterlith:"dev-master"

or

- git clone https://github.com/merry-goblin/casterlith-composer

No sample files will be included. 
See the paragraph below to test Casterlith.

### Install a standalone sample of Casterlith ready to be used

- git clone https://github.com/merry-goblin/casterlith
- composer install

Sample's entry point is "web/index.php".

### Database connection & Casterlith configuration

```
$params = array(
	'driver'    => 'pdo_sqlite',
	'path'      => __DIR__."/../config/chinook.db",
	'memory'    => false,
);
$config = new \Monolith\Casterlith\Configuration();
$casterlith = new \Monolith\Casterlith\Casterlith($params, $config);
```

### Sample (SELECT)

This is an example on how to map the [database](http://www.sqlitetutorial.net/sqlite-sample-database/) below :

[![chinook](config/sqlite-sample-database-color.jpg)](http://www.sqlitetutorial.net/sqlite-sample-database/)

**web/index.php :**
```
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

// To see the entire dump you can uncomment the 3 lines below
/*ini_set('xdebug.var_display_max_depth', '20');
ini_set('xdebug.var_display_max_children', '65536');
ini_set('xdebug.var_display_max_data', '1048576');*/

var_dump($tracks);
```

And a sample of a mapper :

**src/Acme/Mappers/Album.php :**
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
	protected static $fields     = array(
		'AlbumId'   => array('name' => 'AlbumId',  'type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Title'     => array('name' => 'Title',    'type' => 'string'),
		'ArtistId'  => array('name' => 'ArtistId', 'type' => 'integer'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'AlbumId';
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
}
```

### INSERT, UPDATE, DELETE

**web/insert.php :**
```
$artist = new \Acme\Entities\Artist();
$artist->ArtistId  = null;
$artist->Name      = "My favorite artist";

if ($artistComposer->insert($artist)) {
	echo "Insert is successful";
}
else {
	echo "An error occured";
}

echo "Artist id : ".$artist->ArtistId;
```

**web/update.php :**
```
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
```

**web/delete.php :**
```
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
```

### Available methods

#### Monolith\Casterlith\Casterlith

- **getComposer(className) :**  returns a specific composer instance
- **getDBALQueryBuilder() :**   returns a new DBAL query builder
- **getDBALConnection() :**     returns a new DBAL connection (raw sql queries with DBAL wrapping)
- **getPDOConnection() :**      returns a new PDO connection (raw sql queries)

#### Monolith\Casterlith\Composer\AbstractComposer

- **Selection :** Entities to load
	- **select(alias1, [alias2], [alias3], ...) :**                               aliases of table to cast. reset selection
	- **addSelect(alias1, [alias2], [alias3], ...) :**                            aliases of table to cast. add to current selection
	- **selectAsRaw(alias1, [sqlSelection1], [sqlSelection2], ...) :**            alias of table then raw sql selectors. reset selection
	- **addSelectAsRaw(sqlSelection1, [sqlSelection2], [sqlSelection3], ...) :**  raw sql selectors. add to current selection

- **Joints:** Relation between entities
	- **join(fromAlias, toAlias, relationName) :**       see innerJoin method
	- **innerJoin(fromAlias, toAlias, relationName) :**  applies inner join between fromAlias' table and toAlias' table with relationName's condition
	- **leftJoin(fromAlias, toAlias, relationName) :**   applies left join between fromAlias' table and toAlias' table with relationName's condition

- **Conditions:** They filter the result
	- **where(condition) :**          applies condition in query. query builder's expressions are allowed. reset selection
	- **andWhere(condition) :**       applies an and condition in query. query builder's expressions are allowed. add to current conditions
	- **orWhere(condition) :**        applies an or condition in query. query builder's expressions are allowed. add to current conditions
	- **setParameter(key, value) :**  parameters to send safely

- **Orders:** They return the rows in certain order
	- **order(sort, order) :**     order query. reset order
	- **addOrder(sort, order) :**  order query. add to current order

- **Response:** The result of the sql request returned as entity(ies)
	- **first() :**            returns one entity. it won't optimize your sql request
	- **all() :**              returns an array of entities
	- **limit(first, max) :**  returns an array of entities. be carefull! It's not a sql limit at all. It limits selection of the composer's entity in the specified range and will load any related associations according to the conditions request. to use only if needed because a second sql request is sent. 

- **Modifications** Entities to create, modify or delete
	- **insert() :**   
	- **update() :**   
	- **delete() :**   
	- **execute() :**  

- **Build a request**
	- **getDBALQueryBuilder() :**  returns the composer's DBAL query builder. Usefull to apply expressions in conditions
	- **getDBALConnection() :**    returns the composer's DBAL connection. Usefull to use raw sql queries.
	- **getPDOConnection() :**     returns a PDO connection wrapped by the composer's DBAL connection. Usefull to use raw sql queries without DBAL wrapping.
	- **getSQL() :**               returns a sql version of the current composition.
	- **expr() :**                 returns an expression builder.
	- **getMapper() :**            returns the entity mapper of the current query composer

### Joints

In a table mapper, one or several joints can be defined.
A joint is a relation between the current entity and another which can be of one of the three types below :

#### Monolith\Casterlith\Relations\OneToOne

When an entity (from) is related to one and one only entity (to) and this entity (to) is related to only one entity (from), **OneToOne** must be used.
For example : A **Person** has a joint named **passport**. It is a **OneToOne** relationship. passport will be an entity of **Passport** because a passport belongs to only one person.

#### Monolith\Casterlith\Relations\OneToMany

When an entity (from) is related to many entities (to) and this entity (to) is related to only one entity (from), **OneToMany** must be used.
For example : A **Book** has a joint named **pages**. It is a **OneToMany** relationship. pages will be an array of **Page** entities because a page belongs to only one book.

#### Monolith\Casterlith\Relations\ManyToOne

When an entity (from) is related to one and one only entity (to) and this entity (to) is related to many entities (from), **ManyToOne** must be used.
For example : A **Page** has a joint named **book**. It is a **ManyToOne** relationship. book will be an entity of **Book** because a book has got many pages.

#### What about ManyToMany relationship ?

The **ManyToMany** relationship is a magical relationship and it's behavior I want to prevent in Casterlith.
In the sample above, the playlist_track which is related to playlist and track is the result of a **ManyToMany** relationship.
To map those entities and connect them :

**Playlist** has a joint named **playlistTracks** of type **OneToMany**
**Track** has a joint named **playlistTracks** of type **OneToMany**

**PlaylistTrack** has two joints
- one joint named **playlist** of type **ManyToOne**
- one joint named **track** of type **ManyToOne**

### Unit tests

Unit tests are made with [atoum](http://atoum.org/)

./vendor/bin/atoum -d tests/units

For a specific method in a specific file:

./vendor/bin/atoum -f "tests\\units\\Composer\\AbstractComposer.php" -m "*::testGroupByWithRawSelection"

--------------------------

author : [alexandre keller](https://github.com/merry-goblin)
