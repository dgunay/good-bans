# GoodBans

Basically a reimplementation of <http://bestbans.com>. That site has been out
of maintenance for a super long time and I loved using it, so I got fed up and
remade it.

GoodBans (exactly like BestBans) decides what champions are most likely to pose
a threat to you if not on your team by calculating which champions are the most 
commonly-picked and highest winrate. The reasoning is that even though some
champions with dedicated mains can pull very high winrates, you are far more 
likely to meet a popular champion with a good winrate. Since you can't know
who is on the enemy team, the most statistically beneficial strategy is to ban
out the most common and effective solo queue threats.

This logic completely ignores team composition and purely favors a law of large
numbers approach, so use it if you are simply not sure what to ban. Oh, and
don't ban away champions your teammates want to pick. That's a dick move and
you'll probably hurt your chances of winning.

## Requirements

- PHP 7.1+

## Installation

1. Create a database. Can be whatever vendor you want, but I used SQLite3.

2. To refresh the database, use `refresh_db.php` as an example:

```php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../config.php';

use GoodBans\OpGG;
use GoodBans\RiotChampions;
use GoodBans\ChampionsDatabase;

$db = new ChampionsDatabase(
	new \PDO('sqlite:/path/to/your_db.db'), // use whatever PDO you like
	new OpGG(),
	new RiotChampions()
);

$db->refresh(); // prepares the tables and fetches data from the interwebs!
```

3. Put something like this in your HTML

```php
	use GoodBans\ChampionsDatabase;
	use GoodBans\TopBans;
	use GoodBans\View;
	$bans = $db->topBans();
	$view = new View($bans);
	echo $view->render();
```

You can also just use the TopBans model to make your own view.

## Development

Master branch is to remain deployable at all times. Development is merged into
release candidate branches (example: `rc-1.0`). Patches continue from release
candidate branches and are merged back into master with an appropriate tag.

### Running Tests

The `run_tests.php` script should handle it, but otherwise you can just do
`php ./vendor/bin/phpunit ./test`. Tests require PDO SQLite3.

### Contributing

GoodBans is intended to provide the same ban efficacy analysis methodology with 
any set of data (though obviously the results depend on what the data looks 
like).

If you know any good LoL stat aggregators with a friendly API or (ethically)
scrapable interface, one of the most awesome things you can do is write a 
subclass of `ChampionsDataSource` for that site. Just make sure that the stats
are separated by elo, and they at least have per-champion win/ban/pickrates.