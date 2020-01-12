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

	/*** select ***/

}