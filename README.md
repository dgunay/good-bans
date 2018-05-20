# GoodBans

Basically a reimplementation of <http://bestbans.com>. That site has been out
of maintenance for a super long time and I loved using it, so I got fed up and
remade it.

## Installation

1. Create a database. Can be whatever vendor you want, but I use SQLite3.

2. Make sure you don't already have a table called "champions", it will overwrite
that. Run 

```sh
php refresh_db.php
```

to refresh the database.

3. Put something like this in your HTML

```php
	$ranker = new BanRanker(new \PDO('sqlite:/your/db/here.db'));
	$bans = $ranker->best_bans();
	$view = new GoodBansView($bans);
	echo $view->render();
```

You can also just use the BanRanker model to make your own view.