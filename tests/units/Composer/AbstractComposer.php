<?php

namespace Monolith\Casterlith\tests\units\Composer;

require_once(__DIR__."/../../../vendor/autoload.php");
require_once(__DIR__."/../config/utils.php");
require_once(__DIR__ ."/../../../vendor/merry-goblin/casterlith/Monolith/Casterlith/Composer/AbstractComposer.php");

use atoum;

class AbstractComposer extends atoum
{
	/*** __construct ***/

	public function testContructor()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->object($composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer'))
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\ArtistComposer')
		;
	}

	public function testContructorWithoutComposerInterface()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutComposerInterface');
				}
			)
		;
	}

	public function testContructorWithoutAValidMapper()
	{
		$orm = getAReadOnlyOrmInstance();

		$this
			->exception(
				function() use($orm) {
					$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposerWithoutAValidMapper');
				}
			)
		;
	}

	/*** select ***/

	public function testSelectWithOneEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
		;
		$art = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.ArtistId IN (1)")
		;
		$this
			->object($art)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->string($art->Name)
				->isEqualTo("AC/DC")
		;
	}

	public function testSelectWithTwoEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE art.ArtistId IN (1)")
		;
	}

	public function testSelectWithoutEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$query = $composer->select();
				}
			)
		;
	}

	public function testSelectWithAnInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$query = $composer->select(12);
				}
			)
		;
	}

	public function testSelectWithOneEntityAliasAndACustomReplacer()
	{
		$replacer = "_cr";
		$config = new \Monolith\Casterlith\Configuration($replacer);
		$config->setSelectionReplacer($replacer);

		$orm = getAReadOnlyOrmInstance("unit-tests", $config);
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
		;

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as art_cr1_ArtistId,art.Name as art_cr1_Name FROM artists art")
		;
	}

	public function testSelectWithEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');

		$this
			->exception(
				function() use($composer) {
					$composer->select('');
				}
			)
		;
	}

	public function testSelectWithResetOfSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->select('art')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.ArtistId IN (1)")
		;
	}

	public function testTwoSelectWithTheSameQuery()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId = 1')
		;
		$artist1 = $query->first();
		$query
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId = 2')
		;
		$artist2 = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE (art.ArtistId = 2) AND (art.ArtistId IN (2))")
		;
	}

	/*** addSelect ***/

	public function testAddSelectWithEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->addSelect('alb')
		;

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art")
		;
	}

	public function testAddSelectWithoutEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
		;

		$this
			->exception(
				function() use($query) {
					$query->addSelect();
				}
			)
		;
	}

	public function testAddSelectWithAnInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->
			select('art')
		;

		$this
			->exception(
				function() use($query) {
					$query->addSelect(12);
				}
			)
		;
	}

	public function testAddSelectWithExistingEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
		;

		$this
			->exception(
				function() use($query) {
					$query->addSelect('art');
				}
			)
		;
	}

	public function testAddSelectWithEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
		;

		$this
			->exception(
				function() use($query) {
					$query->addSelect('');
				}
			)
		;
	}

	public function testAddSelectWithResetOfSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->addSelect('alb')
			->join('art', 'alb', 'albums')
			->select('art')
			->addSelect('alb')
			->join('art', 'alb', 'albums')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE art.ArtistId IN (1)")
		;
	}

	/*** selectAsRaw ***/

	public function testSelectAsRawWithOnlyAnEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art')
		;

		$this
			->exception(
				function() use($query) {
					$query->first();
				}
			)
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndACounter()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art")
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndTwoSelectors()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testSelectAsRawWithAnEntityAliasAndOneSelectorOfTwoCounters()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testSelectAsRawWithResetOfSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb')
			->join('art', 'alb', 'albums')
			->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb')
			->join('art', 'alb', 'albums')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId")
		;
	}

	/*** addSelectAsRaw ***/

	public function testAddSelectAsRawWithACounter()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art')
			->addSelectAsRaw('count(distinct(art.ArtistId)) as nb')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art")
		;
	}

	public function testAddSelectAsRawWithAnEntityAliasAndTwoSelectors()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art')
			->addSelectAsRaw('count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testAddSelectAsRawWithAnEntityAliasAndOneSelectorOfTwoCounters()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art')
			->addSelectAsRaw('count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2 FROM artists art")
		;
	}

	public function testAddSelectAsRawWithResetOfSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art')
			->addSelectAsRaw('count(distinct(art.ArtistId)) as nb')
			->join('art', 'alb', 'albums')
			->selectAsRaw('art')
			->addSelectAsRaw('count(distinct(art.ArtistId)) as nb')
			->join('art', 'alb', 'albums')
		;
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT count(distinct(art.ArtistId)) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId")
		;
	}

	/*** join | innerJoin ***/

	public function testJoinWithOneArtist()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(2)
			 	->hasKeys(array(1, 4))
		;
		$this
			->string($artist->albums[4]->Title)
				->isEqualTo("Let There Be Rock")
		;
	}

	public function testJoinWithAllArtists()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
		;
		$artists = $query->all();
		$artist = $artists[227];

		$artist->albumsNoRecursion[293];

		$this
			->array($artists)
				->hasSize(204)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albums[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albums[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->string($artist->albums[293]->artist->Name)
				->isEqualTo("Luciano Pavarotti")
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testJoinWithAllArtistsAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albumsNoRecursion')
		;
		$artists = $query->all();
		$artist = $artists[227];

		$artist->albumsNoRecursion[293];

		$this
			->array($artists)
				->hasSize(204)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albumsNoRecursion)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albumsNoRecursion[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albumsNoRecursion[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->variable($artist->albumsNoRecursion[293]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testJoinWithTheSameEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->join('art', 'art', 'albums');
				}
			)
		;
	}

	public function testJoinWithAnEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->join('art', '', 'albums');
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->join('', 'alb', 'albums');
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->join('art', 'alb', '');
				}
			)
		;
	}

	public function testJoinWithWrongNames()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->join('artz', 'alb', 'albums');
					$query->first();
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->join('art', 'albz', 'albums');
					$query->first();
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->join('art', 'alb', 'albumz');
					$query->first();
				}
			)
		;
	}

	public function testJoinWithAllArtistsAndBothRecursionAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb', 'alb2')
			->join('art', 'alb', 'albums')
			->join('art', 'alb2', 'albumsNoRecursion')
		;
		$artists = $query->all();
		$artist  = $artists[227];

		$artist->albumsNoRecursion[293];

		$this
			->array($artists)
				->hasSize(204)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->array($artist->albumsNoRecursion)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albums[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->string($artist->albumsNoRecursion[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albums[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->integer($artist->albumsNoRecursion[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->object($artist->albums[293]->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->variable($artist->albumsNoRecursion[293]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	/*** leftJoin ***/

	public function testLeftJoinWithOneArtist()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->leftJoin('art', 'alb', 'albums')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(2)
			 	->hasKeys(array(1, 4))
		;
		$this
			->string($artist->albums[4]->Title)
				->isEqualTo("Let There Be Rock")
		;
	}

	public function testLeftJoinWithAllArtists()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->leftJoin('art', 'alb', 'albums')
		;
		$artists = $query->all();
		$artist = $artists[227];

		$artist->albumsNoRecursion[293];

		$this
			->array($artists)
				->hasSize(275)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albums[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albums[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->string($artist->albums[293]->artist->Name)
				->isEqualTo("Luciano Pavarotti")
		;
		$this
			->variable($artist->albumsNoRecursion)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testLeftJoinWithAllArtistsAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->leftJoin('art', 'alb', 'albumsNoRecursion')
		;
		$artists = $query->all();
		$artist = $artists[227];

		$artist->albumsNoRecursion[293];

		$this
			->array($artists)
				->hasSize(275)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albumsNoRecursion)
			 	->hasSize(1)
			 	->hasKeys(array(293))
		;
		$this
			->string($artist->albumsNoRecursion[293]->Title)
				->isEqualTo("Pavarotti's Opera Made Easy")
		;
		$this
			->integer($artist->albumsNoRecursion[293]->AlbumId)
				->isEqualTo(293)
		;
		$this
			->variable($artist->albumsNoRecursion[293]->artist)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
		$this
			->variable($artist->albums)
				->isIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testLeftJoinWithTheSameEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->leftJoin('art', 'art', 'albums');
				}
			)
		;
	}

	public function testLeftJoinWithAnEmptyEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->leftJoin('art', '', 'albums');
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->leftJoin('', 'alb', 'albums');
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->leftJoin('art', 'alb', '');
				}
			)
		;
	}

	public function testLeftJoinWithWrongNames()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
		;

		$this
			->exception(
				function() use($query) {
					$query->leftJoin('artz', 'alb', 'albums');
					$query->first();
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->leftJoin('art', 'albz', 'albums');
					$query->first();
				}
			)
		;
		$this
			->exception(
				function() use($query) {
					$query->leftJoin('art', 'alb', 'albumz');
					$query->first();
				}
			)
		;
	}

	public function testLeftJoinWithOneArtistWithoutAlbum()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->leftJoin('art', 'alb', 'albums')
			->where('art.ArtistId = :artistId')
			->setParameter('artistId', 26)
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($artist->albums)
			 	->hasSize(0)
		;
		$this
			->variable($artist->albums)
			 	->isNotIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	public function testLeftJoinWithOneEmployeeWithoutBoss()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\EmployeeComposer');
		$query = $composer
			->select('sub', 'sup')
			->leftJoin('sub', 'sup', 'reportsTo')
			->where('sub.EmployeeId = :employeeId')
			->setParameter('employeeId', 1)
		;
		$employee = $query->first();

		$this
			->object($employee)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\employeeEntity')
		;
		$this
			->variable($employee->reportsTo)
			 	->isNull()
		;
		$this
			->variable($employee->reportsTo)
			 	->isNotIdenticalTo(\Monolith\Casterlith\Casterlith::NOT_LOADED)
		;
	}

	/*** where ***/

	public function testWhereAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.Name = \"Green Day\"")
		;
	}

	public function testWhereFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = \"Green Day\") AND (art.ArtistId IN (54))")
		;
	}

	public function testWhereWithApostrophe()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = \'Green Day\'')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = 'Green Day') AND (art.ArtistId IN (54))")
		;
	}

	public function testWhereWithGraveAccent()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('`art`.`Name` = "Green Day"')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (`art`.`Name` = \"Green Day\") AND (art.ArtistId IN (54))")
		;
	}

	public function testWhereWithInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.ArtistId = 54')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.ArtistId = 54) AND (art.ArtistId IN (54))")
		;
	}

	public function testWhereWithDateTime()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceComposer');
		$query = $composer->select('inv');
		$dt = new \DateTime("2013-12-04 00:00:00");
		$query->where('inv.InvoiceDate = "'.$dt->format("Y-m-d H:i:s").'"');
		$invoice = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT inv.InvoiceId as invcl1_InvoiceId,inv.CustomerId as invcl1_CustomerId,inv.InvoiceDate as invcl1_InvoiceDate,inv.BillingAddress as invcl1_BillingAddress,inv.BillingCity as invcl1_BillingCity,inv.BillingState as invcl1_BillingState,inv.BillingCountry as invcl1_BillingCountry,inv.BillingPostalCode as invcl1_BillingPostalCode,inv.Total as invcl1_Total FROM invoices inv WHERE (inv.InvoiceDate = \"2013-12-04 00:00:00\") AND (inv.InvoiceId IN (406))")
		;
		$this
			->dateTime($invoice->InvoiceDate)
				->hasDateAndTime('2013', '12', '04', '00', '00', '00')
		;
	}

	public function testWhereWithExpressionBuilder()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$qb = $composer->getQueryBuilder();                      // DBAL's query builder can be accessed from Casterlith (a new instance) and from a Composer (same one as the one used by the composer)

		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where($qb->expr()->andX(
				$qb->expr()->eq('art.Name', ':artistName'),
				$qb->expr()->neq('art.Name', ':notArtistName'),
				$qb->expr()->lt('art.ArtistId', ':ltArtistId'),
				$qb->expr()->lte('art.ArtistId', ':lteArtistId'),
				$qb->expr()->gt('art.ArtistId', ':gtArtistId'),
				$qb->expr()->gte('art.ArtistId', ':gteArtistId'),
				$qb->expr()->isNull('null'),
				$qb->expr()->isNotNull('alb.AlbumId'),
				$qb->expr()->like('alb.Title', ':albumTitle'),
				$qb->expr()->notLike('alb.Title', ':notAlbumTitle'),
				$qb->expr()->in('alb.AlbumId', ':inAlbumIds'),
				$qb->expr()->notIn('alb.AlbumId', ':notInAlbumIds'),
				$qb->expr()->orX(
					$qb->expr()->eq('art.Name', ':orXArtistId'),
					$qb->expr()->eq('art.Name', ':orXArtistName')
				)
			))
			->setParameter('artistName', "Audioslave")
			->setParameter('notArtistName', "BackBeat")
			->setParameter('ltArtistId', 9)
			->setParameter('lteArtistId', 8)
			->setParameter('gtArtistId', 7)
			->setParameter('gteArtistId', 8)
			->setParameter('albumTitle', "%Exile")
			->setParameter('notAlbumTitle', "Carnaval%")
			->setParameter('inAlbumIds', array(10,11), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('notInAlbumIds', array(12,13), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('orXArtistId', 8)
			->setParameter('orXArtistName', "Audioslave")
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(8)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE (art.Name = :artistName) AND (art.Name <> :notArtistName) AND (art.ArtistId < :ltArtistId) AND (art.ArtistId <= :lteArtistId) AND (art.ArtistId > :gtArtistId) AND (art.ArtistId >= :gteArtistId) AND (null IS NULL) AND (alb.AlbumId IS NOT NULL) AND (alb.Title LIKE :albumTitle) AND (alb.Title NOT LIKE :notAlbumTitle) AND (alb.AlbumId IN (:inAlbumIds)) AND (alb.AlbumId NOT IN (:notInAlbumIds)) AND ((art.Name = :orXArtistId) OR (art.Name = :orXArtistName)) AND (art.ArtistId IN (8))")
		;
	}

	public function testWhereWithResetOfConditions()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->select('art')
			->where('art.ArtistId = 3')
			->where('art.ArtistId = 4')
		;
		$artist = $query->first();

		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(4)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.ArtistId = 4) AND (art.ArtistId IN (4))")
		;
	}

	/*** andWhere ***/

	public function testAndWhereAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
			->andWhere('art.ArtistId = 54')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = \"Green Day\") AND (art.ArtistId = 54)")
		;
	}

	public function testAndWhereFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
			->andWhere('art.ArtistId = 54')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = \"Green Day\") AND (art.ArtistId = 54) AND (art.ArtistId IN (54))")
		;
	}

	public function testAndWhereWithApostrophe()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->andWhere('art.Name = \'Green Day\'')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.ArtistId IS NOT NULL) AND (art.Name = 'Green Day') AND (art.ArtistId IN (54))")
		;
	}

	public function testAndWhereWithGraveAccent()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('`art`.`Name` = "Green Day"')
			->andWhere('`art`.`ArtistId` = 54')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (`art`.`Name` = \"Green Day\") AND (`art`.`ArtistId` = 54) AND (art.ArtistId IN (54))")
		;
	}

	public function testAndWhereWithInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->andWhere('art.ArtistId = 54')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.ArtistId IS NOT NULL) AND (art.ArtistId = 54) AND (art.ArtistId IN (54))")
		;
	}

	public function testAndWhereWithDateTime()
	{
		$dt = new \DateTime("2013-12-04 00:00:00");

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceComposer');
		$query = $composer
			->select('inv')
			->where('inv.InvoiceId IS NOT NULL')
			->andWhere('inv.InvoiceDate = :invoiceDate')
			->setParameter('invoiceDate', $dt->format("Y-m-d H:i:s"))
		;
		$invoice = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT inv.InvoiceId as invcl1_InvoiceId,inv.CustomerId as invcl1_CustomerId,inv.InvoiceDate as invcl1_InvoiceDate,inv.BillingAddress as invcl1_BillingAddress,inv.BillingCity as invcl1_BillingCity,inv.BillingState as invcl1_BillingState,inv.BillingCountry as invcl1_BillingCountry,inv.BillingPostalCode as invcl1_BillingPostalCode,inv.Total as invcl1_Total FROM invoices inv WHERE (inv.InvoiceId IS NOT NULL) AND (inv.InvoiceDate = :invoiceDate) AND (inv.InvoiceId IN (406))")
		;
		$this
			->dateTime($invoice->InvoiceDate)
				->hasDateAndTime('2013', '12', '04', '00', '00', '00')
		;
	}

	public function testAndWhereWithExpressionBuilder()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$qb = $composer->getQueryBuilder();                      // DBAL's query builder can be accessed from Casterlith (a new instance) and from a Composer (same one as the one used by the composer)

		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId IS NOT NULL')
			->andWhere($qb->expr()->andX(
				$qb->expr()->eq('art.Name', ':artistName'),
				$qb->expr()->neq('art.Name', ':notArtistName'),
				$qb->expr()->lt('art.ArtistId', ':ltArtistId'),
				$qb->expr()->lte('art.ArtistId', ':lteArtistId'),
				$qb->expr()->gt('art.ArtistId', ':gtArtistId'),
				$qb->expr()->gte('art.ArtistId', ':gteArtistId'),
				$qb->expr()->isNull('null'),
				$qb->expr()->isNotNull('alb.AlbumId'),
				$qb->expr()->like('alb.Title', ':albumTitle'),
				$qb->expr()->notLike('alb.Title', ':notAlbumTitle'),
				$qb->expr()->in('alb.AlbumId', ':inAlbumIds'),
				$qb->expr()->notIn('alb.AlbumId', ':notInAlbumIds'),
				$qb->expr()->orX(
					$qb->expr()->eq('art.Name', ':orXArtistId'),
					$qb->expr()->eq('art.Name', ':orXArtistName')
				)
			))
			->setParameter('artistName', "Audioslave")
			->setParameter('notArtistName', "BackBeat")
			->setParameter('ltArtistId', 9)
			->setParameter('lteArtistId', 8)
			->setParameter('gtArtistId', 7)
			->setParameter('gteArtistId', 8)
			->setParameter('albumTitle', "%Exile")
			->setParameter('notAlbumTitle', "Carnaval%")
			->setParameter('inAlbumIds', array(10,11), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('notInAlbumIds', array(12,13), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('orXArtistId', 8)
			->setParameter('orXArtistName', "Audioslave")
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(8)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE (art.ArtistId IS NOT NULL) AND ((art.Name = :artistName) AND (art.Name <> :notArtistName) AND (art.ArtistId < :ltArtistId) AND (art.ArtistId <= :lteArtistId) AND (art.ArtistId > :gtArtistId) AND (art.ArtistId >= :gteArtistId) AND (null IS NULL) AND (alb.AlbumId IS NOT NULL) AND (alb.Title LIKE :albumTitle) AND (alb.Title NOT LIKE :notAlbumTitle) AND (alb.AlbumId IN (:inAlbumIds)) AND (alb.AlbumId NOT IN (:notInAlbumIds)) AND ((art.Name = :orXArtistId) OR (art.Name = :orXArtistName))) AND (art.ArtistId IN (8))")
		;
	}

	public function testAndWhereWithResetOfConditions()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->andWhere('art.ArtistId = 3')
			->where('art.ArtistId IS NOT NULL')
			->andWhere('art.ArtistId = 4')
		;
		$artist = $query->first();

		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(4)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.ArtistId IS NOT NULL) AND (art.ArtistId = 4) AND (art.ArtistId IN (4))")
		;
	}

	/*** orWhere ***/

	public function testOrWhereAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
			->orWhere('art.ArtistId = 54')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = \"Green Day\") OR (art.ArtistId = 54)")
		;
	}

	public function testOrWhereFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = "Green Day"')
			->orWhere('art.ArtistId = 54')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(54)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE ((art.Name = \"Green Day\") OR (art.ArtistId = 54)) AND (art.ArtistId IN (54))")
		;
	}

	public function testOrWhereWithApostrophe()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->orWhere('art.Name = \'Green Day\'')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE ((art.ArtistId IS NOT NULL) OR (art.Name = 'Green Day')) AND (art.ArtistId IN (1))")
		;
	}

	public function testOrWhereWithGraveAccent()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('`art`.`Name` = "Green Day"')
			->orWhere('`art`.`ArtistId` = 54')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE ((`art`.`Name` = \"Green Day\") OR (`art`.`ArtistId` = 54)) AND (art.ArtistId IN (54))")
		;
	}

	public function testOrWhereWithInteger()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->orWhere('art.ArtistId = 54')
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE ((art.ArtistId IS NOT NULL) OR (art.ArtistId = 54)) AND (art.ArtistId IN (1))")
		;
	}

	public function testOrWhereWithDateTime()
	{
		$dt = new \DateTime("2013-12-04 00:00:00");

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceComposer');
		$query = $composer
			->select('inv')
			->where('inv.InvoiceId IS NOT NULL')
			->orWhere('inv.InvoiceDate = :invoiceDate')
			->setParameter('invoiceDate', $dt->format("Y-m-d H:i:s"))
		;
		$invoice = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT inv.InvoiceId as invcl1_InvoiceId,inv.CustomerId as invcl1_CustomerId,inv.InvoiceDate as invcl1_InvoiceDate,inv.BillingAddress as invcl1_BillingAddress,inv.BillingCity as invcl1_BillingCity,inv.BillingState as invcl1_BillingState,inv.BillingCountry as invcl1_BillingCountry,inv.BillingPostalCode as invcl1_BillingPostalCode,inv.Total as invcl1_Total FROM invoices inv WHERE ((inv.InvoiceId IS NOT NULL) OR (inv.InvoiceDate = :invoiceDate)) AND (inv.InvoiceId IN (1))")
		;
		$this
			->dateTime($invoice->InvoiceDate)
				->hasDateAndTime('2009', '01', '01', '00', '00', '00')
		;
	}

	public function testOrWhereWithExpressionBuilder()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$qb = $composer->getQueryBuilder();                      // DBAL's query builder can be accessed from Casterlith (a new instance) and from a Composer (same one as the one used by the composer)

		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId IS NOT NULL')
			->orWhere($qb->expr()->andX(
				$qb->expr()->eq('art.Name', ':artistName'),
				$qb->expr()->neq('art.Name', ':notArtistName'),
				$qb->expr()->lt('art.ArtistId', ':ltArtistId'),
				$qb->expr()->lte('art.ArtistId', ':lteArtistId'),
				$qb->expr()->gt('art.ArtistId', ':gtArtistId'),
				$qb->expr()->gte('art.ArtistId', ':gteArtistId'),
				$qb->expr()->isNull('null'),
				$qb->expr()->isNotNull('alb.AlbumId'),
				$qb->expr()->like('alb.Title', ':albumTitle'),
				$qb->expr()->notLike('alb.Title', ':notAlbumTitle'),
				$qb->expr()->in('alb.AlbumId', ':inAlbumIds'),
				$qb->expr()->notIn('alb.AlbumId', ':notInAlbumIds'),
				$qb->expr()->orX(
					$qb->expr()->eq('art.Name', ':orXArtistId'),
					$qb->expr()->eq('art.Name', ':orXArtistName')
				)
			))
			->setParameter('artistName', "Audioslave")
			->setParameter('notArtistName', "BackBeat")
			->setParameter('ltArtistId', 9)
			->setParameter('lteArtistId', 8)
			->setParameter('gtArtistId', 7)
			->setParameter('gteArtistId', 8)
			->setParameter('albumTitle', "%Exile")
			->setParameter('notAlbumTitle', "Carnaval%")
			->setParameter('inAlbumIds', array(10,11), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('notInAlbumIds', array(12,13), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			->setParameter('orXArtistId', 8)
			->setParameter('orXArtistName', "Audioslave")
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE ((art.ArtistId IS NOT NULL) OR ((art.Name = :artistName) AND (art.Name <> :notArtistName) AND (art.ArtistId < :ltArtistId) AND (art.ArtistId <= :lteArtistId) AND (art.ArtistId > :gtArtistId) AND (art.ArtistId >= :gteArtistId) AND (null IS NULL) AND (alb.AlbumId IS NOT NULL) AND (alb.Title LIKE :albumTitle) AND (alb.Title NOT LIKE :notAlbumTitle) AND (alb.AlbumId IN (:inAlbumIds)) AND (alb.AlbumId NOT IN (:notInAlbumIds)) AND ((art.Name = :orXArtistId) OR (art.Name = :orXArtistName)))) AND (art.ArtistId IN (1))")
		;
	}

	public function testOrWhereWithResetOfConditions()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->select('art')
			->where('art.ArtistId IS NOT NULL')
			->orWhere('art.ArtistId = 3')
			->where('art.ArtistId IS NOT NULL')
			->orWhere('art.ArtistId = 4')
		;
		$artist = $query->first();

		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE ((art.ArtistId IS NOT NULL) OR (art.ArtistId = 4)) AND (art.ArtistId IN (1))")
		;
	}

	/*** setParameter ***/

	public function testSetParameter()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->where('art.Name = :artistName')
			->setParameter('artistName', "Alice In Chains")
		;
		$artist = $query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE (art.Name = :artistName) AND (art.ArtistId IN (5))")
		;
		$this
			->string($artist->Name)
				->isEqualTo("Alice In Chains")
		;
	}

	//	Please take a look to those tests
	//	 - testWhereWithExpressionBuilder
	//	 - testAndWhereWithDateTime

	/*** groupBy ***/

	public function testGroupBy()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->select('alb', 'art')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
		;
		$albums = $query->all();
		$album  = reset($albums);

		$this
			->array($albums)
				->hasSize(204)
		;
		$this
			->object($album)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity')
		;
		$this
			->object($album->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($album->artist->albums)
				->hasSize(1) // Instead of 2 because of the groupBy
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.AlbumId as albcl1_AlbumId,alb.Title as albcl1_Title,alb.ArtistId as albcl1_ArtistId, art.ArtistId as artcl2_ArtistId,art.Name as artcl2_Name FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.ArtistId")
		;
	}

	public function testGroupByWithRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->selectAsRaw('alb', 'alb.ArtistId, count(alb.ArtistId) as nb')
			->groupBy('alb.ArtistId')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(1)
		;
		$this
			->string($result->nb)
				->isEqualTo(2)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, count(alb.ArtistId) as nb FROM albums alb GROUP BY alb.ArtistId")
		;
	}

	public function testGroupByWithReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->select('alb', 'art')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
			->groupBy('alb.AlbumId')
		;
		$albums = $query->all();
		$album  = reset($albums);

		$this
			->array($albums)
				->hasSize(347)
		;
		$this
			->object($album)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity')
		;
		$this
			->object($album->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($album->artist->albums)
				->hasSize(2) // Instead of 2 because of the groupBy
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.AlbumId as albcl1_AlbumId,alb.Title as albcl1_Title,alb.ArtistId as albcl1_ArtistId, art.ArtistId as artcl2_ArtistId,art.Name as artcl2_Name FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.AlbumId")
		;
	}

	public function testGroupByWithRawSelectionAndReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->selectAsRaw('alb', 'alb.ArtistId, count(alb.ArtistId) as nb')
			->groupBy('alb.ArtistId')
			->groupBy('alb.AlbumId')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(1)
		;
		$this
			->string($result->nb)
				->isEqualTo(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, count(alb.ArtistId) as nb FROM albums alb GROUP BY alb.AlbumId")
		;
	}

	/*** addGroupBy ***/

	public function testAddGroupBy()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->select('alb', 'art')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
			->addGroupBy('art.ArtistId')
		;
		$albums = $query->all();
		$album  = reset($albums);

		$this
			->array($albums)
				->hasSize(204)
		;
		$this
			->object($album)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity')
		;
		$this
			->object($album->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($album->artist->albums)
				->hasSize(1) // Instead of 2 because of the groupBy
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.AlbumId as albcl1_AlbumId,alb.Title as albcl1_Title,alb.ArtistId as albcl1_ArtistId, art.ArtistId as artcl2_ArtistId,art.Name as artcl2_Name FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.ArtistId, art.ArtistId")
		;
	}

	public function testAddGroupByWithRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->selectAsRaw('alb', 'alb.ArtistId, count(alb.ArtistId) as nb')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
			->addGroupBy('art.ArtistId')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(1)
		;
		$this
			->string($result->nb)
				->isEqualTo(2)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, count(alb.ArtistId) as nb FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.ArtistId, art.ArtistId")
		;
	}

	public function testAddGroupByWithReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->select('alb', 'art')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
			->addGroupBy('art.ArtistId')
			->groupBy('alb.AlbumId')
		;
		$albums = $query->all();
		$album  = reset($albums);

		$this
			->array($albums)
				->hasSize(347)
		;
		$this
			->object($album)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity')
		;
		$this
			->object($album->artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->array($album->artist->albums)
				->hasSize(2) // Instead of 2 because of the groupBy
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.AlbumId as albcl1_AlbumId,alb.Title as albcl1_Title,alb.ArtistId as albcl1_ArtistId, art.ArtistId as artcl2_ArtistId,art.Name as artcl2_Name FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.AlbumId")
		;
	}

	public function testAddGroupByWithRawSelectionAndReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumComposer');
		$query = $composer
			->selectAsRaw('alb', 'alb.ArtistId, count(alb.ArtistId) as nb')
			->join('alb', 'art', 'artist')
			->groupBy('alb.ArtistId')
			->addGroupBy('art.ArtistId')
			->groupBy('alb.AlbumId')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(1)
		;
		$this
			->string($result->nb)
				->isEqualTo(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, count(alb.ArtistId) as nb FROM albums alb INNER JOIN artists art ON `alb`.ArtistId = `art`.ArtistId GROUP BY alb.AlbumId")
		;
	}

	/*** having ***/

	public function testHaving()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(26)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(8)
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING COUNT(alb.AlbumId) > 2")
		;
	}

	public function testHavingWithRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'alb.ArtistId, COUNT(alb.AlbumId) as nb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(8)
		;
		$this
			->string($result->nb)
				->isEqualTo(3)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, COUNT(alb.AlbumId) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING COUNT(alb.AlbumId) > 2")
		;
	}

	/*** addHaving ***/

	public function testAndHaving()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 3')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(12)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(21)
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) AND (COUNT(alb.AlbumId) > 3)")
		;
	}

	public function testAndHavingWithRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'alb.ArtistId, COUNT(alb.AlbumId) as nb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 3')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo("21")
		;
		$this
			->string($result->nb)
				->isEqualTo("4")
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, COUNT(alb.AlbumId) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) AND (COUNT(alb.AlbumId) > 3)")
		;
	}

	public function testAndHavingWithReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 3')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 4')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(7)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(22)
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) AND (COUNT(alb.AlbumId) > 4)")
		;
	}

	public function testAndHavingWithResetAndRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'alb.ArtistId, COUNT(alb.AlbumId) as nb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 3')
			->having('COUNT(alb.AlbumId) > 2')
			->andHaving('COUNT(alb.AlbumId) > 4')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo("22")
		;
		$this
			->string($result->nb)
				->isEqualTo("14")
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, COUNT(alb.AlbumId) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) AND (COUNT(alb.AlbumId) > 4)")
		;
	}

	/*** orHaving ***/

	public function testOrHaving()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 3')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(26)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(8)
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) OR (COUNT(alb.AlbumId) > 3)")
		;
	}

	public function testOrHavingWithRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'alb.ArtistId, COUNT(alb.AlbumId) as nb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 3')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo(8)
		;
		$this
			->string($result->nb)
				->isEqualTo(3)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, COUNT(alb.AlbumId) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) OR (COUNT(alb.AlbumId) > 3)")
		;
	}

	public function testOrHavingWithReset()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 3')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 4')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(26)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(8)
		;
		$this
			->array($artist->albums)
				->hasSize(1)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) OR (COUNT(alb.AlbumId) > 4)")
		;
	}

	public function testOrHavingWithResetAndRawSelection()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->selectAsRaw('art', 'alb.ArtistId, COUNT(alb.AlbumId) as nb')
			->join('art', 'alb', 'albums')
			->groupBy('art.ArtistId')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 3')
			->having('COUNT(alb.AlbumId) > 2')
			->orHaving('COUNT(alb.AlbumId) > 4')
		;
		$result = $query->first();

		$this
			->object($result)
				->isInstanceOf('\\stdClass')
		;
		$this
			->string($result->ArtistId)
				->isEqualTo("8")
		;
		$this
			->string($result->nb)
				->isEqualTo("3")
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT alb.ArtistId, COUNT(alb.AlbumId) as nb FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId GROUP BY art.ArtistId HAVING (COUNT(alb.AlbumId) > 2) OR (COUNT(alb.AlbumId) > 4)")
		;
	}

	/*** order ***/

	public function testOrderAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->order('art.Name', 'DESC')
		;
		$artists = $query->all();
		$artist  = reset($artists);

		$this
			->integer($artist->ArtistId)
				->isEqualTo(155)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art ORDER BY art.Name DESC")
		;
	}

	public function testOrderFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->order('art.Name', 'DESC')
		;
		$artist = $query->first();

		$this
			->integer($artist->ArtistId)
				->isEqualTo(155)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.ArtistId IN (155) ORDER BY art.Name DESC")
		;
	}

	public function testOrderFirstWithResetOrder()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art')
			->order('art.Name', 'DESC')
			->order('art.ArtistId', 'DESC')
		;
		$artist = $query->first();

		$this
			->integer($artist->ArtistId)
				->isEqualTo(275)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.ArtistId IN (275) ORDER BY art.ArtistId DESC")
		;
	}

	/*** addOrder ***/

	public function testAddOrderAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId = 90')
			->order('art.Name', 'DESC')
			->addOrder('alb.Title', 'DESC')
		;
		$artists = $query->all();
		$artist  = reset($artists);
		$album   = reset($artist->albums);

		$this
			->integer($artist->ArtistId)
				->isEqualTo(90)
		;
		$this
			->integer($album->AlbumId)
				->isEqualTo(114)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE art.ArtistId = 90 ORDER BY art.Name DESC, alb.Title DESC")
		;
	}

	public function testAddOrderFirst()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId = 90')
			->order('art.Name', 'DESC')
			->addOrder('alb.Title', 'DESC')
		;
		$artist = $query->first();
		$album  = reset($artist->albums);

		$this
			->integer($artist->ArtistId)
				->isEqualTo(90)
		;
		$this
			->integer($album->AlbumId)
				->isEqualTo(114)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE (art.ArtistId = 90) AND (art.ArtistId IN (90)) ORDER BY art.Name DESC, alb.Title DESC")
		;
	}

	public function testAddOrderFirstWithResetOrder()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId = 90')
			->order('art.Name', 'DESC')
			->addOrder('alb.Title', 'DESC')
			->order('art.Name', 'ASC')
			->addOrder('alb.Title', 'ASC')
		;
		$artist = $query->first();
		$album  = reset($artist->albums);

		$this
			->integer($artist->ArtistId)
				->isEqualTo(90)
		;
		$this
			->integer($album->AlbumId)
				->isEqualTo(94)
		;
		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE (art.ArtistId = 90) AND (art.ArtistId IN (90)) ORDER BY art.Name ASC, alb.Title ASC")
		;
	}

	/*** limit ***/

	public function testLimit()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(true);
		$config->setExceptionMultipleResultOnFirst(true); // We except this configuration to be useless with autoSelection equal to true

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->order('art.ArtistId', 'DESC')
		;
		$artists = $query->limit(0, 10);
		$artist  = reset($artists);

		$this
			->array($artists)
				->hasSize(10)
		;
		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
				->isEqualTo(275)
		;
	}

	public function testLimitWithZeroToEveryParameters()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(true);
		$config->setExceptionMultipleResultOnFirst(true); // We except this configuration to be useless with autoSelection equal to true

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->leftJoin('art', 'alb', 'albums')
			->order('art.ArtistId', 'DESC')
		;
		$artists = $query->limit(0, 0);

		$this
			->array($artists)
				->hasSize(275)
		;
	}

	public function testLimitWithInvalidLimitParameter()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(true);
		$config->setExceptionMultipleResultOnFirst(true); // We except this configuration to be useless with autoSelection equal to true

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->order('art.ArtistId', 'DESC')
		;

		$this
			->exception(
				function() use($query) {
					$artists = $query->limit(0, -10);
				}
			)
		;
	}

	public function testLimitWithInvalidOffsetParameter()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(true);
		$config->setExceptionMultipleResultOnFirst(true); // We except this configuration to be useless with autoSelection equal to true

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->order('art.ArtistId', 'DESC')
		;

		$this
			->exception(
				function() use($query) {
					$artists = $query->limit(-10, 10);
				}
			)
		;
	}

	/*** first ***/

	public function testFirstWithAutoSelection()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(true);
		$config->setExceptionMultipleResultOnFirst(true); // We except this configuration to be useless with autoSelection equal to true

		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId <= 10')
		;
		$artist = $query->first();
		$album  = reset($artist->albums);

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(1)
		;
	}

	public function testFirstWithoutAutoSelection()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(false);
		$config->setExceptionMultipleResultOnFirst(false);
		
		$orm = getAReadOnlyOrmInstance("unit-tests", $config);
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId <= 10')
		;
		$artist = $query->first();

		$this
			->object($artist)
				->isInstanceOf('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity')
		;
		$this
			->integer($artist->ArtistId)
			 	->isEqualTo(1)
		;
	}

	public function testFirstWithExceptionMultipleResultOnFirst()
	{
		$config = new \Monolith\Casterlith\Configuration();
		$config->setFirstAutoSelection(false);
		$config->setExceptionMultipleResultOnFirst(true);
		
		$orm = getAReadOnlyOrmInstance("unit-tests", $config);
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer
			->select('art', 'alb')
			->join('art', 'alb', 'albums')
			->where('art.ArtistId <= 10')
		;

		$this
			->exception(
				function() use($query) {
					$artist = $query->first();
				}
			)
		;
	}

}

