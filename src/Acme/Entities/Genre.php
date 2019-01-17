<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Genre implements EntityInterface
{
	public $GenreId       = null;
	public $Name          = null;

	public $tracks = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->GenreId;
	}
}
