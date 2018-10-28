# GoodBans

Basically a reimplementation of <http://bestbans.com>. That site has been out
of maintenance for a super long time and I loved using it, so I got fed up and
remade it.

## Requirements

- PHP 7.0+

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

$db->refresh(); // initializes the tables and fetches data from the interwebs!
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