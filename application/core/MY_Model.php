<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MY_Model Class
 *
 * @category  Model
 * @Description  An extended model class for CodeIgniter 3 with advanced querying capabilities, relationship handling, and security features.
 * @author    Mohd Fahmy Izwan Zulkhafri <faizzul14@gmail.com>
 * @link      -
 * @version   0.0.9.6
 */

class MY_Model extends CI_Model
{
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

    protected $relations = [];
    protected $eagerLoad = [];
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

    protected $_paginateColumn = [];
    protected $_paginateSearchValue = '';

    public $_validationRules = []; // will be used for both insert & update
    protected $_validationCustomize = []; // will be used to customize/add-on validation for insert & update
    public $_insertValidation = []; // will be used for insert only
    public $_updateValidation = []; // will be used for update only
    protected $_overrideValidation = []; // to override the validation for update & insert
    protected $_ignoreValidation = false; // will ignore the validation
    protected $_validationError = []; // used to store the validation error message

    public $cacheEnable = false;
    public $cacheTimeout = 3600;
    protected $cacheDir = 'query';

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
     * Select columns for the query
     *
     * @param string $columns Columns to select
     * @return $this
     */
    public function select($columns = '*')
    {
        // Handle ambiguous column names (e.g., id, created_at, updated_at) by prefixing with table name
        if (is_array($columns)) {
            $columns = array_map(function ($column) {
                return strpos($column, '.') === false ? "{$this->table}.$column" : $column;
            }, $columns);
            $columns = implode(',', $columns);
        } else if ($columns !== '*') {
            $columns = implode(',', array_map(function ($column) {
                return strpos($column, '.') === false ? "{$this->table}.$column" : $column;
            }, explode(',', $columns)));
        } else {
            $columns = $this->table . '.*';
        }

        $this->_database->select(trim($columns));
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
        $this->_database = $this->_database->query($query);
        return $this;
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

    public function chunk($size, callable $callback)
    {
        $offset = 0;

        // Store the original query state
        $originalState = $this->_cloneDatabaseSettings();

        while (true) {
            // Restore the original query state
            $this->connection = $originalState['connection'];
            $this->table = $originalState['table'];
            $this->primaryKey = $originalState['primaryKey'];
            $this->relations = $originalState['relations'];
            $this->eagerLoad = $originalState['eagerLoad'];
            $this->returnType = $originalState['returnType'];

            // Apply limit and offset
            $this->limit($size)->offset($offset);

            // Get results 
            $results = $this->get();

            if (empty($results)) {
                break;
            }

            if (call_user_func($callback, $results) === false) {
                break;
            }

            $offset += $size;

            // Clear the results to free memory
            unset($results);
        }

        // Reset internal properties for next query
        $this->resetQuery();

        return $this;
    }

    public function count()
    {
        $query = $this->_database->count_all_results($this->table);
        $this->resetQuery();
        return $query;
    }

    public function toSql()
    {
        $query = $this->_database->get_compiled_select($this->table, false);
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
     * Get the results of the query
     *
     * @return array|object|json Results based on returnType
     */
    public function get()
    {
        try {

            $cacheKey = $this->getCacheKey();
            $cachedResult = $this->getCache($cacheKey);

            if ($cachedResult !== false) {
                $result = $cachedResult;
            } else {
                $this->_withTrashQueryFilter();
                $result = $this->_database->get($this->table)->result_array();
                $this->setCache($cacheKey, $result);
            }

            if (!empty($result))
                $result = $this->loadRelations($result);

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
            $cacheKey = $this->getCacheKey();
            $cachedResult = $this->getCache($cacheKey);

            if ($cachedResult !== false) {
                $result = $cachedResult;
            } else {
                $this->_withTrashQueryFilter();
                $result = $this->_database->get($this->table)->row_array();
                $this->setCache($cacheKey, $result);
            }

            if (!empty($result))
                $result = $this->loadRelations([$result]);

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

    # PAGINATION SECTION

    public function setPaginateFilterColumn($column = [])
    {
        $this->_paginateColumn = is_array($column) ? $column : [];
        return $this;
    }

    /**
     * Paginate the query results
     *
     * @param int $perPage Items per page
     * @param int|null $page Current page
     * @param string|null $searchValue The search value for the specific column
     * @param array|null $customFilter The advanced filter search
     * @return array Paginated results
     */
    public function paginate($perPage = 10, $page = null, $searchValue = '', $customFilter = null)
    {
        $this->cacheOff(); // Removed caching for datatable

        $page = $page ?: ($this->input->get('page') ? $this->input->get('page') : 1);
        $offset = ($page - 1) * $perPage;

        $this->_paginateSearchValue = trim($searchValue);
        $columns = $this->_database->list_fields($this->table);

        $this->_withTrashQueryFilter();

        // Count total rows before filter
        $countTempTotal = clone $this->_database;
        $totalRecords = (int) $countTempTotal->count_all_results($this->table);
        unset($countTempTotal);

        // Apply custom filter (advanced search)
        if (!empty($customFilter) && is_array($customFilter)) {
            $this->_paginateFilterCondition($customFilter);
        }

        // Apply search filter
        $this->_paginateSearchFilter($columns);

        // Count total rows after filter
        $countTempQuery = clone $this->_database;
        $total = (int) $countTempQuery->count_all_results($this->table);
        unset($countTempQuery);

        // Fetch only the required page of results
        $this->limit($perPage)->offset($offset);
        $data = $this->get();

        // Calculate pagination details
        $totalPages = (int) ceil($total / $perPage);
        $nextPage = ($page < $totalPages) ? $page + 1 : null;
        $previousPage = ($page > 1) ? $page - 1 : null;

        // Configure pagination
        $this->load->library('pagination');
        $config = [
            'base_url' => current_url(),
            'total_rows' => $total,
            'per_page' => $perPage,
            'use_page_numbers' => TRUE,
            'page_query_string' => TRUE,
            'query_string_segment' => 'page',
            'full_tag_open' => '<ul class="pagination">',
            'full_tag_close' => '</ul>',
            'first_link' => '&laquo;',
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'last_link' => '&raquo;',
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close' => '</li>',
            'next_link' => '&gt;',
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close' => '</li>',
            'prev_link' => '&lt;',
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close' => '</li>',
            'cur_tag_open' => '<li class="page-item active"><a class="page-link">',
            'cur_tag_close' => '</a></li>',
            'num_tag_open' => '<li class="page-item">',
            'num_tag_close' => '</li>',
            'attributes' => ['class' => 'page-link'],
        ];

        $this->pagination->initialize($config);

        return [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $total,
            'data' => $data,
            'current_page' => $page,
            'next_page' => $nextPage,
            'previous_page' => $previousPage,
            'last_page' => $totalPages,
            'error' => $page > $totalPages ? "Current page ({$page}) is more than total pages ({$totalPages})" : '',
            'links' => $this->pagination->create_links()
        ];
    }

    public function paginate_ajax($dataPost, $customFilter = null)
    {
        $this->cacheOff(); // Removed caching for datatable

        $this->_paginateSearchValue = trim($dataPost['search']['value']);
        $columns = empty($this->_paginateColumn) ? $this->_database->list_fields($this->table) : $this->_paginateColumn;

        $this->_withTrashQueryFilter();

        // Count total rows before filter
        $countTempTotal = clone $this->_database;
        $totalRecords = (int) $countTempTotal->count_all_results($this->table);
        unset($countTempTotal);

        // Apply custom filter (advanced search)
        if (!empty($customFilter) && is_array($customFilter)) {
            $this->_paginateFilterCondition($customFilter);
        }

        // Apply search filter
        $this->_paginateSearchFilter($columns);

        // Count total rows after filter
        $countTempQuery = clone $this->_database;
        $total = (int) $countTempQuery->count_all_results($this->table);
        unset($countTempQuery);

        // Fetch only the required page of results
        $this->limit($dataPost['length'])->offset($dataPost['start']);

        // Apply Order data if exists
        $orderBy = $dataPost['order'];
        if (!empty($orderBy)) {
            $this->orderBy($columns[$orderBy[0]['column']], $orderBy[0]['dir']);
        }

        return [
            'draw' => $dataPost['draw'],
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $total,
            'data' => $this->get(),
        ];
    }

    private function _paginateFilterCondition($condition = null)
    {
        if (empty($condition)) {
            return;
        }
        
        // $matchType : 1-Match Exactly (default), 2-Match Beginning, 3-Match Anywhere 
        if (_isMultidimensional($condition)) {
            $matchType = $condition['filter_type'] ?? 1; // Default to exact match
            $condition = $condition['filter'] ?? [];
        } else {
            $matchType = 1; // Default to exact match
        }

        $this->_database->group_start();

        foreach ($condition as $column => $value) {
            if (!empty($value)) {
                switch ($matchType) {
                    case 2:
                        $this->_database->like($column, $value, 'after'); // `column` LIKE 'value%' ESCAPE '!'
                        break;
                    case 3:
                        $this->_database->like($column, $value); // `column` LIKE '%value%' ESCAPE '!'
                        break;
                    default:
                        $this->_database->where($column, $value);
                }
            }
        }

        $this->_database->group_end();
    }

    private function _paginateSearchFilter($columns)
    {
        $searchValue = $this->_paginateSearchValue;

        if (empty($searchValue)) {
            return;
        }

        $i = 0;
        $this->_database->group_start();
        foreach ($columns as $column) {
            if (!empty($column)) {
                if ($i === 0) {
                    $this->_database->like($column, $searchValue);
                } else {
                    $this->_database->or_like($column, $searchValue);
                }
            }
            $i++;
        }
        $this->_database->group_end();
    }

    # RELATION SECTION

    public function hasMany($modelName, $foreignKey, $localKey = null)
    {
        $this->relations[$modelName] = ['type' => 'hasMany', 'model' => $modelName, 'foreignKey' => $foreignKey, 'localKey' => $localKey ?: $this->primaryKey];
        return $this;
    }

    public function hasOne($modelName, $foreignKey, $localKey = null)
    {
        $this->relations[$modelName] = ['type' => 'hasOne', 'model' => $modelName, 'foreignKey' => $foreignKey, 'localKey' => $localKey ?: $this->primaryKey];
        return $this;
    }

    public function belongsTo($modelName, $foreignKey, $ownerKey = null)
    {
        $this->relations[$modelName] = ['type' => 'belongsTo', 'model' => $modelName, 'foreignKey' => $foreignKey, 'ownerKey' => $ownerKey ?: $this->primaryKey];
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
                throw new Exception("Method {$currentRelation} does not exist in the model " . get_class($this));
            }

            $configRelation = $relatedInstance->{$currentRelation}();

            if (isset($configRelation->relations)) {
                foreach ($configRelation->relations as $modelName => $rels) {
                    $relationType = $rels['type'];
                    $foreignKey = $rels['foreignKey'];

                    if (!$this->load->is_model_loaded($modelName))
                        $this->load->model($modelName);

                    $relationInstance = $this->{$modelName};

                    if ($constraints instanceof Closure) {
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
        } catch (Exception $e) {
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

    # CRUD SECTION

    /**
     * Ignore validation rules temporarily
     *
     * @return $this
     */
    public function ignoreValidation()
    {
        $this->_ignoreValidation = true;
        return $this;
    }

    /**
     * Set override validation rules
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
     * Set custom validation rules
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
            $existingRecord = get_instance()->db->from($this->table)->where($conditions)->get()->row_array();

            if ($existingRecord) {
                // If record exists, update it
                $id = $existingRecord[$this->primaryKey];
                return $this->patch($data, $id);
            } else {
                // If record doesn't exist, create it
                return $this->create($data);
            }
        } catch (Exception $e) {
            log_message('error', 'insertOrUpdate error: ' . $e->getMessage());
            return [
                'code' => 500,
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

                if (!$success) {
                    throw new Exception('Failed to insert record');
                }

                $insertId = $this->_database->insert_id();
                $this->resetQuery();

                $this->deleteCache();

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
            log_message('error', 'Create error: ' . $e->getMessage());
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

            $this->deleteCache();

            return [
                'code' => 200,
                'id' => $lastInsertId,
                'data' => $batchData,
                'message' => 'Batch creation successful',
                'action' => 'create'
            ];
        } catch (Exception $e) {
            $this->_database->trans_rollback();
            log_message('error', "Batch creation error in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 500,
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

                $this->deleteCache();

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
            log_message('error', "Update error for id ({$id}) in table {$this->table}: "  . $e->getMessage());
            return [
                'code' => 500,
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

            return $this->batchPatch($updateData);
        } catch (Exception $e) {
            log_message('error', "Update error for patchAll: "  . $e->getMessage());
            return [
                'code' => 500,
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

            $this->deleteCache();

            return [
                'code' => 200,
                'id' => array_column($batchData, $keyColumn),
                'data' => $batchData,
                'message' => 'Updated successfully',
                'action' => 'update'
            ];
        } catch (Exception $e) {
            $this->_database->trans_rollback(); // Rollback the transaction
            log_message('error', "Batch update error in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 500,
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

            $this->deleteCache();

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete'
            ];
        } catch (Exception $e) {
            log_message('error', "Delete error for id ({$id}) in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 500,
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

            $temp = clone $this->_database;
            $data = $this->withTrashed()->get();
            $this->_database = $temp;

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

            $this->deleteCache();

            return [
                'code' => 200,
                'id' => array_column($data, $this->primaryKey),
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete'
            ];
        } catch (Exception $e) {
            log_message('error', "Delete error for multi data in table {$this->table}: " . $e->getMessage());
            return [
                'code' => 500,
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

            $this->deleteCache();

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Removed successfully',
                'action' => 'delete',
            ];
        } catch (Exception $e) {
            log_message('error', 'Force Delete Error: ' . $e->getMessage());
            return [
                'code' => 500,
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

            $this->deleteCache();

            return [
                'code' => 200,
                $this->primaryKey => $id,
                'data' => $data,
                'message' => 'Restore successfully',
                'action' => 'restore',
            ];
        } catch (Exception $e) {
            log_message('error', 'Restore Error: ' . $e->getMessage());
            return [
                'code' => 500,
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
        if (!empty($data) && is_array($data)) {

            if (!empty($includeKey)) {
                $this->fillable[] = $includeKey;
                
                if (isset($this->protected[$includeKey])) {
                    unset($this->protected[$includeKey]);
                }
            }

            if ($this->fillable !== null) {
                $data = array_intersect_key($data, array_flip($this->fillable));
            }

            if ($this->protected !== null) {
                $data = array_diff_key($data, array_flip($this->protected));
            }
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

                    // Set the data to be validated
                    $this->form_validation->set_data($data);

                    // Set the filtered validation rules
                    $this->form_validation->set_rules($filteredValidation);

                    // Run validation and return errors if any
                    if (!$this->form_validation->run()) {
                        // Collect validation error messages and format as an unordered list without <p> tags
                        $errors = validation_errors();

                        // Remove <p> tags from errors
                        $err = strip_tags($errors); // Remove all HTML tags
                        $errorsArray = explode("\n", trim($err));

                        // Build the unordered list
                        $errorsList = "<ul>";
                        foreach ($errorsArray as $error) {
                            if (!empty($error)) {
                                $errorsList .= "<li>" . htmlspecialchars(trim($error)) . "</li>";
                            }
                        }
                        $errorsList .= "</ul>";

                        log_message('error', ucfirst($action) . ' Validation Error: ' . $errorsList);
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

    # CACHING HELPER

    public function cacheTimeout($timeout = 3600)
    {
        $this->cacheEnable = true;
        $this->cacheTimeout = $timeout;
        return $this;
    }

    public function cacheOff()
    {
        $this->cacheEnable = false;
        return $this;
    }

    private function getCacheKey()
    {
        if (!$this->cacheEnable) return null;

        // Get the compiled SQL query
        $_temp = clone $this->_database;
        $query = $_temp->get_compiled_select($this->table, false);
        unset($_temp);

        // Create a standardized array of key components
        $keyComponents = [
            'query' => $this->normalizeQuery($query),
            'connection' => $this->connection,
            'table' => $this->table,
            'primary_key' => $this->primaryKey,
            'eager_load' => $this->eagerLoad,
            'return_as' => $this->returnType
        ];

        // Sort the array by keys to ensure consistent order
        ksort($keyComponents);

        // Create the cache directory if it doesn't exist
        $cachePath = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $this->cacheDir . DIRECTORY_SEPARATOR . strtolower(get_class($this)) . DIRECTORY_SEPARATOR;
        if (!file_exists($cachePath) && $this->cacheEnable) {
            mkdir($cachePath, 0755, true);
        }

        // Generate a consistent hash for the key components
        return strtolower($this->table) . '_' . md5(json_encode($keyComponents, JSON_UNESCAPED_SLASHES));
    }

    private function getCache($key)
    {
        if (!$this->cacheEnable) return false;

        $file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $this->cacheDir . DIRECTORY_SEPARATOR . strtolower(get_class($this)) . DIRECTORY_SEPARATOR . $key;
        if (file_exists($file) && (time() - filemtime($file) < $this->cacheTimeout)) {
            return unserialize(gzuncompress(file_get_contents($file)));
        }

        return false;
    }

    private function setCache($key, $data = NULL)
    {
        if (!$this->cacheEnable) return;

        if (!empty($data)) {
            $file = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $this->cacheDir . DIRECTORY_SEPARATOR . strtolower(get_class($this)) . DIRECTORY_SEPARATOR . $key;
            file_put_contents($file, gzcompress(serialize($data)));
        }
    }

    private function deleteCache($path = NULL)
    {
        if (!$this->cacheEnable) {
            return;
        }

        // Set default path if none provided
        if (is_null($path)) {
            $path = APPPATH . 'cache' . DIRECTORY_SEPARATOR . $this->cacheDir . DIRECTORY_SEPARATOR;
        }

        // Return if path doesn't exist
        if (!file_exists($path)) {
            return;
        }

        // If path is a file, delete it directly
        if (is_file($path)) {
            unlink($path);
            return;
        }

        // Handle directory
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                $this->deleteCache($fullPath);
            }

            rmdir($path);
        }
    }

    /**
     * Normalizes a SQL query for consistent cache key generation
     * 
     * @param string $query
     * @return string
     */
    private function normalizeQuery($query)
    {
        // Remove extra whitespace
        $query = preg_replace('/\s+/', ' ', trim($query));

        // Sort WHERE conditions alphabetically
        if (preg_match('/WHERE\s+(.*?)(?:ORDER|GROUP|LIMIT|$)/i', $query, $matches)) {
            $whereClause = $matches[1];
            $conditions = explode(' AND ', $whereClause);
            sort($conditions);
            $newWhereClause = implode(' AND ', $conditions);
            $query = str_replace($whereClause, $newWhereClause, $query);
        }

        // Sort ORDER BY clauses
        if (preg_match('/ORDER BY\s+(.*?)(?:LIMIT|$)/i', $query, $matches)) {
            $orderClause = $matches[1];
            $orders = explode(',', $orderClause);
            sort($orders);
            $newOrderClause = implode(',', $orders);
            $query = str_replace($orderClause, $newOrderClause, $query);
        }

        return $query;
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
        // $this->_database->close();
        $this->_database->trans_off();
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

    private function _cloneDatabaseSettings()
    {
        return [
            'connection' => $this->connection,
            'table' => $this->table,
            'primaryKey' => $this->primaryKey,
            'relations' => $this->relations,
            'eagerLoad' => $this->eagerLoad,
            'returnType' => $this->returnType,
            '_paginateColumn' => $this->_paginateColumn,
            'db' => clone $this->_database
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
        $query = $this->_database->from($this->table);
        $callback($query);
        return $query->get_compiled_select();
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

        if ($value < 1900 || $value > date('Y')) {
            throw new InvalidArgumentException("Invalid year: $value");
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
        $this->returnType = 'array';
        $this->_paginateColumn = [];
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
     * Enable safe output against XSS injection with exception key
     *
     * @param array $exception The key array that will be except from sanitize
     * @return $this
     */
    public function safeOutputWithException($exception = [])
    {
        $this->_secureOutput = true;
        $this->_secureOutputException = $exception;
        return $this;
    }

    /**
     * Sanitize output data if safe output is enabled
     *
     * @param mixed $data Data to sanitize
     * @return mixed
     */
    private function _safeOutputSanitize($data)
    {
        if (!$this->_secureOutput) {
            return $data;
        }

        // Early return if data is null or empty
        if (is_null($data) || $data === '') {
            return $data;
        }

        return $this->sanitize($data);
    }

    /**
     * Recursively sanitize data
     *
     * @param mixed $value Value to sanitize
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function sanitize($value = null)
    {
        // Check if $value is not null or empty
        if (!isset($value) || is_null($value)) {
            return $value;
        }

        // If $value is an array, sanitize its values while checking each key against the exception list
        if (is_array($value)) {
            if (!empty($this->_secureOutputException)) {
                foreach ($value as $key => $item) {
                    // Check if the key exists in the exception list
                    if (in_array($key, $this->_secureOutputException)) {
                        continue; // Skip sanitization for this key
                    }

                    // Recursively sanitize the value for this key
                    $value[$key] = $this->sanitize($item);
                }

                return $value;
            } else {
                return array_map([$this, 'sanitize'], $value);
            }
        }

        // Sanitize input based on data type
        switch (gettype($value)) {
            case 'string':
                if (isset($this->_secureOutputException) && in_array($value, $this->_secureOutputException)) {
                    return $value; // Skip sanitization if the string is in the exception list
                }
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'); // Apply XSS protection and trim
            case 'integer':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            case 'double':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'boolean':
                return (bool) $value;
            default:
                // Handle unexpected data types (consider throwing an exception)
                throw new \InvalidArgumentException("Unsupported data type for sanitization: " . gettype($value));
        }
    }

    /**
     * Recursively removes hidden keys from the given data array.
     *
     * This method takes an array ($data) and an array of hidden keys ($hidden).
     * It removes keys listed in the $hidden array from $data.
     * If a value in $data is an array, the method is called recursively.
     *
     * @param array $data The data array from which to remove hidden keys.
     * @return array The modified data array with hidden keys removed.
     */
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
            foreach ($resultQuery as &$item) {
                $this->appendToSingle($item, $appendMethods);
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
}