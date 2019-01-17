<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Playlist implements EntityInterface
{
	public $PlaylistId  = null;
	public $Name        = null;

	public $playlistTracks  = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->PlaylistId;
	}
}
