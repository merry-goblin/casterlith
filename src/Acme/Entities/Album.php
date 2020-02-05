<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Album implements EntityInterface
{
	public $AlbumId   = null;
	public $Title     = null;
	public $ArtistId  = null;

	public $tracks  = Casterlith::NOT_LOADED;
	public $artist  = Casterlith::NOT_LOADED;
}
