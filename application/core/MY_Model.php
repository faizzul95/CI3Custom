<?php

defined('BASEPATH') or exit('No direct script access allowed');

use App\core\Traits\EagerQuery;
use App\core\Traits\PaginateQuery;

/**
 * MY_Model Class
 *
 * @category  Model
 * @Description  An extended model class for CodeIgniter 3 with advanced querying capabilities, relationship handling, and security features.
 * @author    Mohd Fahmy Izwan Zulkhafri <faizzul14@gmail.com>
 * @link      https://github.com/faizzul95/MY_Model
 */

class MY_Model extends CI_Model
{
    use EagerQuery, PaginateQuery;

    public $table;
    public $primaryKey = 'id';
    public $connection = 'default';

    public $db;
    private $_database;

    /**
     * @var null|array
     * Specifies fields to be protected from mass-assignment.
     * If set to null, it will be initialized as an array containing only the primary key.
     * If set as an array, it will retain its value without modifications.
     * Note: An empty array will not automatically include the primary key.
     */
    public $protected = null;

    /**
     * @var null|array
     * Specifies additional attributes to be appended to the model's array and JSON representations.
     * If set to null, it will be initialized as an empty array.
     * If set as an array, it will retain its value without modifications.
     */
    public $appends = null;

    /**
     * @var array|null
     * Specifies fields to be hidden from array and JSON representation.
     * If null, it will be initialized as an empty array when accessed.
     * If set as an array, it will contain the names of fields to be hidden.
     * Hidden fields are typically sensitive data like passwords or internal attributes.
     */
    public $hidden = null;

    /**
     * @var null|array
     * Sets fillable fields.
     * If value is set as null, the $fillable property will be set as an array with all the table fields (except the primary key) as elements.
     * If value is set as an array, there won't be any changes done to it (ie: no field of the table will be updated or inserted).
     */
    public $fillable = null;

    protected $returnType = 'array';
    protected $_secureOutput = false;
    protected $_secureOutputException = [];
    protected $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', '<>', 'LIKE', 'NOT LIKE'];

    public $timestamps = true;
    public $timestamps_format = 'Y-m-d H:i:s';
    public $timezone = 'Asia/Kuala_Lumpur';

    public $created_at = 'created_at';
    public $updated_at = 'updated_at';
    public $deleted_at = 'deleted_at';

    public $softDelete = false;
    private $_trashed = 'without';

    public $_validationRules = []; // will be used for both insert & update
    protected $_validationCustomize = []; // will be used to customize/add-on validation for insert & update
    public $_insertValidation = []; // will be used for insert only
    public $_updateValidation = []; // will be used for update only
    protected $_overrideValidation = []; // to override the validation for update & insert
    protected $_ignoreValidation = false; // will ignore the validation
    protected $_validationError = []; // used to store the validation error message
    public $_validationLang = 'english'; // used to set validation language for error message, default is english
    public $debug = false; // used to set debug mode

    protected $_indexString = null;
    protected $_indexType = 'USE INDEX';
    protected $_suggestIndexEnabled = false;

    public function __construct()
    {
        $this->db = $this->load->database($this->connection, TRUE);
        $this->_set_connection();
        $this->_set_timezone();
        $this->_fetch_table();
    }

    /**
     * Set the table name
     *
     * @param string $table Table name to be set
     * @return $this
     */
    public function table($table)
    {
        $this->table = trim($table);
        return $this;
    }

    /**
     * Select columns for the query (safe, Laravel-style)
     *
     * @param string|array $columns Columns to select
     * @return $this
     */
    public function select($columns = ['*'])
    {
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        $columns = array_map(function ($column) {
            $column = trim($column);
            // Skip prefixing for table.column, aliases, or SQL functions
            if (
                strpos($column, '.') !== false ||
                stripos($column, ' as ') !== false ||
                preg_match('/\w+\s*\(.*\)/i', $column) // handles nested functions like SUM(price * quantity)
            ) {
                return $column;
            }
            return "{$this->table}.{$column}";
        }, $columns);

        $this->_database->select(implode(', ', $columns));
        return $this;
    }

