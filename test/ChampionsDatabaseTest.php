<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\ChampionGG;
use GoodBans\Test\Mock\RiotChampions;
use PHPUnit\Framework\TestCase;

final class ChampionsDatabaseTest // extends TestCase
{
	protected $db;

	protected function setUp() {
		$this->db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			new ChampionGG(),
			new RiotChampions()
		);
		$this->db->load_csv(__DIR__ . '/datasets/simple.csv');
	}
	protected function tearDown() {
		$this->db = null;
	}
	public function test_visit() {
    $this->db->visit('Chipotle');
    $data = $this->db->get_all_rows();
		$expected = array(
			array(
				"Chipotle",
				"6",
				"mexican",
			),
			array(
				"Santa Monica Seafood",
				"10",
				"seafood",
			),
    );
    
    $this->assertEquals($expected, $data);
  }
  
	public function test_visit_new_restaurant() {
    $this->db->visit('McDonalds', 'fast food');
    $data = $this->db->get_all_rows();
		$expected = array(
			array(
				"Chipotle",
				"5",
				"mexican",
			),
			array(
				"Santa Monica Seafood",
				"10",
				"seafood",
			),
			array(
				"McDonalds",
				"1",
				"fast food",
			),
    );
    $this->assertEquals($expected, $data);
  }
	public function test_least_visited_restaurant() {
		// TODO: create a test case for ties
    $least_visited = $this->db->least_visited_restaurant();
    $expected = 'Chipotle';
    
    $this->assertEquals($expected, $least_visited);
	}
	public function test_remove() {
    $this->db->remove('Chipotle');
    $expected = array(
      array(
				"Santa Monica Seafood",
				"10",
				"seafood",
			),
    );
    $this->assertEquals($expected, $this->db->get_all_rows());
	}
}