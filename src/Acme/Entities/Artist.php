<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Artist implements EntityInterface
{
	public $ArtistId  = null;
	public $Name      = null;

	public $albums  = Casterlith::NOT_LOADED;
}
