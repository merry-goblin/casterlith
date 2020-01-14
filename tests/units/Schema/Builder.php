<?php

namespace Monolith\Casterlith\tests\units\Schema;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Schema/Builder.php");

use atoum;

class Builder extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder      = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$this
			->object($schemaBuilder)
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Builder')
		;
	}

	/*** select ***/

	public function testSelect()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$schemaBuilder->select("artist");

		$num = getPrivateValue($schemaBuilder, 'num');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');

		$this
			->variable($num)
				->isIdenticalTo(1)
		;
		$this
			->array($selectionList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
	}

	public function testSelectWithTwoCalls()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");

		$num = getPrivateValue($schemaBuilder, 'num');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');

		$this
			->variable($num)
				->isIdenticalTo(2)
		;
		$this
			->array($selectionList)
				->hasSize(2)
				->hasKeys(array("artist", "album"))
		;
		$this
			->object($selectionList['album'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
	}

	public function testSelectWithTwoIdenticalCalls()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$schemaBuilder->select("artist");

		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->select("artist");
				}
			)
		;
	}

	public function testSelectWithEmpty()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->select("");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->select(null);
				}
			)
		;
	}

	/*** from ***/

	public function testFrom()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(1)
		;
		$this
			->array($mapperList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(0)
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testFromWithTwoCalls()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $mapper);

		$this
			->exception(
				function() use($schemaBuilder, $mapper) {
					$schemaBuilder->from("album", $mapper);
				}
			)
		;
	}

	public function testFromWithNonExistingAliasInSelection()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $mapper);

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(1)
		;
		$this
			->array($mapperList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(1)
				->hasKey("album")
		;
		$this
			->object($selectionList['album'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(0)
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testFromWithoutSelectFirst()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->from("artist", $mapper);
		
		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(0)
		;
		$this
			->array($mapperList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(0)
		;
		$this
			->array($jointList)
				->hasSize(0)
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	/*** join ***/

	public function testJoin()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(1)
		;
		$this
			->array($mapperList)
				->hasSize(2)
				->hasKeys(array("artist","album"))
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(2)
				->hasKeys(array("artist", "album"))
		;
		$this
			->array($jointList['artist'])
				->hasSize(1)
				->hasKey("albums")
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testJoinWithoutAlias()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("", "album", "albums");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("artist", "", "albums");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("artist", "album", "s");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join(null, "album", "albums");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("artist", null, "albums");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("artist", "album", null);
				}
			)
		;
	}

	public function testJoinWithTwoCalls()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->select("albumNoRecursion");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");
		$schemaBuilder->join("artist", "albumNoRecursion", "albumsNoRecursion");

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(3)
		;
		$this
			->array($mapperList)
				->hasSize(3)
				->hasKeys(array("artist","album","albumNoRecursion"))
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(3)
				->hasKeys(array("artist","album","albumNoRecursion"))
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(2)
				->hasKeys(array("artist", "album"))
		;
		$this
			->array($jointList['artist'])
				->hasSize(2)
				->hasKeys(array("albums", "albumsNoRecursion"))
		;
		$this
			->array($jointList['album'])
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testJoinWithTwoDifferentRelationsButTheSameEntity()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");
		$schemaBuilder->join("artist", "album", "albumsNoRecursion");

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(2)
		;
		$this
			->array($mapperList)
				->hasSize(2)
				->hasKeys(array("artist","album"))
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(2)
				->hasKeys(array("artist","album"))
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(2)
				->hasKeys(array("artist", "album"))
		;
		$this
			->array($jointList['artist'])
				->hasSize(2)
				->hasKeys(array("albums", "albumsNoRecursion"))
		;
		$this
			->array($jointList['album'])
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testJoinWrongRelationName()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->exception(
				function() use($schemaBuilder) {
					$schemaBuilder->join("artist", "album", "wrongRelationName");
				}
			)
		;
	}

	public function testJoinWithTwoCallsOfTheSameRelation()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");
		$schemaBuilder->join("artist", "album", "albums");

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(1)
		;
		$this
			->array($mapperList)
				->hasSize(2)
				->hasKeys(array("artist","album"))
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(2)
				->hasKeys(array("artist", "album"))
		;
		$this
			->array($jointList['artist'])
				->hasSize(1)
				->hasKey("albums")
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testJoinWithTwoCallsOfTheSameRelationButDifferentEntities()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$queryBuilder = $orm->getQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->select("album2");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");
		$schemaBuilder->join("artist", "album2", "albums");

		$num           = getPrivateValue($schemaBuilder, 'num');
		$mapperList    = getPrivateValue($schemaBuilder, 'mapperList');
		$selectionList = getPrivateValue($schemaBuilder, 'selectionList');
		$jointList     = getPrivateValue($schemaBuilder, 'jointList');
		$rootAlias     = getPrivateValue($schemaBuilder, 'rootAlias');

		$this
			->variable($num)
				->isIdenticalTo(3)
		;
		$this
			->array($mapperList)
				->hasSize(3)
				->hasKeys(array("artist","album","album2"))
		;
		$this
			->object($mapperList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper')
		;
		$this
			->array($selectionList)
				->hasSize(3)
				->hasKeys(array("artist","album","album2"))
		;
		$this
			->object($selectionList['artist'])
				->isInstanceOf('\\Monolith\\Casterlith\\Schema\\Selection')
		;
		$this
			->array($jointList)
				->hasSize(3)
				->hasKeys(array("artist","album","album2"))
		;
		$this
			->array($jointList['artist'])
				->hasSize(1)
				->hasKey("albums")
		;
		$this
			->array($jointList['album'])
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->array($jointList['album2'])
				->hasSize(1)
				->hasKey("artist")
		;
		$this
			->variable($rootAlias)
				->isIdenticalTo("artist")
		;
	}

}

//	Mapping

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistMapper';
}

class AlbumComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumMapper';
}

class ArtistMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'artists';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'ArtistId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'ArtistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Name'      => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'albums'            => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Schema\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId', 'artist'),
				'albumsNoRecursion' => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Schema\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId'),
			);
		}

		return self::$relations;
	}
}

class AlbumMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity';
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
				'artist' => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Schema\ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}
}


class ArtistEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $ArtistId  = null;
	public $Name      = null;

	public $albums             = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $albumsNoRecursion  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->ArtistId;
	}
}

class AlbumEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $AlbumId   = null;
	public $Title     = null;
	public $ArtistId  = null;

	public $tracks  = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $artist  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->AlbumId;
	}
}
