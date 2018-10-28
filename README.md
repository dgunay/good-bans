# GoodBans

Basically a reimplementation of <http://bestbans.com>. That site has been out
of maintenance for a super long time and I loved using it, so I got fed up and
remade it.

## Requirements

- PHP 7.0+

## Installation

1. Create a database. Can be whatever vendor you want, but I used SQLite3.

2. Make sure you don't already have a table called "champions", it will overwrite
that. To refresh the database, use `refresh_db.php` as an example:

TODO: rewrite this whole thing
```php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../config.php';

use GoodBans\OpGG;
use GoodBans\RiotChampions;
use GoodBans\ChampionsDatabase;

$db = new ChampionsDatabase(
	new \PDO('sqlite:/path/to/your_db.db'), // use whatever PDO you like
	new OpGG(),
);

$db->refresh();
```

3. Put something like this in your HTML

```php
	use GoodBans\BanRanker;
	use GoodBans\View;
	$ranker = new BanRanker(new \PDO('sqlite:/your/db/here.db'));
	$bans = $ranker->best_bans();
	$view = new View($bans);
	echo $view->render();
```

You can also just use the BanRanker model to make your own view.