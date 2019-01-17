<?php

namespace Acme\Composers;

use Monolith\Casterlith\Entities\Track as TrackEntity;

use Monolith\Casterlith\Composer\ComposerInterface;
use Monolith\Casterlith\Composer\AbstractComposer;

class Track extends AbstractComposer implements ComposerInterface
{
	protected static $mapperName  = 'Acme\\Mappers\\Track';
}
