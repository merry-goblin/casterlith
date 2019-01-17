<?php

namespace Monolith\Casterlith\Schema;

use Monolith\Casterlith\Relations\RelationInterface;

class Joint
{
	public $fromAlias  = null;
	public $toAlias    = null;
	public $property   = null;
	public $relation   = null;

	/**
	 * @param string                                                          $fromAlias
	 * @param string                                                          $toAlias
	 * @param string                                                          $property
	 * @param Monolith\Casterlith\Relations\RelationInterface  $relation
	 */
	public function __construct($fromAlias, $toAlias, $property, RelationInterface $relation)
	{
		$this->fromAlias  = $fromAlias;
		$this->toAlias    = $toAlias;
		$this->property   = $property;
		$this->relation   = $relation;
	}
}