//	Valid

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapper';
}

class AlbumComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumMapper';
}

class InvoiceComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceMapper';
}

class EmployeeComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\EmployeeMapper';
}

class ArtistMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'artists';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'ArtistId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'ArtistId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Name'      => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'albums'            => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Composer\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId', 'artist'),
				'albumsNoRecursion' => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Composer\AlbumMapper(), 'artist', 'album', '`artist`.ArtistId = `album`.ArtistId'),
			);
		}

		return self::$relations;
	}
}

class AlbumMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'albums';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\AlbumEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'AlbumId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'AlbumId'   => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'Title'     => array('type' => 'string'),
				'ArtistId'  => array('type' => 'integer'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'artist' => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Composer\ArtistMapper(), 'album', 'artist', '`album`.ArtistId = `artist`.ArtistId', 'albums'),
			);
		}

		return self::$relations;
	}
}

class InvoiceMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'invoices';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'InvoiceId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'InvoiceId'          => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'CustomerId'         => array('type' => 'integer'),
				'InvoiceDate'        => array('type' => 'datetime'),
				'BillingAddress'     => array('type' => 'string'),
				'BillingCity'        => array('type' => 'string'),
				'BillingState'       => array('type' => 'string'),
				'BillingCountry'     => array('type' => 'string'),
				'BillingPostalCode'  => array('type' => 'string'),
				'Total'              => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
			);
		}

		return self::$relations;
	}
}

