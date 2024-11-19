<?php

namespace App\Core\Traits;

trait EagerQuery
{
    protected $relations = [];
    protected $eagerLoad = [];

    # CONDITIONAL SECTION

    public function whereHas($relation, \Closure $callback = null)
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

            // Add exists clause
            $existsQuery = $subquery->from($relationTable)->get_compiled_select();
            $this->_database->where("EXISTS ({$existsQuery})");
        }

        return $this;
    }

    public function orWhereHas($relation, \Closure $callback = null)
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

            // Add exists clause with OR condition
            $existsQuery = $subquery->from($relationTable)->get_compiled_select();
            $this->_database->or_where("EXISTS ({$existsQuery})");
        }

        return $this;
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

            $this->eagerLoad[$name] = $constraints;
        }

        return $this;
    }

    public function withCount($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $relation) {
            $this->_addAggregateRelation('count', $relation, '*');
        }

        return $this;
    }

    public function withSum($relation, $column)
    {
        $this->_addAggregateRelation('sum', $relation, $column);
        return $this;
    }

    public function withMin($relation, $column)
    {
        $this->_addAggregateRelation('min', $relation, $column);
        return $this;
    }

    public function withMax($relation, $column)
    {
        $this->_addAggregateRelation('max', $relation, $column);
        return $this;
    }

    private function loadRelations($results)
    {
        if (empty($this->eagerLoad) || empty($results)) {
            return $results;
        }

        foreach ($this->eagerLoad as $relation => $constraints) {
            $relations = explode('.', $relation);
            $this->loadNestedRelation($this, $results, $relations, $constraints);
        }

        return $results;
    }

    private function loadNestedRelation($currentInstance, &$results, $relations, $constraints = null)
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

            $configRelation = $relatedInstance->{$currentRelation}();

            if (isset($configRelation->relations)) {
                foreach ($configRelation->relations as $modelName => $rels) {
                    $relationType = $rels['type'];
                    $foreignKey = $rels['foreignKey'];

                    if (!$this->load->is_model_loaded($modelName))
                        $this->load->model($modelName);

                    $relationInstance = $this->{$modelName};

                    if ($constraints instanceof \Closure) {
                        $constraints($relationInstance);
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
        $chunks = array_chunk($values, $chunkSize);
        $result = [];

        foreach ($chunks as $chunk) {
            $chunkResult = count($chunk) == 1
                ? $model->where($column, $chunk[0])->get()
                : $model->whereIn($column, $chunk)->get();

            $result = array_merge($result, $chunkResult);
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

    private function _addAggregateRelation($type, $relation, $column)
    {
        if (!method_exists($this, $relation)) {
            throw new \Exception("Relation method {$relation} does not exist.");
        }

        $relationConfig = $this->{$relation}();

        foreach ($relationConfig->relations as $modelName => $config) {
            if (!$this->load->is_model_loaded($modelName)) {
                $this->load->model($modelName);
            }

            $relationModel = $this->{$modelName};
            $foreignKey = $config['foreignKey'];

            switch ($config['type']) {
                case 'hasMany':
                case 'hasOne':
                    $localKey = $config['localKey'];
                    $aggregateColumn = $column === '*' ? '1' : "{$relationModel->table}.{$column}";

                    $this->_database->select("{$this->table}.*");
                    $this->_database->select("(
                        SELECT {$type}({$aggregateColumn})
                        FROM {$relationModel->table}
                        WHERE {$relationModel->table}.{$foreignKey} = {$this->table}.{$localKey}
                    ) as {$relation}_{$type}" . ($column !== '*' ? "_{$column}" : ''));
                    break;

                case 'belongsTo':
                    $ownerKey = $config['ownerKey'];
                    $aggregateColumn = $column === '*' ? '1' : "{$relationModel->table}.{$column}";

                    $this->_database->select("{$this->table}.*");
                    $this->_database->select("(
                        SELECT {$type}({$aggregateColumn})
                        FROM {$relationModel->table}
                        WHERE {$relationModel->table}.{$ownerKey} = {$this->table}.{$foreignKey}
                    ) as {$relation}_{$type}" . ($column !== '*' ? "_{$column}" : ''));
                    break;
            }
        }
    }
}
