<?php

namespace Core\Database;

interface ConnectionInteface
{
    public function select($query, $bindings = []): array;
   // public function selectOne($query, $bindings = [], $useReadPdo = true): mixed;
    //public function statement($query, $bindings = []);
    public function affectingStatement($query, $bindings = []): int;
    public function prepareBindings(array $bindings): array;
    public function delete($query, $bindings = []): int;
    public function insert($query, $bindings = []): bool;
    public function update($query, $bindings = []): int;

}
