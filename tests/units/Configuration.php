<?php

namespace Monolith\Casterlith\tests\units;

require_once(__DIR__."/../../vendor/autoload.php");
require_once(__DIR__ . '/../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Configuration.php');

use atoum;

class Configuration extends atoum
{
	/*** setSelectionReplacer ***/

	public function testSetSelectionReplacerByDefault()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setSelectionReplacer();

		$this
			->string($config->getSelectionReplacer())
				->isEqualTo('cl')
		;
	}

	public function testSetSelectionReplacerWithAnExpectedParameter()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setSelectionReplacer('custom_replacer');

		$this
			->string($config->getSelectionReplacer())
				->isEqualTo('custom_replacer')
		;
	}

	public function testSetSelectionReplacerWithAnIntegerParameter()
	{
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->exception(
				function() use($config) {
					$config->setSelectionReplacer(31);
				}
			)
		;
	}

	public function testSetSelectionReplacerWithAnObjectParameter()
	{
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->exception(
				function() use($config) {
					$obj = new \stdClass();
					$config->setSelectionReplacer($obj);
				}
			)
		;
	}

	/*** getSelectionReplacer ***/

	public function testGetSelectionReplacerByDefault()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$this
			->string($config->getSelectionReplacer())
				->isEqualTo('cl')
		;
	}

	/*** setFirstAutoSelection ***/

	public function testSetFirstAutoSelection()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection();

		$this
			->boolean($config->getFirstAutoSelection())
				->isEqualTo(true)
		;
	}

	/*** getFirstAutoSelection ***/

	public function testGetFirstAutoSelection()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$this
			->boolean($config->getFirstAutoSelection())
				->isEqualTo(true)
		;
	}

	/*** setExceptionMultipleResultOnFirst ***/

	public function testSetExceptionMultipleResultOnFirst()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setExceptionMultipleResultOnFirst();

		$this
			->boolean($config->getExceptionMultipleResultOnFirst())
				->isEqualTo(false)
		;
	}

	/*** getFirstAutoSelection ***/

	public function testGetExceptionMultipleResultOnFirst()
	{
		$config = new \Monolith\Casterlith\Configuration();

		$this
			->boolean($config->getExceptionMultipleResultOnFirst())
				->isEqualTo(false)
		;
	}

}
