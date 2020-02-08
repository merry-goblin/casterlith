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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder      = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
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

	/*** getAUniqueSelection ***/

	public function testGetAUniqueSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->string($selection = $schemaBuilder->getAUniqueSelection("artist"))
				->isEqualTo("artist.ArtistId as artistcl1_ArtistId,artist.Name as artistcl1_Name")
		;
	}

	public function testGetAUniqueSelectionWithAnEmptyReplacer()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->string($selection = $schemaBuilder->getAUniqueSelection("artist"))
				->isEqualTo("artist.ArtistId as artist1_ArtistId,artist.Name as artist1_Name")
		;
	}

	public function testGetAUniqueSelectionWithDifferentSelectAndFromAliases()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $mapper);

		$this
			->exception(
				function() use($schemaBuilder) {
					$selection = $schemaBuilder->getAUniqueSelection("artist");
				}
			)
		;
	}

	public function testGetAUniqueSelectionWithoutCallingFrom()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");

		$this
			->exception(
				function() use($schemaBuilder) {
					$selection = $schemaBuilder->getAUniqueSelection("artist");
				}
			)
		;
	}

	public function testGetAUniqueSelectionWithAnEmptyAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->exception(
				function() use($schemaBuilder) {
					$selection = $schemaBuilder->getAUniqueSelection("");
				}
			)
		;
		$this
			->exception(
				function() use($schemaBuilder) {
					$selection = $schemaBuilder->getAUniqueSelection(null);
				}
			)
		;
	}

	public function testGetAUniqueSelectionWithJoin()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $mapper);
		$schemaBuilder->join("artist", "album", "albums");

		$this
			->string($selection = $schemaBuilder->getAUniqueSelection("artist"))
				->isEqualTo("artist.ArtistId as artistcl1_ArtistId,artist.Name as artistcl1_Name")
		;
		$this
			->string($selection = $schemaBuilder->getAUniqueSelection("album"))
				->isEqualTo("album.AlbumId as albumcl2_AlbumId,album.Title as albumcl2_Title,album.ArtistId as albumcl2_ArtistId")
		;
	}

	/*** getAUniqueSelectionFromRaw ***/

	public function testGetAUniqueSelectionFromRaw()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);

		$this
			->string($selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(artist.ArtistId) as nb"))
				->isEqualTo("count(`artist`.`ArtistId`) as nb")
		;
	}

	/*** buildFirst ***/

	public function testBuildFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$selection = $schemaBuilder->getAUniqueSelection("artist");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($mapper->getTable(), "artist");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);
		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildFirstWithJoin()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$selection = $schemaBuilder->getAUniqueSelection("artist");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);
		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildFirstWithTwoEntities()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$albumSelection  = $schemaBuilder->getAUniqueSelection("album");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($albumSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);
		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(2)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildFirstWithGetRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(artist.ArtistId) as nb");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($mapper->getTable(), "artist");
		$statement = $queryBuilder->execute();

		$this
			->when(
				function() use ($schemaBuilder, $statement) {
					$result = $schemaBuilder->buildFirst($statement);
				}
			)->error
				->exists()
        ;
	}

	public function testBuildFirstWithNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$albumSelection  = $schemaBuilder->getAUniqueSelection("album");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($albumSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);
		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(2)
		;
		$this
			->object($artist->albumsNoRecursion[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[1]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildFirstWithBothRecursionAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$schemaBuilder->join("artist", "album", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$albumSelection  = $schemaBuilder->getAUniqueSelection("album");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($albumSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);

		$this
			->exception(
				function() use($queryBuilder) {
					$statement = $queryBuilder->execute();
				}
			)
		;
	}

	public function testBuildFirstWithBothRecursionAndNoRecursionAndDifferentAliasFromSameTable()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album1");
		$schemaBuilder->select("album2");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album1", "albums");
		$schemaBuilder->join("artist", "album2", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$album1Selection  = $schemaBuilder->getAUniqueSelection("album1");
		$album2Selection  = $schemaBuilder->getAUniqueSelection("album2");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($album1Selection);
		$queryBuilder->addSelect($album2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album1", "`artist`.ArtistId = `album1`.ArtistId");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album2", "`artist`.ArtistId = `album2`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->andWhere("album1.AlbumId = :albumId1");
		$queryBuilder->andWhere("album2.AlbumId = :albumId2");
		$queryBuilder->setParameter("artistId", 1);
		$queryBuilder->setParameter("albumId1", 1);
		$queryBuilder->setParameter("albumId2", 4);

		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(1)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albumsNoRecursion[4])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[4]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildFirstWithBothRecursionAndNoRecursionAndDifferentAliasFromSameTableAndSameData()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album1");
		$schemaBuilder->select("album2");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album1", "albums");
		$schemaBuilder->join("artist", "album2", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$album1Selection  = $schemaBuilder->getAUniqueSelection("album1");
		$album2Selection  = $schemaBuilder->getAUniqueSelection("album2");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($album1Selection);
		$queryBuilder->addSelect($album2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album1", "`artist`.ArtistId = `album1`.ArtistId");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album2", "`artist`.ArtistId = `album2`.ArtistId");
		$queryBuilder->where("artist.ArtistId = :artistId");
		$queryBuilder->setParameter("artistId", 1);

		$statement = $queryBuilder->execute();

		$artist = $schemaBuilder->buildFirst($statement);
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(2)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(2)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albumsNoRecursion[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[1]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	/*** buildAll ***/

	public function testBuildAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$selection = $schemaBuilder->getAUniqueSelection("artist");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($mapper->getTable(), "artist");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->setParameter("artistId", 10);
		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildAllWithJoin()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$selection = $schemaBuilder->getAUniqueSelection("artist");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->setParameter("artistId", 10);
		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildAllWithTwoEntities()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$albumSelection  = $schemaBuilder->getAUniqueSelection("album");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($albumSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->setParameter("artistId", 10);
		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(2)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildAllWithGetRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$mapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $mapper);
		$selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(artist.ArtistId) as nb");

		//	DBal
		$queryBuilder->select($selection);
		$queryBuilder->from($mapper->getTable(), "artist");
		$statement = $queryBuilder->execute();

		$this
			->when(
				function() use ($schemaBuilder, $statement) {
					$result = $schemaBuilder->buildAll($statement);
				}
			)->error
				->exists()
        ;
	}

	public function testBuildAllWithNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$albumSelection  = $schemaBuilder->getAUniqueSelection("album");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($albumSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->setParameter("artistId", 10);
		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(2)
		;
		$this
			->object($artist->albumsNoRecursion[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[1]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildAllWithBothRecursionAndNoRecursionAndDifferentAliasFromSameTable()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album1");
		$schemaBuilder->select("album2");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album1", "albums");
		$schemaBuilder->join("artist", "album2", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$album1Selection  = $schemaBuilder->getAUniqueSelection("album1");
		$album2Selection  = $schemaBuilder->getAUniqueSelection("album2");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($album1Selection);
		$queryBuilder->addSelect($album2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album1", "`artist`.ArtistId = `album1`.ArtistId");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album2", "`artist`.ArtistId = `album2`.ArtistId");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->andWhere("album1.AlbumId = :albumId1");
		$queryBuilder->andWhere("album2.AlbumId = :albumId2");
		$queryBuilder->setParameter("artistId", 10);
		$queryBuilder->setParameter("albumId1", 1);
		$queryBuilder->setParameter("albumId2", 4);

		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(1)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(1)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albumsNoRecursion[4])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[4]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testBuildAllWithBothRecursionAndNoRecursionAndDifferentAliasFromSameTableAndSameData()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper  = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->select("album1");
		$schemaBuilder->select("album2");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album1", "albums");
		$schemaBuilder->join("artist", "album2", "albumsNoRecursion");
		$artistSelection = $schemaBuilder->getAUniqueSelection("artist");
		$album1Selection  = $schemaBuilder->getAUniqueSelection("album1");
		$album2Selection  = $schemaBuilder->getAUniqueSelection("album2");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->addSelect($album1Selection);
		$queryBuilder->addSelect($album2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album1", "`artist`.ArtistId = `album1`.ArtistId");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album2", "`artist`.ArtistId = `album2`.ArtistId");
		$queryBuilder->where("artist.ArtistId <= :artistId");
		$queryBuilder->setParameter("artistId", 10);

		$statement = $queryBuilder->execute();

		$artists = $schemaBuilder->buildAll($statement);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->ArtistId)
				->isIdenticalTo(1)
		;
		$this
			->variable($artist->Name)
				->isIdenticalTo("AC/DC")
		;
		$this
			->array($artist->albums)
				->hasSize(2)
		;
		$this
			->array($artist->albumsNoRecursion)
				->hasSize(2)
		;
		$this
			->object($artist->albums[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albumsNoRecursion[1])
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\AlbumEntity')
		;
		$this
			->object($artist->albums[1]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Schema\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[1]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	/*** buildFirstAsRaw ***/

	public function testBuildFirstAsRaw()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$artistSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");

		$statement = $queryBuilder->execute();

		$result = $schemaBuilder->buildFirstAsRaw($statement);
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb)
				->isIdenticalTo("275")
		;
	}

	public function testBuildFirstAsRawWithJoin()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$artistSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");

		$statement = $queryBuilder->execute();

		$result = $schemaBuilder->buildFirstAsRaw($statement);
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb)
				->isIdenticalTo("204")
		;
	}

	public function testBuildFirstAsRawWithTwoSelections()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$nb1Selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb1");
		$nb2Selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb2");

		//	DBal
		$queryBuilder->select($nb1Selection);
		$queryBuilder->addSelect($nb2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");

		$statement = $queryBuilder->execute();

		$result = $schemaBuilder->buildFirstAsRaw($statement);
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb1)
				->isIdenticalTo("275")
		;
		$this
			->variable($result->nb2)
				->isIdenticalTo("275")
		;
	}

	public function testBuildFirstAsRawWithTwoSelectionsInASingleSelect()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$albumMapper = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("album");
		$schemaBuilder->from("album", $albumMapper);
		$nbSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(album.ArtistId)) as nb1, count(distinct(album.AlbumId)) as nb2");

		//	DBal
		$queryBuilder->select($nbSelection);
		$queryBuilder->from($albumMapper->getTable(), "album");

		$statement = $queryBuilder->execute();

		$result = $schemaBuilder->buildFirstAsRaw($statement);
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb1)
				->isIdenticalTo("204")
		;
		$this
			->variable($result->nb2)
				->isIdenticalTo("347")
		;
	}

	/*** buildAllAsRaw ***/

	public function testBuildAllAsRaw()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$artistSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");

		$statement = $queryBuilder->execute();

		$results = $schemaBuilder->buildAllAsRaw($statement);
		$result =  reset($results);
		$this
			->array($results)
				->hasSize(1)
		;
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb)
				->isIdenticalTo("275")
		;
	}

	public function testBuildAllAsRawWithJoin()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();
		$albumMapper = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$schemaBuilder->join("artist", "album", "albums");
		$artistSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb");

		//	DBal
		$queryBuilder->select($artistSelection);
		$queryBuilder->from($artistMapper->getTable(), "artist");
		$queryBuilder->innerJoin("artist", $albumMapper->getTable(), "album", "`artist`.ArtistId = `album`.ArtistId");

		$statement = $queryBuilder->execute();

		$results = $schemaBuilder->buildAllAsRaw($statement);
		$result =  reset($results);
		$this
			->array($results)
				->hasSize(1)
		;
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb)
				->isIdenticalTo("204")
		;
	}

	public function testBuildAllAsRawWithTwoSelections()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$nb1Selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb1");
		$nb2Selection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(artist.ArtistId)) as nb2");

		//	DBal
		$queryBuilder->select($nb1Selection);
		$queryBuilder->addSelect($nb2Selection);
		$queryBuilder->from($artistMapper->getTable(), "artist");

		$statement = $queryBuilder->execute();

		$results = $schemaBuilder->buildAllAsRaw($statement);
		$result =  reset($results);
		$this
			->array($results)
				->hasSize(1)
		;
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb1)
				->isIdenticalTo("275")
		;
		$this
			->variable($result->nb2)
				->isIdenticalTo("275")
		;
	}

	public function testBuildAllAsRawWithTwoSelectionsInASingleSelect()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$albumMapper = new \Monolith\Casterlith\tests\units\Schema\AlbumMapper();

		$schemaBuilder->select("album");
		$schemaBuilder->from("album", $albumMapper);
		$nbSelection = $schemaBuilder->getAUniqueSelectionFromRaw("count(distinct(album.ArtistId)) as nb1, count(distinct(album.AlbumId)) as nb2");

		//	DBal
		$queryBuilder->select($nbSelection);
		$queryBuilder->from($albumMapper->getTable(), "album");

		$statement = $queryBuilder->execute();

		$results = $schemaBuilder->buildAllAsRaw($statement);
		$result =  reset($results);
		$this
			->array($results)
				->hasSize(1)
		;
		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->variable($result->nb1)
				->isIdenticalTo("204")
		;
		$this
			->variable($result->nb2)
				->isIdenticalTo("347")
		;
	}

	/*** getRootAlias ***/

	public function testGetRootAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);
		$artistMapper = new \Monolith\Casterlith\tests\units\Schema\ArtistMapper();

		$schemaBuilder->select("artist");
		$schemaBuilder->from("artist", $artistMapper);
		$rootAlias = $schemaBuilder->getRootAlias();

		$this
			->string($rootAlias)
				->isIdenticalTo("artist")
		;
	}

	public function testGetRootAliasWithoutCallingFromFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$queryBuilder = $orm->getDBALQueryBuilder();
		$selectionReplacer = "cl";
		$schemaBuilder = new \Monolith\Casterlith\Schema\Builder($queryBuilder, $selectionReplacer);

		$schemaBuilder->select("artist");
		$rootAlias = $schemaBuilder->getRootAlias();

		$this
			->variable($rootAlias)
				->isNull()
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
	protected static $fields     = array(
		'ArtistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Name'      => array('type' => 'string'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'ArtistId';
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
	protected static $fields     = array(
		'AlbumId'   => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'Title'     => array('type' => 'string'),
		'ArtistId'  => array('type' => 'integer'),
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
}

class AlbumEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $AlbumId   = null;
	public $Title     = null;
	public $ArtistId  = null;

	public $tracks  = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $artist  = \Monolith\Casterlith\Casterlith::NOT_LOADED;
}
