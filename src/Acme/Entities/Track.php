<?php

namespace Acme\Entities;

use Monolith\Casterlith\Casterlith;
use Monolith\Casterlith\Entity\EntityInterface;

class Track implements EntityInterface
{
	public $TrackId       = null;
	public $Name          = null;
	public $AlbumId       = null;
	public $MediaTypeId   = null;
	public $GenreId       = null;
	public $Composer      = null;
	public $Milliseconds  = null;
	public $Bytes         = null;
	public $UnitPrice     = null;

	public $album           = Casterlith::NOT_LOADED;
	public $invoiceItems    = Casterlith::NOT_LOADED;
	public $genre           = Casterlith::NOT_LOADED;
	public $mediaType       = Casterlith::NOT_LOADED;
	public $playlistTracks  = Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->TrackId;
	}
}