class EmployeeMapper extends \Monolith\Casterlith\Mapper\AbstractMapper implements \Monolith\Casterlith\Mapper\MapperInterface
{
	protected static $table      = 'employees';
	protected static $entity     = '\\Monolith\\Casterlith\\tests\\units\\Composer\\EmployeeEntity';
	protected static $fields     = null;
	protected static $relations  = null;

	public static function getPrimaryKey()
	{
		return 'EmployeeId';
	}

	/**
	 * @return array
	 */
	public static function getFields()
	{
		if (is_null(self::$fields)) {
			self::$fields = array(
				'EmployeeId'  => array('type' => 'integer', 'primary' => true, 'autoincrement' => true),
				'FirstName'   => array('type' => 'string'),
				'LastName'    => array('type' => 'string'),
				'Title'       => array('type' => 'string'),
				'ReportsTo'   => array('type' => 'string'),
				'BirthDate'   => array('type' => 'string'),
				'HireDate'    => array('type' => 'string'),
				'Address'     => array('type' => 'string'),
				'City'        => array('type' => 'string'),
				'State'       => array('type' => 'string'),
				'Country'     => array('type' => 'string'),
				'PostalCode'  => array('type' => 'string'),
				'Phone'       => array('type' => 'string'),
				'Fax'         => array('type' => 'string'),
				'Email'       => array('type' => 'string'),
			);
		}

		return self::$fields;
	}

