<?php

namespace Monolith\Casterlith\tests\units;

require_once(__DIR__."/../../vendor/autoload.php");
require_once(__DIR__."/config/utils.php");
require_once(__DIR__ . '/../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Casterlith.php');

use atoum;

class Casterlith extends atoum
{
	/*** __construct ***/

	public function testContructorForSqlLite()
	{
		$params = array(
			'driver'  => 'pdo_sqlite',
			'path'    => __DIR__."/config/sqllite-unit-tests-readonly.db",
			'memory'  => false,
		);
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->object($casterlith = new \Monolith\Casterlith\Casterlith($params, $config))
				->isInstanceOf('\\Monolith\\Casterlith\\Casterlith')
		;
	}

	public function testContructorForMySQL()
	{
		$params = array(
			'driver'   => 'pdo_mysql',
			'host'     => 'localhost',
			'dbname'   => 'mysql-unit-tests-readonly',
			'user'     => 'root',
			'password' => '',
		);
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->object($casterlith = new \Monolith\Casterlith\Casterlith($params, $config))
				->isInstanceOf('\\Monolith\\Casterlith\\Casterlith')
		;
	}

	public function testGetComposer()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($artistComposer = $orm->getComposer('\\Acme\\Composers\\Artist'))
				->isInstanceOf('\\Acme\\Composers\\Artist')
		;
	}

	public function testGetComposerWithAnInvalidClass()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->exception(
				function() use($orm) {
					$orm->getComposer('\\Acme\\Entities\\Artist');
				}
			)
		;
	}

	public function testGetQueryBuilder()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($queryBuilder = $orm->getQueryBuilder())
				->isInstanceOf('\\Doctrine\\DBAL\\Query\\QueryBuilder')
		;
	}

	public function testGetDBALConnection()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($dbalConnection = $orm->getDBALConnection())
				->isInstanceOf('\\Doctrine\\DBAL\\Connection')
		;
	}

	public function testGetPDOConnection()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($pdoConnection = $orm->getPDOConnection())
				->isInstanceOf('\\PDO')
		;
	}

}
