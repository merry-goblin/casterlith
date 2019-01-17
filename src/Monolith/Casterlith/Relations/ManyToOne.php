<?php

namespace Monolith\Casterlith\Relations;

use Monolith\Casterlith\Mapper\MapperInterface;

class ManyToOne implements RelationInterface
{
	protected $mapper;
	protected $fromAlias    = null;
	protected $toAlias      = null;
	protected $condition    = null;
	protected $reversedBy   = null;

	public function __construct(MapperInterface $mapper, $fromAlias, $toAlias, $condition, $reversedBy = null)
	{
		$this->mapper      = $mapper;
		$this->fromAlias   = $fromAlias;
		$this->toAlias     = $toAlias;
		$this->condition   = $condition;
		$this->reversedBy  = $reversedBy;
	}

	public function getCondition($fromAlias, $toAlias)
	{
		$condition = str_replace("`".$this->fromAlias."`.", "`".$fromAlias."`.", $this->condition);
		$condition = str_replace("`".$this->toAlias."`.", "`".$toAlias."`.", $condition);

		return $condition;
	}

	public function getMapper()
	{
		return $this->mapper;
	}

	public function getReversedBy()
	{
		return $this->reversedBy;
	}

	public static function getType()
	{
		return "ManyToOne";
	}
}
