<?php

namespace Monolith\Casterlith\tests\units\Mapper;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Mapper/AbstractMapper.php");

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

	/**
	 * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#mapping-matrix
	 */
	public function testTypesFromSelection()
	{
		$orm = getAReadOnlyOrmInstance("types");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Mapper\\TypeComposer');
		$query = $composer
			->select('t')
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
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'id';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'id'         => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'anInteger'  => array('type' => 'integer'),
				'aString'    => array('type' => 'string'),
				'aBlob'      => array('type' => 'blob'),
				'aReal'      => array('type' => 'float'),
				'aNumeric'   => array('type' => 'decimal'),
				'aDate'      => array('type' => 'date'),
				'aDateTime'  => array('type' => 'datetime'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array();
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

	public function getPrimaryValue()
	{
		return $this->id;
	}
}
