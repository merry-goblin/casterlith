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
		cleanSqlLiteDB();

		$params = array(
			'driver'  => 'pdo_sqlite',
			'path'    => __DIR__."/config/sqlLite.db",
			'memory'  => false,
		);
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->given($casterlith = new \Monolith\Casterlith\Casterlith($params, $config))
		;
	}
}
