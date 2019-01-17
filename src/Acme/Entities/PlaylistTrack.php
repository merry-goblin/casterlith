<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class PlaylistTrack implements EntityInterface
{
	public $PlaylistTrackId  = null;
	public $PlaylistId       = null;
	public $TrackId          = null;

	public $playlist  = Casterlith::NOT_LOADED;
	public $track     = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->PlaylistTrackId;
	}
}
