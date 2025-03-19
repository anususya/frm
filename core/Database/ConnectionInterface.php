<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Database\Query\Builder as QueryBuilder;
use PDO;

interface ConnectionInterface
{
    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return array<mixed>
     */
    public function select(string $query, array $bindings = []): array;

    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return int
     */
    public function affectingStatement(string $query, array $bindings = []): int;

    /**
     * @param array<mixed>  $bindings
     *
     * @return array<mixed>
     */
    public function prepareBindings(array $bindings): array;

    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return int
     */
    public function delete(string $query, array $bindings = []): int;

    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return bool
     */
    public function insert(string $query, array $bindings = []): bool;

    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return int
     */
    public function update(string $query, array $bindings = []): int;
    public function query(): QueryBuilder;

    /**
     * @param string $query
     * @param array<mixed>  $bindings
     *
     * @return void
     */
    public function statement(string $query, array $bindings = []): void;
}
