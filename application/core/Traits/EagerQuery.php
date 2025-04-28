<?php

namespace App\core\Traits;

trait EagerQuery
{
    protected $relations = [];
    protected $eagerLoad = [];
    protected $aggregateRelations = [];

    # CONDITIONAL SECTION

    public function whereHas($relation, \Closure $callback = null)
    {
        if (empty($relation)) {
            return $this;
        }

        if (str_contains($relation, '.')) {
            $this->_applyNestedRelation($relation, $callback, 'AND', 'EXISTS');
        } else {
            $this->_applySingleRelation($relation, $callback, 'AND', 'EXISTS');
        }

        return $this;
    }

    public function orWhereHas($relation, \Closure $callback = null)
    {
        if (empty($relation)) {
            return $this;
        }

        if (str_contains($relation, '.')) {
            $this->_applyNestedRelation($relation, $callback, 'OR', 'EXISTS');
        } else {
            $this->_applySingleRelation($relation, $callback, 'OR', 'EXISTS');
        }

        return $this;
    }

    public function whereDoesntHave($relation, \Closure $callback = null)
    {
        if (empty($relation)) {
            return $this;
        }

        if (str_contains($relation, '.')) {
            $this->_applyNestedRelation($relation, $callback, 'AND', 'NOT EXISTS');
        } else {
            $this->_applySingleRelation($relation, $callback, 'AND', 'NOT EXISTS');
        }

        return $this;
    }

    public function orWhereDoesntHave($relation, \Closure $callback = null)
    {
        if (empty($relation)) {
            return $this;
        }

        if (str_contains($relation, '.')) {
            $this->_applyNestedRelation($relation, $callback, 'OR', 'NOT EXISTS');
        } else {
            $this->_applySingleRelation($relation, $callback, 'OR', 'NOT EXISTS');
        }

        return $this;
    }