	public static function getRelations()
	{
		if (is_null(self::$relations)) {
			self::$relations = array(
				'reportsTo'     => new \Monolith\Casterlith\Relations\ManyToOne(new \Monolith\Casterlith\tests\units\Composer\EmployeeMapper(), 'sub', 'sup', '`sub`.ReportsTo = `sup`.EmployeeId', 'isReportedBy'),
				'isReportedBy'  => new \Monolith\Casterlith\Relations\OneToMany(new \Monolith\Casterlith\tests\units\Composer\EmployeeMapper(), 'sup', 'sub', '`sup`.EmployeeId = `sub`.ReportsTo', 'reportsTo'),
			);
		}

		return self::$relations;
	}
}

class ArtistEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $ArtistId  = null;
	public $Name      = null;

	public $albums             = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $albumsNoRecursion  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->ArtistId;
	}
}

class AlbumEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $AlbumId   = null;
	public $Title     = null;
	public $ArtistId  = null;

	public $tracks  = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $artist  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->AlbumId;
	}
}

class InvoiceEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $InvoiceId          = null;
	public $CustomerId         = null;
	public $InvoiceDate        = null;
	public $BillingAddress     = null;
	public $BillingCity        = null;
	public $BillingState       = null;
	public $BillingCountry     = null;
	public $BillingPostalCode  = null;
	public $Total              = null;

	public function getPrimaryValue()
	{
		return $this->InvoiceId;
	}
}

class EmployeeEntity implements \Monolith\Casterlith\Entity\EntityInterface
{
	public $EmployeeId  = null;
	public $FirstName   = null;
	public $LastName    = null;
	public $Title       = null;
	public $ReportsTo   = null;
	public $BirthDate   = null;
	public $HireDate    = null;
	public $Address     = null;
	public $City        = null;
	public $State       = null;
	public $Country     = null;
	public $PostalCode  = null;
	public $Phone       = null;
	public $Fax         = null;
	public $Email       = null;

	public $customers     = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $reportsTo     = \Monolith\Casterlith\Casterlith::NOT_LOADED;
	public $isReportedBy  = \Monolith\Casterlith\Casterlith::NOT_LOADED;

	public function getPrimaryValue()
	{
		return $this->EmployeeId;
	}
}

//	Invalid

class ArtistComposerWithoutComposerInterface extends \Monolith\Casterlith\Composer\AbstractComposer
{
}

class ArtistComposerWithoutAValidMapper extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapperWithoutMapperInterface';
}

class ArtistMapperWithoutMapperInterface extends \Monolith\Casterlith\Mapper\AbstractMapper
{
}