    /**
     * Select raw expression for the query (Laravel-style)
     *
     * @param string $expression Raw SQL string with optional bindings (?)
     * @param array $bindings Bindings to safely escape
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        // If bindings are provided, bind them safely using CodeIgniter's built-in methods
        if (!empty($bindings)) {
            $expression = vsprintf(str_replace('?', '%s', $expression), array_map(function ($value) {
                return $this->_database->escape(trim($value));
            }, $bindings));
        }

        $this->_database->select($expression, false); // false disables escaping
        return $this;
    }

    /**
     * Add a WHERE clause to the query
     *
     * @param string|array|Closure $column Column name, array of conditions, or Closure
     * @param mixed $operator Operator or value
     * @param mixed $value Value (if operator is provided)
     * @return $this
     */
    public function where($column, $operator = null, $value = null)
    {
        // If it's a Closure, we'll handle it separately
        if ($column instanceof Closure) {
            return $this->whereNested($column);
        }

        // If it's an array, we'll assume it's a key-value pair of conditions
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, $val);
            }
            return $this;
        }

        // If only two parameters are given, we'll assume it's column and value
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('where', $column, $value, $operator);
        return $this;
    }

    /**
     * Add an OR WHERE clause to the query
     *
     * @param string|array|Closure $column Column name, array of conditions, or Closure
     * @param mixed $operator Operator or value
     * @param mixed $value Value (if operator is provided)
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        // If it's a Closure, we'll handle it separately
        if ($column instanceof Closure) {
            return $this->whereNested($column);
        }

        // If it's an array, we'll assume it's a key-value pair of conditions
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->orWhere($key, $val);
            }
            return $this;
        }

        // If only two parameters are given, we'll assume it's column and value
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('or_where', $column, $value, $operator);
        return $this;
    }

    public function whereNull($column)
    {
        $this->_database->where($column . ' IS NULL');
        return $this;
    }

    public function orWhereNull($column)
    {
        $this->_database->or_where($column . ' IS NULL');
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->_database->where($column . ' IS NOT NULL');
        return $this;
    }

    public function orWhereNotNull($column)
    {
        $this->_database->or_where($column . ' IS NOT NULL');
        return $this;
    }

    public function whereExists(Closure $callback)
    {
        $subQuery = $this->forSubQuery($callback);
        $this->_database->where("EXISTS ($subQuery)", NULL, FALSE);
        return $this;
    }

    public function orWhereExists(Closure $callback)
    {
        $subQuery = $this->forSubQuery($callback);
        $this->_database->or_where("EXISTS ($subQuery)", NULL, FALSE);
        return $this;
    }

    public function whereNotExists(Closure $callback)
    {
        $subQuery = $this->forSubQuery($callback);
        $this->_database->where("NOT EXISTS ($subQuery)", NULL, FALSE);
        return $this;
    }

    public function orWhereNotExists(Closure $callback)
    {
        $subQuery = $this->forSubQuery($callback);
        $this->_database->or_where("NOT EXISTS ($subQuery)", NULL, FALSE);
        return $this;
    }

    public function whereColumn($first, $operator = null, $second = null)
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->_database->where("$first $operator $second", NULL, FALSE);
        return $this;
    }

    public function orWhereColumn($first, $operator = null, $second = null)
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->_database->or_where("$first $operator $second", NULL, FALSE);
        return $this;
    }

    public function whereNot($column, $operator = null, $value = null)
    {
        $this->where($column, $operator, $value)->where($column . ' IS NOT', null);
        return $this;
    }

    public function orWhereNot($column, $operator = null, $value = null)
    {
        $this->orWhere($column, $operator, $value)->orWhere($column . ' IS NOT', null);
        return $this;
    }

    public function whereJsonContains($column, $value)
    {
        $this->_database->where("JSON_CONTAINS($column, " . $this->escapeValue(json_encode($value)) . ")", NULL, FALSE);
        return $this;
    }

    public function orWhereJsonContains($column, $value)
    {
        $this->_database->or_where("JSON_CONTAINS($column, " . $this->escapeValue(json_encode($value)) . ")", NULL, FALSE);
        return $this;
    }

    # WHERE TIME, DATE, DAY, MONTH, YEAR SECTION

    public function whereTime($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('where', "TIME($column)", $value, $operator);
        return $this;
    }

    public function orWhereTime($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('or_where', "TIME($column)", $value, $operator);
        return $this;
    }

    public function whereDate($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('where', "DATE($column)", $value, $operator);
        return $this;
    }

    public function orWhereDate($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->applyCondition('or_where', "DATE($column)", $value, $operator);
        return $this;
    }

    public function whereDay($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateDayMonth($value);
        $this->applyCondition('where', "DAY($column)", $value, $operator);
        return $this;
    }

    public function orWhereDay($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateDayMonth($value);
        $this->applyCondition('or_where', "DAY($column)", $value, $operator);
        return $this;
    }

    public function whereYear($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateYear($value);
        $this->applyCondition('where', "YEAR($column)", $value, $operator);
        return $this;
    }

    public function orWhereYear($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateYear($value);
        $this->applyCondition('or_where', "YEAR($column)", $value, $operator);
        return $this;
    }

    public function whereMonth($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateDayMonth($value, true);
        $this->applyCondition('where', "MONTH($column)", $value, $operator);
        return $this;
    }

    public function orWhereMonth($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateDayMonth($value, true);
        $this->applyCondition('or_where', "MONTH($column)", $value, $operator);
        return $this;
    }

    public function whereIn($column, $values)
    {
        $this->_database->where_in($column, $values);
        return $this;
    }

    public function whereNotIn($column, $values)
    {
        $this->_database->where_not_in($column, $values);
        return $this;
    }

    public function orWhereIn($column, $values)
    {
        $this->_database->or_where_in($column, $values);
        return $this;
    }

    public function orWhereNotIn($column, $values)
    {
        $this->_database->or_where_not_in($column, $values);
        return $this;
    }

    public function whereBetween($column, $start, $end)
    {
        $this->_database->where("$column BETWEEN {$this->escapeValue($start)} AND {$this->escapeValue($end)}");
        return $this;
    }

    public function whereNotBetween($column, $start, $end)
    {
        $this->_database->where("$column NOT BETWEEN {$this->escapeValue($start)} AND {$this->escapeValue($end)}");
        return $this;
    }

    public function orWhereBetween($column, $start, $end)
    {
        $this->_database->or_where("$column BETWEEN {$this->escapeValue($start)} AND {$this->escapeValue($end)}");
        return $this;
    }

    public function orWhereNotBetween($column, $start, $end)
    {
        $this->_database->or_where("$column NOT BETWEEN {$this->escapeValue($start)} AND {$this->escapeValue($end)}");
        return $this;
    }

    public function when($condition, \Closure $callback, \Closure $default = null)
    {
        if ($condition) {
            return $callback($this, $condition) ?: $this;
        }

        if ($default) {
            return $default($this, $condition) ?: $this;
        }

        return $this;
    }

    /**
     * Execute a raw SQL query
     *
     * @param string $query Raw SQL query
     * @param array $binding Binding parameters
     * @return $this
     */
    public function rawQuery($query, $binding = [])
    {
        $query = $this->_database->compile_binds($query, $binding);
        return $this->_database->query($query);
    }

    public function join($table, $condition, $type = 'inner')
    {
        $this->_database->join($table, $condition, $type);
        return $this;
    }

    public function rightJoin($table, $condition)
    {
        $this->_database->join($table, $condition, 'right');
        return $this;
    }

    public function leftJoin($table, $condition)
    {
        $this->_database->join($table, $condition, 'left');
        return $this;
    }

    public function innerJoin($table, $condition)
    {
        $this->_database->join($table, $condition, 'inner');
        return $this;
    }

    public function outerJoin($table, $condition)
    {
        $this->_database->join($table, $condition, 'outer');
        return $this;
    }

    public function limit($limit)
    {
        $limit = $this->validateInteger($limit, 'Limit');
        $this->_database->limit($limit);
        return $this;
    }

    public function offset($offset)
    {
        $offset = $this->validateInteger($offset, 'Offset', false);
        $this->_database->offset($offset);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->_database->order_by($column, strtoupper($direction));
        return $this;
    }

    public function groupBy($column)
    {
        $this->_database->group_by($column);
        return $this;
    }

    public function groupByRaw($expression)
    {
        $this->_database->group_by($expression, FALSE);
        return $this;
    }

    public function having($column, $value, $operator = '=')
    {
        $this->_database->having("$column $operator", $value);
        return $this;
    }

    public function havingRaw($condition)
    {
        $this->_database->having($condition, NULL, FALSE);
        return $this;
    }

    /**
     * Sorts the collection by a given key or callback
     * Similar to Laravel's sortBy method
     * Supports dot notation for accessing relationship values
     * 
     * @param mixed $key The key to sort by (supports dot notation) or a callback function
     * @param int $direction SORT_ASC or SORT_DESC
     * @param int $sortFlags The sort flags (PHP sort flags)
     * @return array Sorted results
     */
    public function sortBy($key, $direction = SORT_ASC, $sortFlags = SORT_REGULAR)
    {
        try {
            if (empty($key) && !is_callable($key)) {
                throw new Exception('The key or callback is required.');
            }

            $results = $this->get();

            if (empty($results)) {
                return $results;
            }

            // Extract sort values
            $sortValues = [];
            foreach ($results as $index => $item) {
                // Handle callback function
                if (is_callable($key)) {
                    $sortValues[$index] = call_user_func($key, $item);
                } else {
                    $sortValues[$index] = $this->_getValueUsingDotNotation($item, $key);
                }

                // Handle null values (place them at the beginning for asc, end for desc)
                if ($sortValues[$index] === null) {
                    $sortValues[$index] = ($direction === SORT_ASC) ? PHP_INT_MIN : PHP_INT_MAX;
                }
            }

            // Sort the array
            if ($direction === SORT_ASC) {
                asort($sortValues, $sortFlags);
            } else {
                arsort($sortValues, $sortFlags);
            }

            // Reorder the results based on the sorted keys
            $sortedResults = [];
            foreach (array_keys($sortValues) as $index) {
                $sortedResults[] = $results[$index];
            }

            return $sortedResults;
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'sortBy error: ' . $e->getMessage());
            throw $e; // Re-throw the exception after cleanup
        }
    }

    /**
     * Sort by multiple columns
     * 
     * @param array $criteria Array of sorting criteria [['column', 'direction'], ['column2', 'direction']]
     * @return array Sorted results
     */
    public function sortByMultiple($criteria)
    {
        try {
            if (empty($criteria)) {
                throw new Exception('Sorting criteria are required.');
            }

            if (!is_array($criteria)) {
                throw new Exception('Sorting criteria are required as array.');
            }

            $results = $this->get();

            if (empty($results)) {
                return $results;
            }

            return $this->_multiSort($results, $criteria);
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'sortByMultiple error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function exists()
    {
        $count = $this->count();
        return $count > 0;
    }

    public function doesntExist()
    {
        return !$this->exists();
    }

    /**
     * Filters the results using a callback
     * Similar to Laravel's filter() method
     * 
     * @param callable $callback The callback to filter results
     * @return array Filtered results
     */
    public function filter(callable $callback)
    {
        $results = $this->get();
        $filtered = [];

        foreach ($results as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                $filtered[$key] = $value;
            }
        }

        return array_values($filtered);
    }

    /**
     * Retrieves results in chunks, yielding each record one by one.
     * Similar to Laravel's cursor() method, this function processes large datasets
     * in a memory-efficient manner by fetching and yielding records one at a time.
     *
     * @param int $chunkSize Optional. The number of records to load in each database query. Default: 500
     * @return Generator A generator that yields results
     */
    public function cursor($chunkSize = 500)
    {
        // Clone the original database state
        $originalState = $this->_cloneDatabaseSettings();
        $this->_database = clone $originalState['db'];

        // Check if primary key is indexed
        $isIndexed = $this->isColumnIndexed($originalState['primaryKey'], $originalState['table']);

        if ($this->debug) {
            log_message('debug', "Cursor using " . ($isIndexed ? "SEEK-based ✅" : "OFFSET-based ❌") . " pagination.");
        }

        // Initialize pagination variables
        $offset = 0;
        $lastId = 0;

        while (true) {

            // Restore the original query conditions
            $this->_database = clone $originalState['db'];

            // Restore model state
            $this->connection = $originalState['connection'];
            $this->table = $originalState['table'];
            $this->primaryKey = $originalState['primaryKey'];
            $this->relations = $originalState['relations'] ?? [];
            $this->eagerLoad = $originalState['eagerLoad'] ?? [];
            $this->returnType = $originalState['returnType'];
            $this->_paginateColumn = $originalState['_paginateColumn'] ?? [];
            $this->_indexString = $originalState['index'] ?? null;
            $this->_indexType = $originalState['indexType'] ?? 'USE INDEX';

            // Log query execution start time
            $startTime = microtime(true);

            // Apply SEEK or OFFSET pagination
            if ($isIndexed) {
                $this->where($this->primaryKey, '>', $lastId)->orderBy($this->primaryKey, 'ASC');
            } else {
                $this->offset($offset);
            }

            // Fetch results
            $results = $this->limit($chunkSize)->get();

            // Log query execution time
            $executionTime = microtime(true) - $startTime;
            if ($this->debug) {
                log_message('debug', "Cursor - " . ($isIndexed ? "SEEK" : "OFFSET") . " Query Execution Time: {$executionTime}s for chunk.");
            }

            // Break if no more results
            if (empty($results)) break;

            foreach ($results as $result) {
                yield $result;
                if ($isIndexed) {
                    $lastId = $result[$this->primaryKey]; // Update last processed ID for SEEK pagination
                }
            }

            // Update offset only if using OFFSET pagination
            if (!$isIndexed) {
                $offset += $chunkSize;
            }

            // Memory cleanup
            unset($results);
            if (function_exists('gc_collect_cycles')) gc_collect_cycles();

            usleep(1500);
        }

        // Reset internal properties
        $this->resetQuery();
    }

    /**
     * Creates a lazy-loaded collection that efficiently processes large datasets with minimal memory usage.
     * 
     * This method returns a LazyCollection instance which loads data in small chunks only when needed,
     * allowing for memory-efficient processing of large datasets. The LazyCollection implements Iterator
     * and Countable interfaces, providing a fluent interface similar to Laravel's collections.
     * 
     * @param int $chunkSize Optional. The number of records to load in each database query. Default: 500
     * @return LazyCollection Returns a LazyCollection instance that lazily loads query results
     * @throws Exception If there's an error creating the lazy collection
     * 
     * @example
     * // Basic usage with default chunk size
     * $users = $this->User_model->where('status', 'active')->lazy();
     * 
     * // Custom chunk size for memory optimization
     * $users = $this->User_model->where('status', 'active')->lazy(50);
     * 
     * // Chain collection methods for data processing
     * $usernames = $this->User_model->lazy()
     *     ->filter(function($user) { return $user['age'] >= 18; })
     *     ->map(function($user) { return $user['username']; })
     *     ->all();
     * 
     * // Process large datasets in a memory-efficient way
     * foreach ($this->User_model->lazy() as $user) {
     *     // Each iteration only loads data when needed
     * }
     */
    public function lazy($chunkSize = 500)
    {
        try {
            $model = $this;

            // Check if primary key is indexed
            $isIndexed = $this->isColumnIndexed($this->primaryKey, $this->table);

            if ($this->debug) {
                log_message('debug', "Lazy Collection using " . ($isIndexed ? "SEEK-based ✅" : "OFFSET-based ❌") . " pagination.");
            }

            // Data source function
            $source = function ($size, $offset) use ($model, $isIndexed) {
                // Clone the model to keep the query intact
                $clonedModel = clone $model;

                // Log start time
                $startTime = microtime(true);

                // Choose pagination strategy
                if ($isIndexed) {
                    if ($offset == 0) {
                        $lastId = 0;
                    } else {
                        $lastId = $offset;
                    }

                    if ($this->debug) {
                        log_message('debug', "Lazy Collection query for last id : {$lastId}");
                    }

                    $clonedModel->limit($size)->where("{$clonedModel->primaryKey}", ">", $lastId)->orderBy($clonedModel->primaryKey, 'ASC');
                } else {
                    $clonedModel->limit($size)->offset($offset);
                }

                // Execute the query
                $results = $clonedModel->get();

                // Log query execution time
                $executionTime = microtime(true) - $startTime;

                if ($clonedModel->debug) {
                    log_message('debug', 'Lazy - ' . ($isIndexed ? "SEEK" : "OFFSET") . " Query Execution Time: {$executionTime}s for chunk.");
                }

                if (empty($results)) return [];

                return is_array($results) ? $results : [$results];
            };

            // Create LazyCollection
            $collection = new LazyCollection($source);
            $collection->setChunkSize($chunkSize);

            if (function_exists('gc_collect_cycles')) gc_collect_cycles();

            return $collection;
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'LazyCollection error: ' . $e->getMessage());
            throw new Exception('Failed to create lazy collection: ' . $e->getMessage(), 0, $e);
        }
    }

    public function chunk($size, callable $callback)
    {
        // Store the original query state
        $originalState = $this->_cloneDatabaseSettings();

        // Initialize pagination variables
        $offset = 0;
        $lastId = 0;

        // Check if primary key is indexed
        $isIndexed = $this->isColumnIndexed($originalState['primaryKey'], $originalState['table']);

        if ($this->debug) {
            log_message('debug', "Chunk using " . ($isIndexed ? "SEEK-based ✅" : "OFFSET-based ❌") . " pagination.");
        }

        while (true) {
            // Restore the original query conditions by cloning
            $this->_database = clone $originalState['db'];

            // Restore the original model state
            $this->connection = $originalState['connection'];
            $this->table = $originalState['table'];
            $this->primaryKey = $originalState['primaryKey'];
            $this->relations = $originalState['relations'] ?? [];
            $this->eagerLoad = $originalState['eagerLoad'] ?? [];
            $this->returnType = $originalState['returnType'];
            $this->_paginateColumn = $originalState['_paginateColumn'] ?? [];
            $this->_indexString = $originalState['index'] ?? null;
            $this->_indexType = $originalState['indexType'] ?? 'USE INDEX';

            // Log query execution start time
            $startTime = microtime(true);

            // Apply SEEK or OFFSET pagination
            if ($isIndexed) {
                $this->where($this->primaryKey, '>', $lastId)->orderBy($this->primaryKey, 'ASC');
            } else {
                $this->offset($offset);
            }

            // Fetch results
            $results = $this->limit($size)->get();

            // Log query execution time
            $executionTime = microtime(true) - $startTime;
            if ($this->debug) {
                log_message('debug', "Chunk - " . ($isIndexed ? "SEEK" : "OFFSET") . " Query Execution Time: {$executionTime}s for chunk.");
            }

            if (empty($results)) {
                break;
            }

            if (call_user_func($callback, $results) === false) {
                break;
            }

            // Update lastId for seek-based pagination
            if ($isIndexed) {
                $lastId = end($results)[$this->primaryKey];  // Get the last item's primary key
            } else {
                $offset += $size;
            }

            // Clear the results to free memory
            unset($results);
        }

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Reset internal properties for next query
        $this->resetQuery();

        return $this;
    }

    public function count()
    {
        $this->_withTrashQueryFilter();
        $query = $this->_database->count_all_results($this->getTableWithIndex());
        $this->resetQuery();
        return $query;
    }

    public function toSql()
    {
        $this->_withTrashQueryFilter();
        $this->_applyAggregates();

        $query = $this->_database->get_compiled_select($this->getTableWithIndex());
        $this->resetQuery();
        return $query;
    }

    public function toSqlPatch($data = null, $id = [])
    {
        if (!empty($data)) {

            $data = $this->filterData($data);

            if ($this->timestamps) {
                $this->_set_timezone();
                $data[$this->updated_at] = date($this->timestamps_format);
            }

            $this->_database->set($data);
        }

        if ($id !== null) {
            $this->_database->where($this->primaryKey, $id);
        }

        $query = $this->_database->get_compiled_update($this->table, false);
        $this->resetQuery();
        return $query;
    }

    public function toSqlCreate($data = [])
    {
        if (!empty($data)) {

            $data = $this->filterData($data);

            if ($this->timestamps) {
                $this->_set_timezone();
                $data[$this->created_at] = date($this->timestamps_format);
            }

            $this->_database->set($data);
        }

        $query = $this->_database->get_compiled_insert($this->table, false);
        $this->resetQuery();
        return $query;
    }

    public function toSqlDestroy($id = null)
    {
        if ($this->softDelete) {
            $this->_set_timezone();
            $data[$this->deleted_at] = date($this->timestamps_format);
            $this->_database->set($data);
            if ($id !== null) {
                $this->_database->where($this->primaryKey, $id);
            }

            $query = $this->_database->get_compiled_update($this->table, false);
        } else {
            if ($id !== null) {
                $this->_database->where($this->primaryKey, $id);
            }

            $query = $this->_database->get_compiled_delete($this->table, false);
        }

        $this->resetQuery();
        return $query;
    }

    /**
     * Get an array of a single column's values from the results
     * Similar to Laravel's pluck() method
     * Supports dot notation for accessing relationship values
     * 
     * @param string $column The column to retrieve values from (supports dot notation)
     * @param string|null $key Optional key column to use as array keys (supports dot notation)
     * @return array An array of values or key-value pairs
     */
    public function pluck($column, $key = null)
    {
        try {
            if (empty($column)) {
                throw new Exception('The column key is required.');
            }

            $results = $this->get();
            $values = [];

            if (empty($results)) {
                return $values;
            }

            foreach ($results as $result) {
                // Get column value, supporting dot notation for relations
                $columnValue = $this->_getValueUsingDotNotation($result, $column);

                if ($key !== null) {
                    // Get key value, supporting dot notation for relations
                    $keyValue = $this->_getValueUsingDotNotation($result, $key);

                    if ($keyValue !== null) {
                        $values[$keyValue] = $columnValue;
                    } else {
                        $values[] = $columnValue;
                    }
                } else {
                    $values[] = $columnValue;
                }
            }

            return $values;
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'Pluck error: ' . $e->getMessage());
            throw $e; // Re-throw the exception after cleanup
        }
    }

    /**
     * Searches the collection for a given value or condition
     * Similar to Laravel's contains() method
     * Supports dot notation for accessing relationship values
     * Supports operator comparisons (=, <, >, <=, >=, !=)
     * Uses lazy loading for memory efficiency with large datasets
     * 
     * @param string|callable $key Column, value, or callback
     * @param string|mixed $operator Comparison operator or value
     * @param mixed $value Value to compare against (optional)
     * @return bool True if found
     */
    public function contains($key, $operator = null, $value = null)
    {
        try {
            // Determine if we're doing a direct value check or key-value comparison
            if (func_num_args() === 2) {
                $value = $operator;
                $operator = '=';
            }

            // Validate operator if we're using the key-operator-value syntax
            if (func_num_args() > 1 && !is_callable($key)) {
                $validOperators = ['=', '==', '===', '!=', '!==', '<', '>', '<=', '>=', '<>'];
                if (!in_array($operator, $validOperators)) {
                    throw new Exception('Invalid operator. Supported operators: =, ==, ===, !=, !==, <, >, <=, >=, <>');
                }
            }

            // Use lazy loading to process results one chunk at a time
            $chunkSize = 1000;
            $resultsGenerator = $this->lazy($chunkSize);

            // This is a simple value check (contains(5))
            if (func_num_args() === 1 && !is_callable($key)) {
                foreach ($resultsGenerator as $item) {
                    if (is_array($item) && in_array($key, $item, true)) {
                        return true;
                    } elseif (is_object($item) && in_array($key, get_object_vars($item), true)) {
                        return true;
                    } elseif ($item === $key) {
                        return true;
                    }
                }
                return false;
            }

            // Case 1: Callback function
            if (is_callable($key)) {
                foreach ($resultsGenerator as $k => $item) {
                    if (call_user_func($key, $item, $k)) {
                        return true;
                    }
                }
                return false;
            }

            // Case 2: Key-operator-value comparison
            foreach ($resultsGenerator as $item) {
                $itemValue = $this->_getValueUsingDotNotation($item, $key);

                switch ($operator) {
                    case '=':
                    case '==':
                        if ($itemValue == $value) return true;
                        break;
                    case '===':
                        if ($itemValue === $value) return true;
                        break;
                    case '!=':
                    case '<>':
                        if ($itemValue != $value) return true;
                        break;
                    case '!==':
                        if ($itemValue !== $value) return true;
                        break;
                    case '<':
                        if ($itemValue < $value) return true;
                        break;
                    case '>':
                        if ($itemValue > $value) return true;
                        break;
                    case '<=':
                        if ($itemValue <= $value) return true;
                        break;
                    case '>=':
                        if ($itemValue >= $value) return true;
                        break;
                }
            }

            return false;
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'contains error: ' . $e->getMessage());
            throw $e; // Re-throw the exception after cleanup
        }
    }

    /**
     * Get the results of the query
     *
     * @return array|object|json Results based on returnType
     */
    public function get()
    {
        try {
            $this->_withTrashQueryFilter();
            $this->_applyAggregates();

            // Execute Query
            $query = $this->_database->get($this->getTableWithIndex());

            // Log query performance if debug is enabled
            if ($this->debug) {
                $this->_logQueryPerformance($this->_database->last_query());
            }

            // Convert to Array
            $result = $query->result_array();

            // Free result to reduce memory usage
            if (isset($query) && method_exists($query, 'free_result')) {
                $query->free_result();
                unset($query);
            }

            if (!empty($result)) {
                $result = $this->loadRelations($result);

                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            $formattedResult = $this->formatResult($result);
            $this->resetQuery();

            return $formattedResult;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception after cleanup
        }
    }

    /**
     * Fetch a single row from the query results
     *
     * @return array|object|json Result based on returnType
     */
    public function fetch()
    {
        try {
            $this->_withTrashQueryFilter();
            $this->_applyAggregates();

            // Execute Query
            $query = $this->_database->get($this->getTableWithIndex());

            // Log query performance if debug is enabled
            if ($this->debug) {
                $this->_logQueryPerformance($this->_database->last_query());
            }

            // Convert to Array
            $result = $query->row_array();

            // Free result to reduce memory usage
            if (isset($query) && method_exists($query, 'free_result')) {
                $query->free_result();
                unset($query);
            }

            if (!empty($result)) {
                $result = $this->loadRelations([$result]);

                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }

            $formattedResult = $this->formatResult($result[0] ?? NULL);
            $this->resetQuery();

            return $formattedResult;
        } catch (Exception $e) {
            throw $e; // Re-throw the exception after cleanup
        }
    }

    public function first()
    {
        $this->orderBy($this->primaryKey, 'ASC');
        $this->limit(1);
        return $this->fetch();
    }

    public function last()
    {
        $this->orderBy($this->primaryKey, 'DESC');
        $this->limit(1);
        return $this->fetch();
    }

    public function find($id)
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    # SOFT DELETE QUERY SECTION

    public function withTrashed()
    {
        $this->_trashed = 'with';
        return $this;
    }

    public function onlyTrashed()
    {
        $this->_trashed = 'only';
        return $this;
    }

    private function _withTrashQueryFilter()
    {
        if ($this->softDelete) {
            switch ($this->_trashed) {
                case 'only':
                    $this->whereNotNull($this->table . '.' . $this->deleted_at);
                    break;
                case 'without':
                    $this->whereNull($this->table . '.' . $this->deleted_at);
                    break;
                case 'with':
                    break;
            }
        }
    }

    # CRUD SECTION

    /**
     * Ignore validation rules temporarily
     *
     * @return $this
     */
    public function skipValidation()
    {
        $this->_ignoreValidation = true;
        return $this;
    }

    /**
     * Set override validation rules, use for both insert AND update
     *
     * @param array $validation This will override all the validation set in the model.
     * @return $this
     */
    public function setValidationRules($validation)
    {
        if (!is_array($validation)) {
            throw new Exception('Validation rules must be an array.');
        }

        $this->_overrideValidation = $validation;
        return $this;
    }

    /**
     * Set custom validation rules, use for both insert AND update
     *
     * @param array $validation This will set the custom validation or the addition rules.
     * @return $this
     */
    public function setCustomValidationRules($validation)
    {
        if (!is_array($validation)) {
            throw new Exception('Additional validation rules must be an array.');
        }

        if (empty($this->_validationRules) && empty($this->_insertValidation) && empty($this->_updateValidation)) {
            throw new Exception('No validation rules found. Please set the validation in model first.');
        }

        $this->_validationCustomize = $validation;
        return $this;
    }

    /**
     * Set custom update validation rules for the insert/create, set $updateRules to true to replace existing key with the new rules.
     *
     * @param array $validation This will set the custom validation or the addition rules.
     * @param boolean $updateRules Indicate updating validation rules from the existing rules.
     * @return $this
     */
    public function setCreateValidationRules($validation, $updateRules = false)
    {
        if (!is_array($validation)) {
            throw new Exception('Custom create/insert validation rules must be an array.');
        }

        if ($updateRules) {
            $existingValidation = !empty($this->_insertValidation) ? $this->_insertValidation : $this->_validationRules;
            $validation = array_merge($existingValidation, $validation);
        }

        $this->_insertValidation = $validation;

        return $this;
    }

    /**
     * Set custom update validation rules for the update/patch, set $updateRules to true to replace existing key with the new rules.
     *
     * @param array $validation This will set the custom validation or the addition rules.
     * @param boolean $updateRules Indicate updating validation rules from the existing rules.
     * @return $this
     */
    public function setPatchValidationRules($validation, $updateRules = false)
    {
        if (!is_array($validation)) {
            throw new Exception('Custom patch/update validation rules must be an array.');
        }

        if ($updateRules) {
            $existingValidation = !empty($this->_updateValidation) ? $this->_updateValidation : $this->_validationRules;
            $validation = array_merge($existingValidation, $validation);
        }

        $this->_updateValidation = $validation;

        return $this;
    }

    /**
     * Insert a new record or update an existing one
     *
     * @param array $conditions The conditions to search for
     * @param array $values The values to update or insert
     * @return array Response with status code, data, action, and primary key
     */
    public function insertOrUpdate($conditions = [], $values = [])
    {
        try {
            if ($this->_isMultidimensional($values)) {
                throw new Exception('This insertOrUpdate method is not designed for batch operations.');
            }

            // Merge $attributes and $values
            $data = array_merge($conditions, $values);

            // Check if a record exists with the given attributes
            $existingRecord = get_instance()->db->select($this->primaryKey)->from($this->getTableWithIndex())->where($conditions)->limit(1)->get()->row_array();

            if (!empty($existingRecord)) {
                // If record exists, update it
                return $this->patch($data, $existingRecord[$this->primaryKey]);
            }

            // If record doesn't exist, create it
            return $this->create($data);
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'insertOrUpdate error: ' . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to insert new data',
                'action' => 'create',
            ];
        }
    }

    /**
     * Create a new record
     *
     * @param array $data Data to insert
     * @return array Response with status code, data, action, and primary key
     */
    public function create($data)
    {
        try {
            if (empty($data)) {
                throw new Exception('Please provide data to insert.');
            }

            if ($this->_isMultidimensional($data)) {
                throw new Exception('This create method is not designed for batch operations. Please use batchCreate() for batch inserts.');
            }

            $data = $this->filterData($data);
            $validationRules = !empty($this->_insertValidation) ? $this->_insertValidation : $this->_validationRules;

            if ($this->_runValidation($data, $validationRules, 'create')) {

                if ($this->timestamps) {
                    $this->_set_timezone();
                    $data[$this->created_at] = date($this->timestamps_format);
                }

                $success = $this->_database->insert($this->table, $data);

                if (!is_array($success) && !$success) {
                    throw new Exception('Failed to insert record');
                }

                $insertId = is_array($success) && isset($success['id']) ? $success['id'] : $this->_database->insert_id();

                $this->resetQuery();

                return [
                    'code' => 201,
                    $this->primaryKey => $insertId,
                    'data' => $data,
                    'message' => 'Inserted successfully',
                    'action' => 'create',
                ];
            } else {
                return $this->_validationError;
            }
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'Create error: ' . $e->getMessage());
            return [
                'code' => 500,
                'error' => $e->getMessage(),
                'message' => 'Failed to insert new data',
                'action' => 'create',
            ];
        }
    }

    /**
     * Create multiple records in a batch.
     *
     * @param array $data Array of data to create, where each item represents a record.
     * @return array Response with status code, message, and action.
     */
    public function batchCreate($data)
    {
        try {

            if (empty($data)) {
                throw new Exception('Please provide data to insert.');
            }

            if (!$this->_isMultidimensional($data)) {
                throw new Exception('This batchCreate method is designed for batch operations. Please use create() for single insert operation.');
            }

            $validationRules = !empty($this->_insertValidation) ? $this->_insertValidation : $this->_validationRules;

            // Prepare data for batch insert
            $batchData = [];
            foreach ($data as $items) {
                $item = $this->filterData($items);
                if ($this->_runValidation($item, $validationRules, 'create')) {
                    if ($this->timestamps) {
                        $this->_set_timezone();
                        $item[$this->created_at] = date($this->timestamps_format);
                    }
                    $batchData[] = $item;
                } else {
                    return $this->_validationError;
                }
            }

            if (empty($batchData)) {
                throw new Exception('No records to insert.');
            }

            $this->_database->trans_begin(); // Begin a transaction

            // Perform batch insert
            $success = $this->_database->insert_batch($this->table, $batchData);

            if (!$success || $this->_database->trans_status() === FALSE) {
                throw new Exception('Failed to insert records');
            }

            $lastInsertId = $this->_database->insert_id();
            $this->resetQuery();

            $this->_database->trans_commit();

            return [
                'code' => 200,
                'id' => $lastInsertId,
                'data' => $batchData,
                'message' => 'Batch creation successful',
                'action' => 'create'
            ];
        } catch (Exception $e) {
            $this->_database->trans_rollback();
            if ($this->debug) log_message('error', "Batch creation error in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to create data',
                'action' => 'create'
            ];
        }
    }

    /**
     * Update an existing record
     *
     * @param array $data Data to update
     * @param mixed $id ID of the record to update
     * @return array Response with status code, data, action, and primary key
     */
    public function patch($data, $id = NULL)
    {
        try {
            if (empty($data)) {
                throw new Exception('Please provide data to update.');
            }

            if ($this->_isMultidimensional($data)) {
                throw new Exception('This method is not designed for batch operations. Please use batchPatch() for batch updates.');
            }

            $data = $this->filterData($data);
            $validationRules = !empty($this->_updateValidation) ? $this->_updateValidation : $this->_validationRules;

            if ($this->_runValidation($data, $validationRules, 'update')) {

                if (is_null($id)) {
                    throw new Exception('Please provide id to update.');
                }

                if ($this->timestamps) {
                    $this->_set_timezone();
                    $data[$this->updated_at] = date($this->timestamps_format);
                }

                $success = $this->_database->where($this->primaryKey, $id)->update($this->table, $data);

                if (!$success) {
                    throw new Exception('Failed to update record');
                }

                $this->resetQuery();

                return [
                    'code' => 200,
                    $this->primaryKey => $id,
                    'data' => $data,
                    'message' => 'Updated successfully',
                    'action' => 'update'
                ];
            } else {
                return $this->_validationError;
            }
        } catch (Exception $e) {
            if ($this->debug) log_message('error', "Update error for id ({$id}) in table {$this->table}: "  . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to update data',
                'action' => 'update'
            ];
        }
    }

    /**
     * Update all/specific existing record based on the conditions
     *
     * @param array $data Data to update
     * @return array Response with status code, data, action, and primary key
     */
    public function patchAll($data)
    {
        try {
            $dataQuery = $this->get();

            if (!$dataQuery) {
                throw new Exception('No record found');
            }

            $updateData = [];
            foreach ($dataQuery as $dq) {
                $data[$this->primaryKey] = $dq[$this->primaryKey];
                $updateData[] = $data;
            }

            return $this->batchPatch($updateData, $this->primaryKey);
        } catch (Exception $e) {
            if ($this->debug) log_message('error', "Update error for patchAll: "  . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to update data',
                'action' => 'update'
            ];
        }
    }

    /**
     * Update multiple records in a batch.
     *
     * @param array $data Array of data to update, where each item represents a record.
     * @param string|null $customField Optional custom field to use as the update key (default is primary key).
     * @return array Response with status code, message, and action.
     */
    public function batchPatch($data, $customField = NULL)
    {
        try {

            if (empty($data)) {
                throw new Exception('Please provide data to update.');
            }

            if (!$this->_isMultidimensional($data)) {
                throw new Exception('This method is designed for batch operations. Please use patch() for single update operation.');
            }

            $validationRules = !empty($this->_updateValidation) ? $this->_updateValidation : $this->_validationRules;
            $keyColumn = empty($customField) ? $this->primaryKey : $customField;

            // Prepare data for batch update
            $batchData = [];
            foreach ($data as $items) {
                $item = $this->filterData($items, $keyColumn);
                if ($this->_runValidation($item, $validationRules, 'update')) {

                    if (!isset($item[$keyColumn]) || empty($item[$keyColumn])) {
                        continue; // skip the item
                    }

                    if ($this->timestamps) {
                        $this->_set_timezone();
                        $item[$this->updated_at] = date($this->timestamps_format);
                    }

                    $batchData[] = $item;
                } else {
                    return $this->_validationError;
                }
            }

            if (empty($batchData)) {
                throw new Exception('No records to update.');
            }

            $this->_database->trans_begin(); // Begin a transaction

            // Perform batch update
            $success = $this->_database->update_batch($this->table, $batchData, $keyColumn);

            if (!$success || $this->_database->trans_status() === FALSE) {
                throw new Exception('Failed to update records');
            }

            $this->_database->trans_commit();
            $this->resetQuery();

            return [
                'code' => 200,
                'id' => array_column($batchData, $keyColumn),
                'data' => $batchData,
                'message' => 'Updated successfully',
                'action' => 'update'
            ];
        } catch (Exception $e) {
            $this->_database->trans_rollback(); // Rollback the transaction
            if ($this->debug) log_message('error', "Batch update error in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to update data',
                'action' => 'update'
            ];
        }
    }

    /**
     * Delete a record
     *
     * @param mixed $id ID of the record to delete
     * @return array Response with status code, data, action, and primary key
     */
    public function destroy($id = NULL)
    {
        try {
            if (empty($id)) {
                throw new Exception('Please provide id to delete.');
            }

            $data = $this->withTrashed()->find($id);

            if (!$data) {
                throw new Exception('Records not found');
            }

            if ($this->softDelete) {
                if (empty($data[$this->deleted_at])) {
                    $this->_set_timezone();
                    $success = $this->_database->where($this->primaryKey, $id)->update($this->table, [$this->deleted_at => date($this->timestamps_format)]);
                } else {
                    $success = $this->_database->delete($this->table, [$this->primaryKey => $id]); // will force delete if the data already been deleted before.
                }
            } else {
                $success = $this->_database->delete($this->table, [$this->primaryKey => $id]);
            }

            $this->resetQuery();

            if (!$success) {
                throw new Exception('Failed to delete record');
            }

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete'
            ];
        } catch (Exception $e) {
            if ($this->debug) log_message('error', "Delete error for id ({$id}) in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to delete records',
                'action' => 'delete'
            ];
        }
    }

    /**
     * Delete all/specific record based on the conditions
     *
     * @return array Response with status code, data, action, and primary key
     */
    public function destroyAll()
    {
        try {

            $data = (clone $this)->withTrashed()->get();

            if (!$data) {
                throw new Exception('Records not found');
            }

            if ($this->softDelete) {
                $this->_set_timezone();
                $success = $this->_database->update($this->table, [$this->deleted_at => date($this->timestamps_format)]);
            } else {
                $success = $this->_database->delete($this->table);
            }

            $this->resetQuery();

            if (!$success) {
                throw new Exception('Failed to delete record');
            }

            return [
                'code' => 200,
                'id' => array_column($data, $this->primaryKey),
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete'
            ];
        } catch (Exception $e) {
            if ($this->debug) log_message('error', "Delete error for multi data in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to delete records',
                'action' => 'delete'
            ];
        }
    }

    /**
     * Force delete a record
     *
     * @param mixed $id ID of the record to delete
     * @return array Response with status code, data, action, and primary key
     */
    public function forceDestroy($id = NULL)
    {
        try {
            $data = $this->withTrashed()->find($id);

            if (!$data) {
                throw new Exception('Records not found');
            }

            $success = $this->_database->delete($this->table, [$this->primaryKey => $id]);

            $this->resetQuery();

            if (!$success) {
                throw new Exception('Failed to force delete record');
            }

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete',
            ];
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'Force Delete Error: ' . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to removed data',
                'action' => 'delete',
            ];
        }
    }

    /**
     * Restore a soft delete records
     *
     * @param mixed $id ID of the record to restore
     * @return array Response with status code, data, action, and primary key
     */
    public function restore($id = NULL)
    {
        try {
            $data = $this->onlyTrashed()->find($id);

            if (!$data) {
                throw new Exception('Records not found');
            }

            $success = $this->_database->where($this->primaryKey, $id)->update($this->table, [$this->deleted_at => NULL]);

            $this->resetQuery();

            if (!$success) {
                throw new Exception('Failed to restore record with id : ' . $id);
            }

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Restore successfully',
                'action' => 'restore',
            ];
        } catch (Exception $e) {
            if ($this->debug) log_message('error', 'Restore Error: ' . $e->getMessage());
            return [
                'code' => 422,
                'error' => $e->getMessage(),
                'message' => 'Failed to restore data',
                'action' => 'restore',
            ];
        }
    }

    /**
     * Filter data based on fillable and protected fields
     *
     * @param array $data Data to filter
     * @param mixed $includeKey column key of the record to be include in the $data
     * @return array Filtered data
     */
    private function filterData($data, $includeKey = null)
    {
        if (empty($data) && !is_array($data)) {
            return $data;
        }

        if (!empty($includeKey)) {
            $this->fillable[] = $includeKey;

            // Check if $includeKey exists in $this->protected
            if (($key = array_search($includeKey, $this->protected)) !== false) {
                unset($this->protected[$key]);
            }
        }

        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        if (!empty($this->protected)) {
            $data = array_diff_key($data, array_flip($this->protected));
        }

        return $data;
    }

    /**
     * Runs validation on the provided data using the specified validation rules.
     *
     * @param array $data The data to validate.
     * @param array $validationRules The validation rules to apply.
     * @param string $action The action being performed (e.g., "create", "update").
     * @return bool True if validation passes, false otherwise.
     */
    private function _runValidation($data, $validationRules, $action)
    {
        // Reset old validation error
        $this->_validationError = [];

        if (!$this->_ignoreValidation) {
            // Merge validation rules and override if any
            $validation = !empty($this->_overrideValidation) ? $this->_overrideValidation : array_merge($validationRules, $this->_validationCustomize);

            if (!empty($validation)) {
                // Filter out validation rules that don't have corresponding keys in $data
                $filteredValidation = array_filter($validation, function ($rule, $key) use ($data) {
                    return array_key_exists($key, $data);
                }, ARRAY_FILTER_USE_BOTH);

                if (!empty($filteredValidation)) {
                    // Load the form validation library
                    $this->load->library('form_validation');

                    // Reset validation to clear previous rules and data
                    $this->form_validation->reset_validation();

                    // Set language if _validationLang is set
                    if (!empty($this->_validationLang) && !in_array(strtolower($this->_validationLang), ['english', 'en'])) {
                        $langCode = strtolower($this->_validationLang);
                        $langFile = APPPATH . "language/{$langCode}/form_validation_lang.php";

                        log_message('debug', 'Attempting to load language file: ' . $langFile);

                        if (file_exists($langFile)) {
                            $this->lang->load('form_validation', $langCode);
                            $this->form_validation->set_message($this->lang->language);
                        } else {
                            log_message('error', 'Language file not found: ' . $langFile);
                        }
                    }

                    // Set the data to be validated
                    $this->form_validation->set_data($data);

                    // Set the filtered validation rules
                    $this->form_validation->set_rules($filteredValidation);

                    // Run validation and return errors if any
                    if (!$this->form_validation->run()) {
                        $errors = $this->form_validation->error_array(); // This returns an associative array of errors

                        // Build the unordered list
                        $errorsList = "<ul>";
                        foreach ($errors as $error) {
                            if (!empty($error)) {
                                $errorsList .= "<li>" . htmlspecialchars(trim($error)) . "</li>";
                            }
                        }
                        $errorsList .= "</ul>";

                        if ($this->debug) log_message('error', ucfirst($action) . ' Validation Error: ' . $errorsList);
                        $this->_validationError = [
                            'code' => 422,
                            'data' => $data,
                            'message' => ucfirst($action) . ' operation failed: ' . $errorsList,
                            'action' => $action,
                            'error' => $errors
                        ];
                        return false;
                    }
                }
            }
        }

        return true;
    }

    # TRANSACTION HELPER

    /**
     * Start a database transaction
     *
     * @return $this
     */
    public function startTrans($strict = TRUE, $testMode = FALSE)
    {
        $this->_database->trans_strict($strict);
        return $this->_database->trans_start($testMode);
    }

    /**
     * Complete a database transaction
     *
     */
    public function completeTrans()
    {
        return $this->_database->trans_complete();
    }

    /**
     * Begin a manual database transaction
     *
     */
    public function beginTrans()
    {
        return $this->_database->trans_begin();
    }

    /**
     * Commit a database transaction
     *
     * @return $this
     */
    public function commit()
    {
        return $this->_database->trans_commit();
    }

    /**
     * Check status database transaction
     *
     * @return bool True on success, false on failure
     */
    public function statusTrans()
    {
        return $this->_database->trans_status();
    }

    /**
     * Rollback a database transaction
     *
     * @return $this
     */
    public function rollback()
    {
        return $this->_database->trans_rollback();
    }

    # CONNECTION HELPER

    /**
     * public function on($connection = NULL)
     * Sets a different connection to use for a query
     * @param $connection = NULL - connection group in database setup
     * @return $this
     */
    public function on($connection = NULL)
    {
        if (!empty($connection)) {
            $this->connection = $connection;
            $this->_set_connection();
        }

        return $this;
    }

    /**
     * public function reset_connection()
     * Resets the connection to the default used for all the model
     * @return $this
     */
    public function reset_connection()
    {
        $this->_set_connection();
        return $this;
    }

    /**
     * private function _set_connection()
     *
     * Sets the connection to database
     */
    private function _set_connection()
    {
        $this->_database = $this->load->database($this->connection, TRUE);
    }

    # HELPER SECTION

    public function setTimezone($newTimeZone = NULL)
    {
        if (!empty($newTimeZone)) {
            $this->timezone = $newTimeZone;
        }

        return $this;
    }

    /**
     * private function _set_timezone()
     *
     * Sets the timezone for created_at/updated_at/deleted_at field
     */
    private function _set_timezone()
    {
        // Set timezone if $this->timezone is not null
        if ($this->timezone) {
            date_default_timezone_set($this->timezone);
        }
    }

    /**
     * Fetches and sets the table name for the model.
     * If the table is not explicitly defined, it derives the table name from the model class name,
     * checks if the table exists, and sets the table's fillable and protected fields.
     *
     * @return bool True if the table was successfully fetched and set.
     */
    private function _fetch_table()
    {
        // If the table is not set, derive it from the model class name
        if (!isset($this->table)) {
            $this->table = $this->_database->dbprefix($this->_get_table_name(get_class($this)));

            // Check if the table exists, throw an error if it does not
            if (!$this->_database->table_exists($this->table)) {
                show_error(
                    sprintf(
                        'While trying to figure out the table name, couldn\'t find an existing table named: <strong>"%s"</strong>.<br />You can set the table name in your model by defining the protected variable <strong>$table</strong>.',
                        $this->table
                    ),
                    500,
                    sprintf('Error trying to figure out table name for model "%s"', get_class($this))
                );
            }
        }

        // Set fillable and protected fields based on the table structure
        $this->_set_table_fillable_protected();
    }

    /**
     * Derives the table name from the model class name.
     * This method removes common suffixes like '_m', '_model', '_mdl', or 'model' and pluralizes the result.
     *
     * @param string $model_name The name of the model class.
     * @return string The derived table name.
     */
    private function _get_table_name($model_name)
    {
        // Load helper for string manipulation
        $this->load->helper('inflector');

        // Remove common suffixes and pluralize the model name
        return plural(preg_replace('/(_m|_model|_mdl|model)?$/', '', strtolower($model_name)));
    }

    /**
     * Sets the fillable and protected fields based on the table's structure.
     * If the `fillable` array is not set, it populates it with all fields excluding protected fields or the primary key.
     * If the `protected` array is not set, it defaults to protecting the primary key.
     *
     * @return $this The current model instance for method chaining.
     */
    private function _set_table_fillable_protected()
    {
        // If fillable fields are not set, derive them from the table structure
        if (is_null($this->fillable) && !empty($this->table)) {
            $table_fields = $this->_database->list_fields($this->table);

            foreach ($table_fields as $field) {

                if (in_array($field, [$this->created_at, $this->updated_at, $this->deleted_at])) {
                    continue;
                }

                // Add fields that are not protected and not the primary key
                if (is_array($this->protected) && !in_array($field, $this->protected)) {
                    $this->fillable[] = $field;
                } else if (is_null($this->protected) && $field !== $this->primaryKey) {
                    $this->fillable[] = $field;
                }
            }
        }

        // If protected fields are not set, protect only the primary key
        if (is_null($this->protected)) {
            $this->protected = [$this->primaryKey];
        }

        return $this;
    }

    /**
     * Clone database settings for a clean state
     * 
     * @return array
     */
    protected function _cloneDatabaseSettings()
    {
        return [
            'db' => $this->_database,
            'connection' => $this->connection,
            'table' => $this->table,
            'primaryKey' => $this->primaryKey,
            'relations' => $this->relations ?? [],
            'eagerLoad' => $this->eagerLoad ?? [],
            'returnType' => $this->returnType,
            '_paginateColumn' => $this->_paginateColumn ?? [],
            'index' => $this->_indexString ?? null,
            'indexType' => $this->_indexType ?? 'USE INDEX'
        ];
    }

    private function searchRelatedKeys($data, $keyToSearch)
    {
        $result = [];

        $keys = explode('.', $keyToSearch);

        $searchRecursive = function ($array, $keys, $currentDepth = 0) use (&$searchRecursive, &$result) {
            foreach ($array as $key => $value) {
                if ($key === $keys[$currentDepth]) {
                    if ($currentDepth === count($keys) - 1) {
                        $result[] = $value;
                    } elseif (is_array($value)) {
                        $searchRecursive($value, $keys, $currentDepth + 1);
                    }
                } elseif (is_array($value)) {
                    $searchRecursive($value, $keys, $currentDepth);
                }
            }
        };

        $searchRecursive($data, $keys);

        return $result;
    }

    private function whereNested(Closure $callback)
    {
        $this->_database->group_start();
        $callback($this);
        $this->_database->group_end();
        return $this;
    }

    private function forSubQuery(Closure $callback)
    {
        $query = $this->_database->from($this->getTableWithIndex());
        $callback($query);
        return $query->get_compiled_select();
    }

    public function onDebug($level = E_ALL)
    {
        $this->debug = true;

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting($level);
        return $this;
    }

    /**
     * Apply a condition to the query
     *
     * @param string $method Query method to use
     * @param string $column Column name
     * @param mixed $value Value to compare
     * @param string $operator Comparison operator
     * @throws InvalidArgumentException
     */
    private function applyCondition($method, $column, $value, $operator)
    {
        static $operatorCache = [];

        $upperOperator = strtoupper($operator);

        // Cache the result of in_array check
        if (!isset($operatorCache[$upperOperator])) {
            $operatorCache[$upperOperator] = in_array($upperOperator, $this->allowedOperators);
        }

        if (!$operatorCache[$upperOperator]) {
            throw new InvalidArgumentException("Invalid operator: $operator");
        }

        switch ($upperOperator) {
            case '=':
                $this->_database->$method($column, $value);
                break;
            case 'LIKE':
            case 'NOT LIKE':
                $this->_database->$method("$column $upperOperator", $value);
                break;
            default:
                $this->_database->$method($column . $operator, $value);
        }
    }

    private function validateDayMonth($value, $month = false)
    {
        $max = $month ? 12 : 31;
        if (!is_numeric($value) || $value < 1 || $value > $max) {
            throw new InvalidArgumentException("Invalid value for day/month: $value");
        }
    }

    protected function validateYear($value)
    {
        if (!is_numeric($value) || strlen((string) $value) !== 4) {
            throw new \InvalidArgumentException('Invalid year. Must be a four-digit number.');
        }
    }

    protected function validateInteger($value, $type, $positive = true)
    {
        if (!is_numeric($value) || ($positive && $value <= 0)) {
            throw new InvalidArgumentException("Invalid $type value: $value");
        }
        return (int) $value;
    }

    /**
     * Escape a value for database input
     *
     * @param mixed $value Value to escape
     * @return mixed
     */
    private function escapeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this->_database, 'escape'], $value);
        }

        return $this->_database->escape($value);
    }

    /**
     * Checks if an array is multidimensional.
     *
     * @param mixed $data The array to check.
     * @return bool True if the array is multidimensional, false otherwise.
     */
    private function _isMultidimensional($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method for multi-column sorting
     * 
     * @param array $results The result set to sort
     * @param array $criteria The sorting criteria
     * @return array Sorted results
     */
    private function _multiSort(array $results, array $criteria)
    {
        usort($results, function ($a, $b) use ($criteria) {
            foreach ($criteria as $criterion) {
                $column = $criterion[0];
                $direction = isset($criterion[1]) && strtolower($criterion[1]) === 'desc' ? -1 : 1;

                $valueA = $this->_getValueUsingDotNotation($a, $column);
                $valueB = $this->_getValueUsingDotNotation($b, $column);

                // Handle null values
                if ($valueA === null && $valueB === null) {
                    continue; // Both null, move to next criterion
                } elseif ($valueA === null) {
                    return $direction * -1; // Null values first for asc, last for desc
                } elseif ($valueB === null) {
                    return $direction;
                }

                // Compare values
                if ($valueA == $valueB) {
                    continue; // Equal, move to next criterion
                }

                return ($valueA < $valueB ? -1 : 1) * $direction;
            }

            return 0; // All criteria were equal
        });

        return $results;
    }

    /**
     * Helper method to get value using dot notation
     * e.g., 'user.profile.name' will get $result['user']['profile']['name']
     * 
     * @param mixed $item The item to extract value from
     * @param string $key The key using dot notation (e.g., 'profile.name')
     * @return mixed The extracted value
     */
    private function _getValueUsingDotNotation($item, $key)
    {
        // Handle simple key (no dot notation)
        if (strpos($key, '.') === false) {
            if (is_object($item)) {
                // Check if it's a method that returns a value
                if (method_exists($item, $key)) {
                    return $item->$key();
                }
                return isset($item->$key) ? $item->$key : null;
            } elseif (is_array($item)) {
                return isset($item[$key]) ? $item[$key] : null;
            }

            return null;
        }

        // Handle dot notation
        $segments = explode('.', $key);
        $current = $item;

        foreach ($segments as $segment) {
            if (is_object($current)) {
                // Check if it's an accessor method
                if (method_exists($current, $segment)) {
                    $current = $current->$segment();
                } else {
                    $current = isset($current->$segment) ? $current->$segment : null;
                }
            } elseif (is_array($current)) {
                $current = isset($current[$segment]) ? $current[$segment] : null;
            } else {
                return null; // Cannot proceed further
            }

            // Early return if we hit a null value
            if ($current === null) {
                return null;
            }

            // Handle one-to-many relationships - if we have an array of items
            if (is_array($current) && !empty($current) && !isset($current[0])) {
                // This is an associative array, not a collection
                continue;
            } elseif (is_array($current) && !empty($current)) {
                // For collections, return the first item's value
                $current = $current[0];
            }
        }

        return $current;
    }

    # FORMAT RESPONSE HELPER

    public function toArray()
    {
        $this->returnType = 'array';
        return $this;
    }

    public function toObject()
    {
        $this->returnType = 'object';
        return $this;
    }

    public function toJson()
    {
        $this->returnType = 'json';
        return $this;
    }

    private function formatResult($result)
    {
        if (empty($result)) {
            return $result;
        }

        if ($this->hidden) {
            $result = $this->removeHiddenDataRecursive($result);
        }

        if ($this->appends) {
            $result = $this->appendData($result);
        }

        $resultFormat = null;

        switch ($this->returnType) {
            case 'object':
                $resultFormat = json_decode(json_encode($this->_safeOutputSanitize($result)));
                break;
            case 'json':
                $resultFormat = json_encode($this->_safeOutputSanitize($result));
                break;
            default:
                $resultFormat = $this->_safeOutputSanitize($result);
        }

        $this->resetQuery();
        return $resultFormat;
    }

    private function resetQuery()
    {
        $this->reset_connection();
        $this->primaryKey = 'id';
        $this->relations = [];
        $this->eagerLoad = [];
        $this->aggregateRelations = [];
        $this->returnType = 'array';
        $this->_paginateColumn = [];
        $this->_indexString = null;
        $this->_indexType = 'USE INDEX';
        $this->_suggestIndexEnabled = false;
    }

    # PERFORMANCE HELPER

    public function getTableWithIndex()
    {
        if (empty($this->_indexString)) {
            return $this->table;
        }

        return $this->table . ' ' . $this->_indexType . ' (' . $this->_indexString . ')';
    }

    /**
     * Forces MySQL to use specific indexes for the query
     *
     * @param string|array $indexName Index name or array of index names
     * @return $this
     */
    public function forceIndex($indexName = [])
    {
        if (empty($indexName)) {
            return $this;
        }

        $this->_indexString = is_array($indexName) ? implode(', ', $indexName) : $indexName;
        $this->_indexType = 'FORCE INDEX';
        return $this;
    }

    /**
     * Suggests MySQL to use specific indexes for the query
     *
     * @param string|array $indexName Index name or array of index names
     * @return $this
     */
    public function useIndex($indexName = [])
    {
        if (empty($indexName)) {
            return $this;
        }

        $this->_indexString = is_array($indexName) ? implode(', ', $indexName) : $indexName;
        $this->_indexType = 'USE INDEX';
        return $this;
    }

    /**
     * Instructs MySQL to ignore specific indexes for the query
     *
     * @param string|array $indexName Index name or array of index names
     * @return $this
     */
    public function ignoreIndex($indexName = [])
    {
        if (empty($indexName)) {
            return $this;
        }

        $this->_indexString = is_array($indexName) ? implode(', ', $indexName) : $indexName;
        $this->_indexType = 'IGNORE INDEX';
        return $this;
    }

    /**
     * Analyzes the current query and suggests optimal indexes
     * This should be used during development to identify missing indexes
     *
     * @param bool $explain Whether to run EXPLAIN on the query
     * @return array|$this If $explain is true, returns the explain result, otherwise returns $this
     */
    public function suggestIndex($explain = true)
    {
        if (!$explain) {
            // Just mark that we should log index suggestions after executing the query
            $this->_suggestIndexEnabled = true;
            return $this;
        }

        // Get the compiled select query
        $query = (clone $this->_database)->get_compiled_select($this->table, false);

        // Run EXPLAIN on the query
        $explainResults = (clone $this->_database)->query("EXPLAIN $query")->result_array();

        $suggestions = [];
        foreach ($explainResults as $row) {
            // Look for rows that indicate poor performance
            if (
                (isset($row['type']) && in_array($row['type'], ['ALL', 'index', 'range'])) ||
                (isset($row['key']) && empty($row['key'])) ||
                (isset($row['rows']) && $row['rows'] > 1000)
            ) {
                // Identify columns that would benefit from indexes
                if (isset($row['possible_keys']) && empty($row['possible_keys'])) {
                    // Extract columns from 'ref' or 'where' clause
                    $columns = [];

                    if (isset($row['ref']) && !empty($row['ref'])) {
                        $columns[] = $row['ref'];
                    }

                    if (isset($row['Extra']) && strpos($row['Extra'], 'Using where') !== false) {
                        // Try to extract column names from the compiled query's WHERE clause
                        preg_match_all('/WHERE\s+([^\s]+)\s*=/', $query, $matches);
                        if (!empty($matches[1])) {
                            $columns = array_merge($columns, $matches[1]);
                        }
                    }

                    if (!empty($columns)) {
                        $suggestions[] = [
                            'table' => $row['table'],
                            'suggested_columns' => array_unique($columns),
                            'type' => 'Missing index',
                            'reason' => "Query scanning {$row['rows']} rows with type {$row['type']}"
                        ];
                    }
                }
            }
        }

        // Log the suggestions
        if (!empty($suggestions)) {
            log_message('debug', 'Index Suggestions: ' . json_encode($suggestions, JSON_PRETTY_PRINT));
        } else {
            log_message('debug', 'No index suggestions for the current query. The query seems optimized.');
        }

        return $this;
    }

    /**
     * Log performance, execution time, and suggest CREATE INDEX
     *
     * @param string $query
     * @return void
     */
    private function _logQueryPerformance($query)
    {
        if (!$this->debug || stripos(trim($query), 'SELECT') !== 0) {
            return;
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $explainQuery = "EXPLAIN $query";
            $explainResults = (clone $this->_database)->query($explainQuery)->result_array();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds
            $memoryUsage = round((memory_get_usage() - $startMemory) / 1024, 2); // in KB

            $fullScans = [];
            $inefficientIndexes = [];
            $indexSuggestions = [];

            foreach ($explainResults as $row) {
                $table = $row['table'] ?? null;
                $type = $row['type'] ?? '';
                $rows = (int) ($row['rows'] ?? 0);
                $key = $row['key'] ?? null;
                $possibleKeys = $row['possible_keys'] ?? null;
                $extra = strtolower($row['Extra'] ?? '');

                if (!$table) continue;

                // FULL TABLE SCAN
                if (strtoupper($type) === 'ALL') {
                    $fullScans[] = $row;

                    if (!empty($possibleKeys)) {
                        foreach (explode(',', $possibleKeys) as $k) {
                            $indexSuggestions[] = "CREATE INDEX idx_{$table}_" . trim($k) . " ON `$table`(" . trim($k) . ");";
                        }
                    } elseif (strpos($extra, 'using where') !== false) {
                        $indexSuggestions[] = "Table `$table` performs full scan with WHERE clause — consider indexing WHERE fields.";
                    } else {
                        $indexSuggestions[] = "Table `$table` has no suggested indexes. Review WHERE/JOIN columns for indexing.";
                    }
                }

                // INEFFICIENT INDEX
                if (!empty($key) && $rows > 5000) {
                    $inefficientIndexes[] = $row;
                    $indexSuggestions[] = "Table `$table` scans $rows rows using `$key` — refine or create covering index.";
                }
            }

            // Logging
            if (!empty($fullScans)) {
                log_message('warning', "FULL TABLE SCAN:\n" . json_encode($fullScans, JSON_PRETTY_PRINT));
                log_message('warning', "Query:\n" . $query);
            }

            if (!empty($inefficientIndexes)) {
                log_message('warning', "INEFFICIENT INDEX USAGE:\n" . json_encode($inefficientIndexes, JSON_PRETTY_PRINT));
            }

            if (!empty($indexSuggestions)) {
                log_message('debug', "CREATE INDEX Suggestions:\n" . implode("\n", array_unique($indexSuggestions)));
            }

            log_message('debug', "Query Performance: Time = {$executionTime}ms | Memory = {$memoryUsage}KB");
        } catch (Exception $e) {
            log_message('error', 'Query performance analysis failed: ' . $e->getMessage());
        }
    }

    private function isColumnIndexed($columnName, $tableName)
    {
        $query = $this->_database->query(
            "
            SELECT COUNT(1) as indexed 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME != 'PRIMARY' 
            AND COLUMN_NAME = ?",
            [$tableName, $columnName]
        );

        $result = $query->row_array();
        $isIndexed = !empty($result) && $result['indexed'] > 0;

        if ($this->debug) {
            log_message('debug', "Index check for column `$columnName` on table `$tableName`: " . ($isIndexed ? "Indexed ✅" : "Not Indexed ❌"));
        }

        return $isIndexed;
    }

    # SECURITY HELPER

    /**
     * Enable safe output against XSS injection
     *
     * @return $this
     */
    public function safeOutput()
    {
        $this->_secureOutput = true;
        $this->_secureOutputException = [];
        return $this;
    }

    /**
     * Enable safe output against XSS injection with exception keys
     *
     * @param array $exceptions Keys to exclude from sanitization
     * @return $this
     */
    public function safeOutputWithException(array $exceptions = [])
    {
        $this->_secureOutput = true;
        $this->_secureOutputException = array_map('strval', $exceptions);
        return $this;
    }

    /**
     * Sanitize output data
     *
     * @param mixed $data
     * @return mixed
     */
    private function _safeOutputSanitize($data)
    {
        if (!$this->_secureOutput || $data === null || $data === '') {
            return $data;
        }

        return $this->sanitize($data);
    }

    /**
     * Recursively sanitize values
     *
     * @param mixed $value
     * @param string|null $key Current key for exceptions
     * @return mixed
     */
    private function sanitize($value, $key = null)
    {
        if ($key !== null && in_array($key, $this->_secureOutputException, true)) {
            return $value;
        }

        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $k => $v) {
                $sanitized[$k] = $this->sanitize($v, $k);
            }
            return $sanitized;
        }

        switch (gettype($value)) {
            case 'string':
                return htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'integer':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            case 'double':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'boolean':
                return (bool) $value;
            case 'NULL':
                return null;
            default:
                return $value;
        }
    }

    protected function removeHiddenDataRecursive($data)
    {
        // Flip the hidden array for faster key lookups
        $hiddenFlipped = array_flip($this->hidden);

        // Remove hidden keys
        $data = array_diff_key($data, $hiddenFlipped);

        // Recursively process nested arrays
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->removeHiddenDataRecursive($value, $this->hidden);
            }
        }

        return $data;
    }

    public function showColumnHidden()
    {
        $this->hidden = null;
        return $this;
    }

    public function setColumnHidden($hidden = null)
    {
        $this->hidden = $hidden;
        return $this;
    }

    # APPEND DATA HELPER

    public function setAppends($appends = null)
    {
        $this->appends = $appends;
        return $this;
    }

    private function appendData($resultQuery)
    {
        if (empty($resultQuery) || empty($this->appends) || empty($this->fillable)) {
            return $resultQuery;
        }

        $isMulti = $this->_isMultidimensional($resultQuery);
        $appendMethods = $this->getAppendMethods();

        if ($isMulti) {
            foreach ($resultQuery as $key => &$item) {
                if (!is_string($key)) {
                    $this->appendToSingle($item, $appendMethods);
                } else {
                    $this->appendToSingle($resultQuery, $appendMethods);
                    break;
                }
            }
        } else {
            $this->appendToSingle($resultQuery, $appendMethods);
        }

        return $resultQuery;
    }

    private function getAppendMethods()
    {
        $methods = [];
        foreach ($this->appends as $append) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $append))) . 'Attribute';
            if (method_exists($this, $methodName)) {
                $methods[$append] = $methodName;
            }
        }
        return $methods;
    }

    private function appendToSingle(&$item, $appendMethods)
    {
        $this->setAttributes($item);

        foreach ($appendMethods as $append => $method) {
            $item[$append] = $this->$method();
        }

        $this->unsetAttributes();
    }

    private function setAttributes($data)
    {
        foreach ($this->fillable as $attribute) {
            $this->$attribute = $data[$attribute] ?? null;
        }
    }

    private function unsetAttributes()
    {
        foreach ($this->fillable as $attribute) {
            unset($this->$attribute);
        }
    }

    public function __destruct()
    {
        $this->resetQuery();
    }

    /**
     * Improves the clone method to properly handle database connection
     */
    public function __clone()
    {
        // Make sure we clone the database connection
        if (isset($this->_database)) {
            $this->_database = clone $this->_database;
        }
    }
}

