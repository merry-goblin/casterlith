<?php

namespace Monolith\Casterlith\tests\units\Composer;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Composer/AbstractComposer.php");

use atoum;

class AbstractComposer extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$params = array(
			'driver'  => 'pdo_sqlite',
			'path'    => __DIR__."/config/sqllite-unit-tests-readonly.db",
			'memory'  => false,
		);
		$config = new \Monolith\Casterlith\Configuration();
		$orm = new \Monolith\Casterlith\Casterlith($params, $config);

		$this
			->object($composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer'))
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\ArtistComposer')
		;
	}

	public function testContructorWithoutComposerInterface()
	{
		$params = array(
			'driver'  => 'pdo_sqlite',
			'path'    => __DIR__."/config/sqllite-unit-tests-readonly.db",
			'memory'  => false,
		);
		$config = new \Monolith\Casterlith\Configuration();
		$orm = new \Monolith\Casterlith\Casterlith($params, $config);

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutComposerInterface');
				}
			)
		;
	}

	public function testContructorWithoutAValidMapper()
	{
		$params = array(
			'driver'  => 'pdo_sqlite',
			'path'    => __DIR__."/config/sqllite-unit-tests-readonly.db",
			'memory'  => false,
		);
		$config = new \Monolith\Casterlith\Configuration();
		$orm = new \Monolith\Casterlith\Casterlith($params, $config);

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutAValidMapper');
				}
			)
		;
	}
}

//	Valid

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapper';
}

class ArtistMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = null;
	protected static $entity     = null;
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return null;
	}

	public static function getFields()
	{
		return null;
	}

	public static function getRelations()
	{
		return null;
	}
}

//	Invalid

class ArtistComposerWithoutComposerInterface extends \Monolith\Casterlith\Composer\AbstractComposer
{
}

class ArtistComposerWithoutAValidMapper extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapperWithoutMapperInterface';
}

class ArtistMapperWithoutMapperInterface extends \Monolith\Casterlith\Mapper\AbstractMapper
{
}
