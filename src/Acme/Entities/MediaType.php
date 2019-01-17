<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class MediaType implements EntityInterface
{
	public $MediaTypeId  = null;
	public $Name         = null;

	public $tracks = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->MediaTypeId;
	}
}