    public function whereRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    public function orWhereRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    public function whereBetweenRelation($relation, $column, $range)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $range) {
                $query->whereBetweenRelation($nestedRelation, $column, $range);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $range) {
            $query->whereBetween($column, $range[0], $range[1]);
        });
    }

    public function orWhereBetweenRelation($relation, $column, $range)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $range) {
                $query->whereBetweenRelation($nestedRelation, $column, $range);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $range) {
            $query->whereBetween($column, $range[0], $range[1]);
        });
    }

    public function whereInRelation($relation, $column, $values)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $values) {
                $query->whereInRelation($nestedRelation, $column, $values);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $values) {
            $query->whereIn($column, $values);
        });
    }

    public function orWhereInRelation($relation, $column, $values)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $values) {
                $query->whereInRelation($nestedRelation, $column, $values);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $values) {
            $query->whereIn($column, $values);
        });
    }

    public function whereNullRelation($relation, $column)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column) {
                $query->whereNull($nestedRelation, $column);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column) {
            $query->whereNull($column);
        });
    }

    public function orWhereNullRelation($relation, $column)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column) {
                $query->whereNull($nestedRelation, $column);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column) {
            $query->whereNull($column);
        });
    }

    public function whereNotNullRelation($relation, $column)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column) {
                $query->whereNotNull($nestedRelation, $column);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column) {
            $query->whereNotNull($column);
        });
    }

    public function orWhereNotNullRelation($relation, $column)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column) {
                $query->whereNotNull($nestedRelation, $column);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column) {
            $query->whereNotNull($column);
        });
    }

    public function whereDateRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereDateRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->whereDate($column, $operator, $value);
        });
    }

    public function orWhereDateRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereDateRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->whereDate($column, $operator, $value);
        });
    }

    public function whereMonthRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereMonthRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->whereMonth($column, $operator, $value);
        });
    }

    public function orWhereMonthRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->orWhereMonthRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->orWhereMonth($column, $operator, $value);
        });
    }

    public function whereYearRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->whereYearRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->whereYear($column, $operator, $value);
        });
    }

    public function orWhereYearRelation($relation, $column, $operator = null, $value = null)
    {
        // Handle the case when the operator is omitted
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $nestedRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($nestedRelation, $column, $operator, $value) {
                $query->orWhereYearRelation($nestedRelation, $column, $operator, $value);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            $query->orWhereYear($column, $operator, $value);
        });
    }

    public function whereHasRelation($relation, $nestedRelation, \Closure $callback = null)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $lastRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($lastRelation, $nestedRelation, $callback) {
                $query->whereHasRelation($lastRelation, $nestedRelation, $callback);
            });
        }

        return $this->whereHas($relation, function ($query) use ($nestedRelation, $callback) {
            $query->whereHas($nestedRelation, $callback);
        });
    }

    public function orWhereHasRelation($relation, $nestedRelation, \Closure $callback = null)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $lastRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($lastRelation, $nestedRelation, $callback) {
                $query->whereHasRelation($lastRelation, $nestedRelation, $callback);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($nestedRelation, $callback) {
            $query->whereHas($nestedRelation, $callback);
        });
    }

    public function whereDoesntHaveRelation($relation, $nestedRelation, \Closure $callback = null)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $lastRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->whereHas($parentRelation, function ($query) use ($lastRelation, $nestedRelation, $callback) {
                $query->whereDoesntHaveRelation($lastRelation, $nestedRelation, $callback);
            });
        }

        return $this->whereHas($relation, function ($query) use ($nestedRelation, $callback) {
            $query->whereDoesntHave($nestedRelation, $callback);
        });
    }

    public function orWhereDoesntHaveRelation($relation, $nestedRelation, \Closure $callback = null)
    {
        if (str_contains($relation, '.')) {
            $parts = explode('.', $relation);
            $lastRelation = array_pop($parts);
            $parentRelation = implode('.', $parts);

            return $this->orWhereHas($parentRelation, function ($query) use ($lastRelation, $nestedRelation, $callback) {
                $query->whereDoesntHaveRelation($lastRelation, $nestedRelation, $callback);
            });
        }

        return $this->orWhereHas($relation, function ($query) use ($nestedRelation, $callback) {
            $query->whereDoesntHave($nestedRelation, $callback);
        });
    }

    private function _applyNestedRelation($relation, \Closure $callback = null, $boolean = 'AND', $existsType = 'EXISTS')
    {
        // Split the relation into parts
        $parts = explode(".", $relation);
        $currentModel = $this;
        $existsQuery = null;

        // Reset and initialize the database query
        $this->_database->reset_query();
        $subquery = $this->_database;

        $totalNested = count($parts) - 1;
        $totalNestedProcess = 0;
        $relationMainTable = '';
        $lastRelationModel = null;

        foreach ($parts as $method) {
            if (!method_exists($currentModel, $method)) {
                throw new \Exception("Relation method {$method} does not exist.");
            }

            $relationConfig = $currentModel->{$method}();

            if (!isset($relationConfig->relations)) {
                throw new \Exception("Invalid relation configuration.");
            }

            foreach ($relationConfig->relations as $modelName => $config) {
                if (!$this->load->is_model_loaded($modelName)) {
                    $this->load->model($modelName);
                }

                $relationModel = $this->{$modelName};
                $relationTable = $relationModel->table;

                $joinType = ($boolean === 'AND') ? 'INNER' : 'LEFT';

                if ($totalNestedProcess == 0) {
                    $relationMainTable = $relationTable;
                    switch ($config['type']) {
                        case 'hasMany':
                        case 'hasOne':
                            $subquery->where("{$relationTable}.{$config['foreignKey']} = {$this->table}.{$config['localKey']}");
                            break;
                        case 'belongsTo':
                            $subquery->where("{$relationTable}.{$config['ownerKey']} = {$this->table}.{$config['foreignKey']}");
                            break;
                    }
                } else {
                    switch ($config['type']) {
                        case 'hasMany':
                        case 'hasOne':
                            $this->_database->join(
                                "{$relationTable} AS {$relationTable}",
                                "{$relationTable}.{$config['foreignKey']} = {$currentModel->table}.{$config['localKey']}",
                                $joinType
                            );
                            break;
                        case 'belongsTo':
                            $this->_database->join(
                                "{$relationTable} AS {$relationTable}",
                                "{$relationTable}.{$config['ownerKey']} = {$currentModel->table}.{$config['foreignKey']}",
                                $joinType
                            );
                            break;
                    }
                }

                if ($relationModel->softDelete) {
                    switch ($relationModel->_trashed) {
                        case 'only':
                            $subquery->where($relationModel->table . '.' . $relationModel->deleted_at . ' IS NOT NULL');
                            break;
                        case 'without':
                            $subquery->where($relationModel->table . '.' . $relationModel->deleted_at . ' IS NULL');
                            break;
                        case 'with':
                            break;
                    }
                }

                $currentModel = $relationModel;

                if ($totalNestedProcess == $totalNested) {
                    $lastRelationModel = $relationModel;
                }
            }

            $totalNestedProcess++;
        }

        // Apply callback using the last relation model
        if ($callback && $lastRelationModel) {
            // Execute the modified callback with the subquery
            $callback($subquery);
        }

        $existsQuery = $subquery->select('1')->from($relationMainTable)->get_compiled_select();

        if (!empty($existsQuery)) {
            if ($boolean === 'AND') {
                $this->_database->where("{$existsType} ({$existsQuery})");
            } else {
                $this->_database->or_where("{$existsType} ({$existsQuery})");
            }
        }
    }

    private function _applySingleRelation($relation, \Closure $callback = null, $boolean = 'AND', $existsType = 'EXISTS')
    {
        if (!method_exists($this, $relation)) {
            throw new \Exception("Relation method {$relation} does not exist.");
        }

        $relationConfig = $this->{$relation}();

        if (!isset($relationConfig->relations)) {
            throw new \Exception("Invalid relation configuration.");
        }

        foreach ($relationConfig->relations as $modelName => $config) {
            if (!$this->load->is_model_loaded($modelName)) {
                $this->load->model($modelName);
            }

            $relationModel = $this->{$modelName};
            $relationTable = $relationModel->table;

            // Build the subquery
            $this->_database->reset_query();
            $subquery = $this->_database;

            if ($callback) {
                $callback($relationModel);
                $subquery = $relationModel->_database;
            }

            // Add the relation condition based on type
            switch ($config['type']) {
                case 'hasMany':
                case 'hasOne':
                    $subquery->where("{$relationTable}.{$config['foreignKey']} = {$this->table}.{$config['localKey']}");
                    break;
                case 'belongsTo':
                    $subquery->where("{$relationTable}.{$config['ownerKey']} = {$this->table}.{$config['foreignKey']}");
                    break;
            }

            if ($relationModel->softDelete) {
                switch ($relationModel->_trashed) {
                    case 'only':
                        $subquery->where($relationModel->table . '.' . $relationModel->deleted_at . ' IS NOT NULL');
                        break;
                    case 'without':
                        $subquery->where($relationModel->table . '.' . $relationModel->deleted_at . ' IS NULL');
                        break;
                    case 'with':
                        break;
                }
            }

            $existsQuery = $subquery->select('1')->from($relationTable)->get_compiled_select();

            if ($boolean === 'AND') {
                $this->_database->where("{$existsType} ({$existsQuery})");
            } else {
                $this->_database->or_where("{$existsType} ({$existsQuery})");
            }
        }
    }

    # RELATION (MODEL) SECTION

    /**
     * Define a one-to-many relationship
     */
    public function hasMany($modelName, $foreignKey, $localKey = null)
    {
        $this->relations[$modelName] = [
            'type' => 'hasMany',
            'model' => $modelName,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey ?: $this->primaryKey
        ];
        return $this;
    }

    /**
     * Define a one-to-one relationship
     */
    public function hasOne($modelName, $foreignKey, $localKey = null)
    {
        $this->relations[$modelName] = [
            'type' => 'hasOne',
            'model' => $modelName,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey ?: $this->primaryKey
        ];
        return $this;
    }

    /**
     * Define an inverse one-to-one or many relationship
     */
    public function belongsTo($modelName, $foreignKey, $ownerKey = null)
    {
        $this->relations[$modelName] = [
            'type' => 'belongsTo',
            'model' => $modelName,
            'foreignKey' => $foreignKey,
            'ownerKey' => $ownerKey ?: $this->primaryKey
        ];
        return $this;
    }

    # EAGER LOADING SECTION

    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $name => $constraints) {
            if (is_numeric($name)) {
                $name = $constraints;
                $constraints = null;
            }


            $columns = null;
            if (is_string($name) && strpos($name, ':') !== false) {
                list($name, $columnString) = explode(':', $name, 2);
                $name = trim($name);
                if (!empty($columnString)) {
                    $columns = array_map('trim', explode(',', $columnString));
                }
            }

            $this->eagerLoad[$name] = [
                'constraints' => $constraints,
                'columns' => $columns
            ];
        }

        return $this;
    }

    public function withAggregate($relations, $function, $column = '*', $alias = null)
    {
        $relationMap = [];

        // Process relations to standardize format
        if (is_string($relations)) {
            $relationMap[$relations] = null;
        } else if (is_array($relations)) {
            foreach ($relations as $key => $value) {
                if (is_numeric($key)) {
                    $relationMap[$value] = [
                        'callback' => null,
                        'alias' => null
                    ];
                } else if (is_string($value)) {
                    $relationMap[$key] = [
                        'callback' => null,
                        'alias' => $value
                    ];
                } else if (is_callable($value)) {
                    $relationMap[$key] = [
                        'callback' => $value,
                        'alias' => null
                    ];
                }

                // Handle "as" alias syntax: 'profile as payment_code_sum'
                if (is_string($key) && strpos($key, ' as ') !== false) {
                    list($relationName, $customAlias) = explode(' as ', $key);
                    $relationMap[$relationName] = [
                        'callback' => $value,
                        'alias' => $customAlias
                    ];
                    unset($relationMap[$key]);
                }
            }
        }

        // Apply each relation
        foreach ($relationMap as $relation => $config) {
            $callback = $config['callback'] ?? null;
            $customAlias = $config['alias'] ?? $alias;

            // Generate the alias name
            $aliasName = $customAlias ?: "{$relation}_{$function}" . ($column !== '*' ? "_{$column}" : '');

            // Remove any existing aggregate for this relation/function/column/alias
            $this->aggregateRelations = array_filter(
                $this->aggregateRelations,
                function ($aggregate) use ($relation, $function, $column, $aliasName) {
                    return !($aggregate['relation'] === $relation &&
                        $aggregate['type'] === $function &&
                        $aggregate['column'] === $column &&
                        (!isset($aggregate['alias']) || $aggregate['alias'] !== $aliasName));
                }
            );

            // Add new aggregate with alias and callback
            $this->_addAggregateRelation($function, $relation, $column, $aliasName, $callback);
        }

        return $this;
    }

    public function withCount($relations, $alias = null)
    {
        // If relations is a string and we have more arguments
        if (is_string($relations) && func_num_args() > 1) {
            $args = func_get_args();
            // Check if the second argument is a string (alias) or a closure
            if (is_string($args[1])) {
                $alias = $args[1];
            }
        }

        return $this->withAggregate($relations, 'count', '*', $alias);
    }

    public function withSum($relations, $column, $alias = null)
    {
        if (is_array($relations) && func_num_args() == 2) {
            return $this->withAggregate($relations, 'sum', $column);
        }

        return $this->withAggregate($relations, 'sum', $column, $alias);
    }

    public function withMin($relations, $column, $alias = null)
    {
        if (is_array($relations) && func_num_args() == 2) {
            return $this->withAggregate($relations, 'min', $column);
        }

        return $this->withAggregate($relations, 'min', $column, $alias);
    }

    public function withMax($relations, $column, $alias = null)
    {
        if (is_array($relations) && func_num_args() == 2) {
            return $this->withAggregate($relations, 'max', $column);
        }

        return $this->withAggregate($relations, 'max', $column, $alias);
    }

    public function withAvg($relations, $column, $alias = null)
    {
        // Handle case where relations is an array of format ['relation as alias' => function]
        if (is_array($relations) && func_num_args() == 2) {
            return $this->withAggregate($relations, 'avg', $column);
        }

        return $this->withAggregate($relations, 'avg', $column, $alias);
    }

    private function loadRelations($results)
    {
        if (empty($this->eagerLoad) || empty($results)) {
            return $results;
        }

        foreach ($this->eagerLoad as $relation => $config) {
            $relations = explode('.', $relation);
            $constraints = is_array($config) ? ($config['constraints'] ?? null) : $config;
            $columns = is_array($config) ? ($config['columns'] ?? null) : null;

            $this->loadNestedRelation($this, $results, $relations, $constraints, $columns);
        }

        return $results;
    }

    private function loadNestedRelation($currentInstance, &$results, $relations, $constraints = null, $columns = null)
    {
        try {
            if (count($relations) == 1) {
                $currentRelation = $relations[0];
                $relatedInstance = $currentInstance;
            } else {
                $newInstance = new $currentInstance;
                $setNewRelations = $newInstance->{$relations[0]}();
                $model = ucfirst(key($setNewRelations->relations));

                if (!$this->load->is_model_loaded($model))
                    $this->load->model($model);

                $relatedInstance = $this->{$model};
                $currentRelation = $relations[1];
            }

            if (!method_exists($relatedInstance, $currentRelation)) {
                throw new \Exception("Method {$currentRelation} does not exist in the model " . get_class($this));
            }

            $configRelation = (clone $relatedInstance)->{$currentRelation}();

            if (isset($configRelation->relations)) {
                foreach ($configRelation->relations as $modelName => $rels) {
                    $relationType = $rels['type'];
                    $foreignKey = $rels['foreignKey'];

                    if (!$this->load->is_model_loaded($modelName))
                        $this->load->model($modelName);

                    $relationInstance = $this->{$modelName};

                    // Apply constraint callback if provided
                    if ($constraints instanceof \Closure) {
                        $constraints($relationInstance);
                    }

                    // Apply column selection if provided
                    if (!empty($columns)) {
                        // Always include the foreign key and primary key for proper relationship mapping
                        $relationPrimaryKey = $relationInstance->primaryKey;
                        $requiredColumns = [$relationPrimaryKey];

                        // Add foreign key or owner key based on relation type
                        switch ($relationType) {
                            case 'hasMany':
                            case 'hasOne':
                                $requiredColumns[] = $foreignKey;
                                break;
                            case 'belongsTo':
                                $requiredColumns[] = $rels['ownerKey'];
                                break;
                        }

                        // Merge with requested columns, ensuring we include required keys
                        $selectedColumns = array_unique(array_merge($columns, $requiredColumns));
                        $relationInstance->select($relationInstance->table . '.' . implode(', ' . $relationInstance->table . '.', $selectedColumns));
                    }

                    switch ($relationType) {
                        case 'hasMany':
                        case 'hasOne':
                            $localKey = $rels['localKey'];
                            $parentIds = array_unique(array_filter(count($relations) > 1 ? $this->searchRelatedKeys($results, $relations[0] . '.' . $localKey) : array_column($results, $localKey)));
                            $relatedData = $this->_processQueryRelations($relationInstance, $foreignKey, $parentIds, 1000);
                            $this->_mergeDataRelations($results, $relatedData, $currentRelation, $localKey, $foreignKey, $relationType, count($relations) > 1 ? $relations[0] : null);
                            break;

                        case 'belongsTo':
                            $ownerKey = $rels['ownerKey'];
                            $foreignIds = array_unique(array_filter(count($relations) > 1 ? $this->searchRelatedKeys($results, $relations[0] . '.' . $foreignKey) : array_column($results, $foreignKey)));
                            $relatedData = $this->_processQueryRelations($relationInstance, $ownerKey, $foreignIds, 1000);
                            $this->_mergeDataRelations($results, $relatedData, $currentRelation, $foreignKey, $ownerKey, $relationType, count($relations) > 1 ? $relations[0] : null);
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Eager (loadNestedRelation) error: ' . $e->getMessage());
        }
    }

    private function _processQueryRelations($model, $column, $values, $chunkSize = 1000)
    {
        $result = [];

        foreach (array_chunk($values, $chunkSize) as $chunk) {
            $chunkResult = count($chunk) == 1
                ? (clone $model)->where($column, $chunk[0])->get()
                : (clone $model)->whereIn($column, $chunk)->get();

            array_push($result, ...$chunkResult);
        }

        return $result;
    }

    private function _mergeDataRelations(&$results, $relatedData, $relation, $localKey, $foreignKey, $type, $parentRelation = null)
    {
        $relatedDataMap = [];
        foreach ($relatedData as $item) {
            $relatedDataMap[$item[$foreignKey]][] = $item;
        }

        if (is_null($parentRelation)) {
            foreach ($results as &$result) {
                $key = $result[$localKey];
                if (isset($relatedDataMap[$key])) {
                    $result[$relation] = $type === 'hasOne' ? $relatedDataMap[$key][0] : $relatedDataMap[$key];
                } else {
                    $result[$relation] = $type === 'hasOne' ? null : [];
                }
            }
        } else {
            foreach ($results as &$result) {
                if (isset($result[$parentRelation])) {
                    foreach ($result[$parentRelation] as &$nestedResult) {
                        if (isset($nestedResult[$localKey])) {
                            $key = $nestedResult[$localKey];
                            if (isset($relatedDataMap[$key])) {
                                $nestedResult[$relation] = $type === 'hasOne' ? $relatedDataMap[$key][0] : $relatedDataMap[$key];
                            } else {
                                $nestedResult[$relation] = $type === 'hasOne' ? null : [];
                            }
                        } else {
                            $key = $result[$parentRelation][$localKey] ?? null;
                            if (!empty($key) && isset($relatedDataMap[$key])) {
                                $result[$parentRelation][$relation] = $type === 'hasOne' ? $relatedDataMap[$key][0] : $relatedDataMap[$key];
                            } else {
                                $result[$parentRelation][$relation] = null;
                            }
                        }
                    }
                }
            }
        }
    }

    private function hasAggregate($relation, $type, $column = '*')
    {
        foreach ($this->aggregateRelations as $aggregate) {
            if ($aggregate['relation'] === $relation && $aggregate['type'] === $type && $aggregate['column'] === $column) {
                return true;
            }
        }

        return false;
    }

    private function _addAggregateRelation($type, $relation, $column, $alias = null, $callback = null)
    {
        $parts = explode('.', $relation);
        $mainRelation = $parts[0];

        if (!method_exists($this, $mainRelation)) {
            throw new \Exception("Relation method {$mainRelation} does not exist.");
        }

        $this->aggregateRelations[] = [
            'type' => $type,
            'relation' => $relation,
            'column' => $column,
            'alias' => $alias,
            'callback' => $callback
        ];

        return $this;
    }

    private function _applyAggregates()
    {
        if (empty($this->aggregateRelations)) {
            return;
        }

        // Ensure the original table's columns are selected first
        if (strpos((clone $this->_database)->get_compiled_select('example_table', FALSE), 'SELECT *') !== false) {
            // If no columns are selected, apply the default selection
            $this->_database->select("{$this->table}.*");
        }

        // Prepare an array to store subqueries
        $subqueries = [];

        // Keep track of used relations to prevent duplicates
        $processedRelations = [];

        // Build each aggregate subquery
        foreach ($this->aggregateRelations as $aggregate) {
            // Get the alias name
            $aliasName = $aggregate['alias'] ?? "{$aggregate['relation']}_{$aggregate['type']}" .
                ($aggregate['column'] !== '*' ? "_{$aggregate['column']}" : '');

            // Skip if this exact relation has already been processed
            $relationKey = $aggregate['relation'] . '_' . $aggregate['type'] . '_' . $aggregate['column'] . '_' . $aliasName;
            if (isset($processedRelations[$relationKey])) {
                continue;
            }

            $parts = explode('.', $aggregate['relation']);
            $mainRelation = $parts[0];

            // Get the main relation configuration
            $relationConfig = $this->{$mainRelation}();

            foreach ($relationConfig->relations as $modelName => $config) {
                if (!$this->load->is_model_loaded($modelName)) {
                    $this->load->model($modelName);
                }

                $relationModel = $this->{$modelName};
                $relationTable = $relationModel->table;

                // Apply the callback to the relation model if specified
                if (isset($aggregate['callback']) && is_callable($aggregate['callback'])) {
                    $callbackModel = clone $relationModel;
                    $aggregate['callback']($callbackModel);

                    // Extract WHERE conditions from the callback query
                    $whereConditions = $this->_extractWhereConditions($callbackModel);
                } else {
                    $whereConditions = '';
                }

                switch ($config['type']) {
                    case 'hasMany':
                    case 'hasOne':
                        $localKey = $config['localKey'];
                        $foreignKey = $config['foreignKey'];

                        // Start building the subquery
                        $subquery = "SELECT {$aggregate['type']}(";

                        if (count($parts) > 1) {
                            // Handle nested relations for aggregates
                            $nestedSubquery = $this->_buildNestedAggregateSubquery(
                                $relationModel,
                                array_slice($parts, 1),
                                $aggregate['column'],
                                $aggregate['type']
                            );
                            $subquery .= $nestedSubquery;
                        } else {
                            // Simple column reference
                            $column = $aggregate['column'] === '*' ? '1' : "`{$relationTable}`.`{$aggregate['column']}`";
                            $subquery .= $column;
                        }

                        $subquery .= ") FROM `{$relationTable}` " .
                            "WHERE `{$relationTable}`.`{$foreignKey}` = `{$this->table}`.`{$localKey}`";

                        // Add callback conditions if they exist
                        if (!empty($whereConditions)) {
                            $subquery .= " AND {$whereConditions}";
                        }

                        // Add soft delete condition if needed
                        if ($relationModel->softDelete) {
                            switch ($relationModel->_trashed) {
                                case 'only':
                                    $subquery .= " AND `{$relationTable}`.`{$relationModel->deleted_at}` IS NOT NULL";
                                    break;
                                case 'without':
                                    $subquery .= " AND `{$relationTable}`.`{$relationModel->deleted_at}` IS NULL";
                                    break;
                            }
                        }

                        // Store the subquery in the array with the alias name
                        $subqueries[$aliasName] = $subquery;

                        // Mark this relation as processed
                        $processedRelations[$relationKey] = true;
                        break;

                    case 'belongsTo':
                        $ownerKey = $config['ownerKey'];
                        $foreignKey = $config['foreignKey'];

                        // Start building the subquery
                        $subquery = "SELECT {$aggregate['type']}(";

                        if (count($parts) > 1) {
                            // Handle nested relations for aggregates
                            $nestedSubquery = $this->_buildNestedAggregateSubquery(
                                $relationModel,
                                array_slice($parts, 1),
                                $aggregate['column'],
                                $aggregate['type']
                            );
                            $subquery .= $nestedSubquery;
                        } else {
                            // Simple column reference
                            $column = $aggregate['column'] === '*' ? '1' : "`{$relationTable}`.`{$aggregate['column']}`";
                            $subquery .= $column;
                        }

                        $subquery .= ") FROM `{$relationTable}` " .
                            "WHERE `{$relationTable}`.`{$ownerKey}` = `{$this->table}`.`{$foreignKey}`";

                        // Add callback conditions if they exist
                        if (!empty($whereConditions)) {
                            $subquery .= " AND {$whereConditions}";
                        }

                        // Add soft delete condition if needed
                        if ($relationModel->softDelete) {
                            switch ($relationModel->_trashed) {
                                case 'only':
                                    $subquery .= " AND `{$relationTable}`.`{$relationModel->deleted_at}` IS NOT NULL";
                                    break;
                                case 'without':
                                    $subquery .= " AND `{$relationTable}`.`{$relationModel->deleted_at}` IS NULL";
                                    break;
                            }
                        }

                        // Store the subquery in the array with the alias name
                        $subqueries[$aliasName] = $subquery;

                        // Mark this relation as processed
                        $processedRelations[$relationKey] = true;
                        break;
                }
            }
        }

        // Apply all prepared subqueries to the main query
        foreach ($subqueries as $aliasName => $subquery) {
            $this->_database->select("({$subquery}) as {$aliasName}");
        }
    }

    # Add helper method to extract WHERE conditions from a query
    private function _extractWhereConditions($model)
    {
        // Get the current query object
        $query = $model->_database;

        // Get the query string without SELECT/FROM parts
        $fullQuery = $query->get_compiled_select('dummy', false);

        // Extract just the WHERE part - this is a simplified approach
        if (preg_match('/WHERE\s+(.+?)(?:\s+ORDER|\s+LIMIT|\s+GROUP|\s+HAVING|\s*$)/is', $fullQuery, $matches)) {
            return $matches[1];
        }

        // If no WHERE clause found
        return '';
    }

    private function _buildNestedAggregateSubquery($model, $relationParts, $column, $aggregateType)
    {
        if (empty($relationParts)) {
            $tableAlias = $model->table;
            return $column === '*' ? '1' : "`{$tableAlias}`.`{$column}`";
        }

        $currentRelation = $relationParts[0];

        if (!method_exists($model, $currentRelation)) {
            throw new \Exception("Relation method {$currentRelation} does not exist on " . get_class($model));
        }

        $relationConfig = $model->{$currentRelation}();

        if (!isset($relationConfig->relations)) {
            throw new \Exception("Invalid relation configuration for {$currentRelation}");
        }

        foreach ($relationConfig->relations as $modelName => $config) {
            if (!$this->load->is_model_loaded($modelName)) {
                $this->load->model($modelName);
            }

            $relationModel = $this->{$modelName};
            $relationTable = $relationModel->table;

            switch ($config['type']) {
                case 'hasMany':
                case 'hasOne':
                    if (count($relationParts) > 1) {
                        // Still more nested relations to process
                        return $this->_buildNestedAggregateSubquery(
                            $relationModel,
                            array_slice($relationParts, 1),
                            $column,
                            $aggregateType
                        );
                    } else {
                        // We've reached the target relation
                        return $column === '*' ? '1' : "`{$relationTable}`.`{$column}`";
                    }

                case 'belongsTo':
                    if (count($relationParts) > 1) {
                        // Still more nested relations to process
                        return $this->_buildNestedAggregateSubquery(
                            $relationModel,
                            array_slice($relationParts, 1),
                            $column,
                            $aggregateType
                        );
                    } else {
                        // We've reached the target relation
                        return $column === '*' ? '1' : "`{$relationTable}`.`{$column}`";
                    }
            }
        }

        throw new \Exception("Could not build nested aggregate subquery for relation chain");
    }
}
