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
		$query = $composer->select('art');
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
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$query->first();

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
		$orm = getAReadOnlyOrmInstance("_cr");
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

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
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$query->select('art');
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art WHERE art.ArtistId IN (1)")
		;
	}

	public function testTwoSelectWithTheSameQuery()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$query->first();

		$query->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
		$query->first();

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name, alb.AlbumId as albcl2_AlbumId,alb.Title as albcl2_Title,alb.ArtistId as albcl2_ArtistId FROM artists art INNER JOIN albums alb ON `art`.ArtistId = `alb`.ArtistId WHERE art.ArtistId IN (1)")
		;
	}

	/*** addSelect ***/

	public function testAddSelectWithEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');
		$query->addSelect('alb');

		$this
			->string($query->getSQL())
				->isEqualTo("SELECT art.ArtistId as artcl1_ArtistId,art.Name as artcl1_Name FROM artists art")
		;
	}

	public function testAddSelectWithoutEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');

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
		$query = $composer->select('art');

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
		$query = $composer->select('art');

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
		$query = $composer->select('art');

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
		$query = $composer->select('art');
		$query->addSelect('alb');
		$query->join('art', 'alb', 'albums');
		$query->select('art');
		$query->addSelect('alb');
		$query->join('art', 'alb', 'albums');
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
		$query = $composer->selectAsRaw('art');

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
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb');
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
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2');
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
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2');
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
		$query = $composer->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb');
		$query->join('art', 'alb', 'albums');
		$query->selectAsRaw('art', 'count(distinct(art.ArtistId)) as nb');
		$query->join('art', 'alb', 'albums');
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
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb');
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
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb', 'count(distinct(art.ArtistId)) as nb2');
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
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb, count(distinct(art.ArtistId)) as nb2');
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
		$query = $composer->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb');
		$query->join('art', 'alb', 'albums');
		$query->selectAsRaw('art');
		$query->addSelectAsRaw('count(distinct(art.ArtistId)) as nb');
		$query->join('art', 'alb', 'albums');
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
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
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
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albums');
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
			->boolean($artist->albumsNoRecursion === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
	}

	public function testJoinWithAllArtistsAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->join('art', 'alb', 'albumsNoRecursion');
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
			->boolean($artist->albumsNoRecursion[293]->artist === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
		$this
			->boolean($artist->albums === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
	}

	public function testJoinWithTheSameEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');

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
		$query = $composer->select('art', 'alb');

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
		$query = $composer->select('art', 'alb');

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

	/*** leftJoin ***/

	public function testLeftJoinWithOneArtist()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->leftJoin('art', 'alb', 'albums');
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
		$query = $composer->select('art', 'alb');
		$query->leftJoin('art', 'alb', 'albums');
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
			->boolean($artist->albumsNoRecursion === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
	}

	public function testLeftJoinWithAllArtistsAndNoRecursion()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');
		$query->leftJoin('art', 'alb', 'albumsNoRecursion');
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
			->boolean($artist->albumsNoRecursion[293]->artist === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
		$this
			->boolean($artist->albums === \Monolith\Casterlith\Casterlith::NOT_LOADED)
				->isEqualTo(true)
		;
	}

	public function testLeftJoinWithTheSameEntityAlias()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art', 'alb');

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
		$query = $composer->select('art', 'alb');

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
		$query = $composer->select('art', 'alb');

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

	/*** where ***/

	public function testWhereAll()
	{
		$orm = getAReadOnlyOrmInstance();
		$composer = $orm->getComposer('\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistComposer');
		$query = $composer->select('art');
		$query->where('art.Name = "Green Day"');
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
		$query = $composer->select('art');
		$query->where('art.Name = "Green Day"');
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
		$query = $composer->select('art');
		$query->where('art.Name = \'Green Day\'');
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
		$query = $composer->select('art');
		$query->where('`art`.`Name` = "Green Day"');
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
		$query = $composer->select('art');
		$query->where('art.ArtistId = 54');
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

	/*** addWhere ***/

}

//	Valid

class ArtistComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\ArtistMapper';
}

class InvoiceComposer extends \Monolith\Casterlith\Composer\AbstractComposer implements \Monolith\Casterlith\Composer\ComposerInterface
{
	protected static $mapperName  = '\\Monolith\\Casterlith\\tests\\units\\Composer\\InvoiceMapper';
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
				//'tracks' => new \Monolith\Casterlith\Relations\OneToMany(new TrackMapper(), 'album', 'track', '`album`.AlbumId = `track`.AlbumId', 'album'),
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