# LazyCollection Class

/**
 * LazyCollection class for handling large datasets with minimal memory usage
 */
class LazyCollection implements Iterator, Countable
{
    private $source;
    private $position = 0;
    private $currentChunk = null;
    private $chunkSize = 100;
    private $chunkPosition = 0;
    private $totalCount = null;
    private $exhausted = false;
    private $operations = [];
    private $currentItems = [];

    /**
     * Create a new LazyCollection instance
     * 
     * @param callable $source The source data generator
     */
    public function __construct(callable $source)
    {
        $this->source = $source;
    }

    /**
     * Get the current item
     * 
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->loadChunkIfNeeded();

        if (!isset($this->currentItems[$this->position % $this->chunkSize])) {
            return null;
        }

        $item = $this->currentItems[$this->position % $this->chunkSize];

        // Apply operations to the item
        foreach ($this->operations as $operation) {
            if ($operation['type'] === 'map') {
                $item = call_user_func($operation['callback'], $item);
            } elseif ($operation['type'] === 'filter' && !call_user_func($operation['callback'], $item)) {
                $this->next();
                return $this->current();
            }
        }

        return $item;
    }

    /**
     * Get the current position
     * 
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Move to the next item
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->position++;
    }

    /**
     * Rewind the collection to the beginning
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
        $this->chunkPosition = 0;
        $this->currentItems = [];
        $this->exhausted = false;
    }

    /**
     * Check if the current position is valid
     * 
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        if ($this->exhausted) {
            return false;
        }

        $this->loadChunkIfNeeded();

        return isset($this->currentItems[$this->position % $this->chunkSize]);
    }

    /**
     * Count elements of the collection
     * 
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->totalCount === null) {
            // We need to iterate through all items to get an accurate count for lazy collections
            $count = 0;
            foreach ($this as $item) {
                $count++;
            }
            $this->totalCount = $count;
            $this->rewind(); // Reset the iterator after counting
        }

        return $this->totalCount;
    }

    /**
     * Load the next chunk of data if needed
     */
    private function loadChunkIfNeeded()
    {
        $currentChunkIndex = floor($this->position / $this->chunkSize);

        if ($currentChunkIndex !== $this->chunkPosition || empty($this->currentItems)) {
            try {
                $source = $this->source;
                $chunk = $source($this->chunkSize, $this->position);

                if (empty($chunk)) {
                    $this->exhausted = true;
                    $this->currentItems = [];
                    return;
                }

                $this->currentItems = $chunk;
                $this->chunkPosition = $currentChunkIndex;
            } catch (Exception $e) {
                $this->exhausted = true;
                $this->currentItems = [];
                throw new Exception("Error loading data chunk: " . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Execute a callback over each item while maintaining lazy evaluation
     * 
     * @param callable $callback
     * @return LazyCollection
     */
    public function map(callable $callback)
    {
        $this->operations[] = [
            'type' => 'map',
            'callback' => $callback
        ];

        return $this;
    }

    /**
     * Filter items by a given callback while maintaining lazy evaluation
     * 
     * @param callable $callback
     * @return LazyCollection
     */
    public function filter(callable $callback)
    {
        $this->operations[] = [
            'type' => 'filter',
            'callback' => $callback
        ];

        return $this;
    }

    /**
     * Execute a callback over each item
     * 
     * @param callable $callback
     * @return LazyCollection
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Get all items as an array (caution: loads all data into memory)
     * 
     * @return array
     */
    public function all()
    {
        $results = [];

        foreach ($this as $item) {
            $results[] = $item;
        }

        return $results;
    }

    /**
     * Get the first item in the collection
     * 
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        if ($callback === null) {
            if ($this->valid()) {
                return $this->current();
            }

            return $default;
        }

        foreach ($this as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * Take the first n items from the collection
     * 
     * @param int $limit
     * @return LazyCollection
     */
    public function take($limit)
    {
        $self = $this;
        return new LazyCollection(function ($size, $offset) use ($self, $limit) {
            if ($offset >= $limit) {
                return [];
            }

            $source = $this->source;
            $items = $source($size, $offset);

            return array_slice($items, 0, min(count($items), $limit - $offset));
        });
    }

    /**
     * Get a value from all items by key
     * 
     * @param string $key
     * @return LazyCollection
     */
    public function pluck($key)
    {
        return $this->map(function ($item) use ($key) {
            return is_array($item) ? ($item[$key] ?? null) : (is_object($item) ? ($item->$key ?? null) : null);
        });
    }

    /**
     * Get a specific chunk of items from the collection
     * 
     * @param int $size
     * @return LazyCollection
     */
    public function chunk($size)
    {
        $chunks = [];
        $chunk = [];
        $i = 0;

        foreach ($this as $item) {
            $chunk[] = $item;
            $i++;

            if ($i % $size === 0) {
                $chunks[] = $chunk;
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            $chunks[] = $chunk;
        }

        return new LazyCollection(function () use ($chunks) {
            return $chunks;
        });
    }

    /**
     * Create a collection of all elements that pass the given truth test
     * 
     * @param callable $callback
     * @return LazyCollection
     */
    public function reject(callable $callback)
    {
        return $this->filter(function ($item) use ($callback) {
            return !$callback($item);
        });
    }

    /**
     * Concatenate values of a given key as a string
     * 
     * @param string $key
     * @param string $glue
     * @return string
     */
    public function implode($key, $glue = '')
    {
        $result = '';
        $first = true;

        foreach ($this->pluck($key) as $item) {
            if (!$first) {
                $result .= $glue;
            } else {
                $first = false;
            }

            $result .= $item;
        }

        return $result;
    }

    /**
     * Pass the collection to the given callback and then return it
     * 
     * @param callable $callback
     * @return LazyCollection
     */
    public function tap(callable $callback)
    {
        $callback($this);
        return $this;
    }

    /**
     * Skip the given number of items
     * 
     * @param int $count
     * @return LazyCollection
     */
    public function skip($count)
    {
        return new LazyCollection(function ($size, $offset) use ($count) {
            $source = $this->source;
            return $source($size, $offset + $count);
        });
    }

    /**
     * Set the chunk size for internal data loading
     *
     * @param int $size
     * @return LazyCollection
     */
    public function setChunkSize($size)
    {
        $this->chunkSize = max(1, (int)$size);
        return $this;
    }
}
