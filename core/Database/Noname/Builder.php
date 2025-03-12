<?php

namespace Core\Database\Noname;

use Core\Database\Query\Builder as QueryBuilder;
use Core\Database\Noname\Model;

class Builder
{
    /**
     * @var Model
     */
    protected Model $model;
    protected $table;

    public function __construct(
        protected QueryBuilder $query
    ) {
    }

    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return void
     */
    public function __call($method, $parameters)
    {
        $this->forwardCallTo($this->query, $method, $parameters);
    }

    /**
     * @param $object
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    protected function forwardCallTo($object, $method, $parameters)
    {
        return $object->{$method}(...$parameters);
    }

    public function get($columns = ['*'])
    {
        // return $this->query->get($columns)->all();
        $builder = clone $this;
        if (count($models = $builder->getModels($columns)) > 0) {
            // $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
        //return $this->query->get($columns)->all();
    }

    public function delete()
    {
        return $this->query->delete();
    }

    public function update(array $values)
    {
        return $this->query->update($values);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getModels($columns = ['*'])
    {
        return $this->hydrate($this->query->get($columns)->all())->all();
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    public function from($table, $as = null)
    {
        if ($this->isQueryable($table)) {
            return $this->fromSub($table, $as);
        }

        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(
            array_map(function ($item) use ($items, $instance) {
                $model = $instance->newFromBuilder($item);

                //            if (count($items) > 1) {
                //                $model->preventsLazyLoading = Model::preventsLazyLoading();
                //            }

                return $model;
            }, $items)
        );
    }

    public function newModelInstance($attributes = [])
    {
        //$attributes = array_merge($this->pendingAttributes, $attributes);

        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }

    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }
        $this->limit(1);
        return $this->whereKey($id)->get($columns)->first();
    }

    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }

    public function whereKey($id)
    {
        if (is_array($id)) {
            if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
                $this->query->whereIntegerInRaw($this->model->getKeyName(), $id);
            } else {
                $this->query->whereIn($this->model->getKeyName(), $id);
            }

            return $this;
        }

        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getKeyName(), '=', $id);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->query->where(...func_get_args());

        return $this;
    }
}
