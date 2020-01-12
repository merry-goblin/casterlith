<?php

namespace Monolith\Casterlith\tests\units\Relations;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Relations/AbstractRelation.php");

use atoum;

class AbstractRelation extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistComposer');
		$mapper = $composer->getMapper();
		$relation = $mapper->getRelation('albums');

		$this
			->object($relation)
				->isInstanceOf('\\Monolith\\Casterlith\\Relations\\OneToMany')
		;

		$fromAlias   = 'artist';
		$toAlias     = 'album';
		$condition   = '`artist`.ArtistId = `album`.ArtistId';
		$reversedBy  = 'artist';
		$manualRelation = new \Monolith\Casterlith\Relations\OneToMany(
			$mapper,
			$fromAlias,
			$toAlias,
			$condition,
			$reversedBy
		);

		$this
			->object($manualRelation)
				->isInstanceOf('\\Monolith\\Casterlith\\Relations\\OneToMany')
		;

	}

	/*** getCondition ***/

	public function testGetConditionWithSameAliases()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistComposer');
		$mapper = $composer->getMapper();
		$relation = $mapper->getRelation('albums');
		$condition = $relation->getCondition('artist', 'album');

		$this
			->string($condition)
				->isEqualTo('`artist`.ArtistId = `album`.ArtistId')
		;
	}

	public function testGetConditionWithDifferentAliases()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistComposer');
		$mapper = $composer->getMapper();
		$relation = $mapper->getRelation('albums');
		$condition = $relation->getCondition('art', 'alb');

		$this
			->string($condition)
				->isEqualTo('`art`.ArtistId = `alb`.ArtistId')
		;
	}

	public function testGetConditionWithEmptyAliases()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistComposer');
		$mapper = $composer->getMapper();
		$relation = $mapper->getRelation('albums');

		$this
			->exception(
				function() use($relation) {
					$condition = $relation->getCondition('artist', '');
				}
			)
		;
		$this
			->exception(
				function() use($relation) {
					$condition = $relation->getCondition('', 'album');
				}
			)
		;
		$this
			->exception(
				function() use($relation) {
					$condition = $relation->getCondition('artist', null);
				}
			)
		;
		$this
			->exception(
				function() use($relation) {
					$condition = $relation->getCondition(null, 'album');
				}
			)
		;
	}
}

//	Mapping

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistMapper';
}

class AlbumComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Relations\\AlbumMapper';
}

class ArtistMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'artists';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Relations\\ArtistEntity';
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
				'albums'            => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Relations\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId', 'artist'),
			);
		}

		return self::$relations;
	}
}

class AlbumMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Relations\\AlbumEntity';
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
				'artist' => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Relations\ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}
}

class ArtistEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $ArtistId  = null;
	public $Name      = null;

	public $albums  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

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

	public $artist  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->AlbumId;
	}
}
