<?php

namespace Monolith\Casterlith\Relations;

interface RelationInterface
{
	public function getCondition($fromAlias, $toAlias);
	public function getMapper();
}
