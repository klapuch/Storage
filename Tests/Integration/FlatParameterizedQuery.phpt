<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Storage\Integration;

use Klapuch\Storage;
use Klapuch\Storage\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class FlatParameterizedQuery extends TestCase\PostgresDatabase {
	public function testArbitraryDimensionToSingle() {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO test (name, type) VALUES (?, ?)',
			['Dom', 'A']
		))->execute();
		$query = new Storage\FlatParameterizedQuery(
			$this->database,
			'SELECT name, type FROM test WHERE name = :nested_name AND type = :nested_nested_type',
			[
				'nested' => [
					'name' => 'Dom',
					'nested' => [
						'type' => 'A',
					],
				],
			]
		);
		Assert::same('Dom', $query->field());
		Assert::same(['name' => 'Dom', 'type' => 'A'], $query->row());
		Assert::same([['name' => 'Dom', 'type' => 'A']], $query->rows());
		Assert::same([['name' => 'Dom', 'type' => 'A']], iterator_to_array($query->execute()));
	}

	public function testSingleDimensionWithoutChange() {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO test (name, type) VALUES (?, ?)',
			['Dom', 'A']
		))->execute();
		$query = new Storage\FlatParameterizedQuery(
			$this->database,
			'SELECT name, type FROM test WHERE name = :name AND type = :type',
			[
				'name' => 'Dom',
				'type' => 'A',
			]
		);
		Assert::same('Dom', $query->field());
		Assert::same(['name' => 'Dom', 'type' => 'A'], $query->row());
		Assert::same([['name' => 'Dom', 'type' => 'A']], $query->rows());
		Assert::same([['name' => 'Dom', 'type' => 'A']], iterator_to_array($query->execute()));
	}
}


(new FlatParameterizedQuery())->run();