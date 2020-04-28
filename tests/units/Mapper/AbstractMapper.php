<?php

namespace Monolith\Casterlith\tests\units\Mapper;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Mapper/AbstractMapper.php");

use atoum;

class AbstractMapper extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');

		$this
			->object($composer->getMapper())
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Mapper\TypeMapper')
		;
	}

	/*** getTable ***/

	public function testGetTable()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$mapper = $composer->getMapper();

		$this
			->variable($mapper->getTable())
				->isIdenticalTo("types")
		;
	}

	/*** getEntity ***/

	public function testGetEntity()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$mapper = $composer->getMapper();

		$this
			->variable($mapper->getEntity())
				->isIdenticalTo('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeEntity')
		;
	}

	/*** getRelation ***/

	public function testGetRelationWithExistingName()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$mapper = $composer->getMapper();

		$this
			->object($mapper->getRelation('children'))
				->isInstanceOf('\\Monolith\\Casterlith\\Relations\\OneToMany')
		;
		$this
			->object($mapper->getRelation('parent'))
				->isInstanceOf('\\Monolith\\Casterlith\\Relations\\ManyToOne')
		;
	}

	public function testGetRelationWithoutName()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$mapper = $composer->getMapper();

		$this
			->exception(
				function() use($mapper) {
					$relation = $mapper->getRelation();
				}
			)
		;
	}

	public function testGetRelationWithNonExistingName()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$mapper = $composer->getMapper();

		$this
			->exception(
				function() use($mapper) {
					$relation = $mapper->getRelation('monkeys');
				}
			)
		;
	}

	/*** Field's types ***/

	/**
	 * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#mapping-matrix
	 */
	public function testTypesFromSelection()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$query = $composer
			->select('type')
		;
		$type = $query->first();

		$this
			->variable($type->anInteger)
				->isIdenticalTo(12)
		;
		$this
			->variable($type->aString)
				->isIdenticalTo("twelve")
		;
		$this
			->resource($type->aBlob)
		;
		$this
			->string(stream_get_contents($type->aBlob))
		;
		$this
			->variable($type->aReal)
				->isIdenticalTo(12.12)
		;
		$this
			->variable($type->aNumeric)
				->isIdenticalTo("12.12")
		;
		$this
			->dateTime($type->aDate)
				->hasDateAndTime('2012', '11', '10', '00', '00', '00')
		;
		$this
			->dateTime($type->aDateTime)
				->hasDateAndTime('2012', '11', '10', '09', '08', '07')
		;
	}

}

//	Valid

class TypeComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeMapper';
}

class TypeMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'types';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeEntity';
	protected static $fields     = array(
		'id'         => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
		'anInteger'  => array('type' => 'integer'),
		'aString'    => array('type' => 'string'),
		'aBlob'      => array('type' => 'blob'),
		'aReal'      => array('type' => 'float'),
		'aNumeric'   => array('type' => 'decimal'),
		'aDate'      => array('type' => 'date'),
		'aDateTime'  => array('type' => 'datetime'),
		'parentId'   => array('type' => 'integer'),
	);
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'id';
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'parent'    => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Mapper\TypeMapper(), 'child', 'parent', '`child`.parentId = `parent`.id', 'children'),
				'children'  => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Mapper\TypeMapper(), 'parent', 'child', '`child`.parentId = `parent`.id', 'parent'),
			);
		}

		return self::$relations;
	}
}

class TypeEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $id         = null;
	public $anInteger  = null;
	public $aString    = null;
	public $aBlob      = null;
	public $aReal      = null;
	public $aNumeric   = null;
	public $aDate      = null;
	public $aDateTime  = null;

	public $parent   = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $children = \Monolith\Casterlith\Casterlith::NOT_LOADED;
}
