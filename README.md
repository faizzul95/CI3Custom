# ePMP

Project Name : e-PMP

Client : Dalilah (MPKL)

Year Project : 2024

Requirement System
- PHP 8.0 or above
- MySQL

Framework
- Bootstrap v5.0
- CodeIgniter v3.2.0 (Beta)

<hr>

<details> 
<summary> DESCRIPTION </summary>
<hr>
<p> N/A </p>
<br/>
</details> 

<details> 
<summary> FEATURES </summary>
<hr>
  
- SECURITY
	1) XSS Protection (validate data from malicious code using middleware)
	2) Google Authenticator (Use for 2FA)
	3) Google ReCAPTCHA v2 (Reduce DDos Attack)
	4) Login Attempt (Reduce Brute Force Attack)
	5) Custom Front-end Validation in JS (Data integrity)
	6) Custom Route & Middleware (Protect URL & Page) - Thanks <a href="https://github.com/ingeniasoftware/luthier-ci" target="_blank"> Luthier CI </a> for amazing library
	7) CSRF Token & Cookie (Built in CI3)
	8) Rate Limiting Trait (API Request limitter using Middleware)

- SYSTEM
	1) Custom Model DB Query. 
	2) Job Queue (Worker) - Running in background (Thanks to <a href="https://github.com/yidas/codeigniter-queue-worker" target="_blank"> Yidas </a> for Queue Worker library)
	3) Maintenance Mode (With custom page)
	4) Blade Templating Engine (Increase security & caching) - (Credit to team <a href="https://github.com/EFTEC/BladeOne" target="_blank">BladeOne</a>)
	5) SSL Force redirect (production mode)
	6) System logger (Log error system in database & files)
	7) Audit Trail (Log data insert, update, delete in database)
	8) CRUD Log (Log data insert, update, delete in files)
	9) Cron Scheduler - (Credit to <a href="https://github.com/peppeocchi/php-cron-scheduler" target="_blank">Peppeocchi</a>)

- HELPER
	<ol type="A">
	<li> Front-end </li> 
	<ol type="1">
		<li> Call API (POST, GET), Upload API, Delete API wrapper (using axios) </li>
		<li> Dynamic modal & Form loaded </li>
		<li> Generate datatable (server-side & client-side rendering) </li>
		<li> Print DIV (use <a href="https://jasonday.github.io/printThis/" target="_blank">printThis</a> library) </li>
	</ol> 
	<br>
	<li> Backend-end </li> 
	<ol type="1">
		<li> Array helper </li>
		<li> Data Helper </li>
		<li> Date Helper </li>
		<li> Upload Helper (upload, move, compress image) </li>
		<li> QR Generate Helper (using <a href="https://github.com/endroid/qr-code" target="_blank">Endroid</a> library) </li>
		<li> Read/Import Excel (using <a href="https://github.com/PHPOffice/PhpSpreadsheet" target="_blank">PHPSpreadsheet</a> library) </li>
		<li> Mailer (using <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a> library) </li>
	</ol>
	</ol>
			
- SERVICES
	1) Backup system folder (with exceptions file or folder)
	2) Backup database (MySQL tested)
	3) Upload file backup to google drive (need to configure)

<br/>
</details> 

<details> 
<summary> COMMAND </summary>
<hr>

Command (Terminal / Command Prompt):-

<ol type="A">
	<li> Cache </li> 
		<ol type="1">
			<li> php struck clear view (remove blade cache)  </li>
			<li> php struck clear cache (remove ci session cache)  </li>
			<li> php struck clear all (remove ci session cache, blade cache & logs file)  </li>
			<li> php struck optimize (remove blade cache & logs file)  </li>
		</ol> 
	<br>
	<li> Backup (use as a ordinary cron jobs) </li> 
		<ol type="1">
			<li> php struck cron database (backup the database in folder project) </li>
			<li> php struck cron system (backup system folder in folder project) </li>
			<li> php struck cron database upload (backup the database & upload to google drive) </li>
			<li> php struck cron system upload (backup system folder & upload to google drive) </li>
		</ol> 
	<br>
	<li> Jobs (Queue Worker) </li> 
		<ol type="1">
			<li> php struck jobs (temporary run until jobs completed) </li>
			<li> php struck jobs work (temporary run until jobs completed) </li>
			<li> php struck jobs launch (permanent until services kill) - use in linux environment </li>
		</ol> 
	<br>
		<li> Cron Scheduler (Laravel Task Scheduling) </li> 
		<ol type="1">
			<li> php struck schedule:run </li>
			<li> php struck schedule:list </li>
			<li> php struck schedule:work </li>
			<li> php struck schedule:fail </li>
		</ol> 
	<br>
</ol>
 <br/>
</details> 

# EXTENDED  

### Path : `application/core/MY_Model`

#### Description
`MY_Model` is an extended model class for CodeIgniter 3 that introduces advanced query capabilities, soft delete records, improved relationship handling, fixes the N+1 query issue, and adds security layers for interacting with databases. 

#### Database Support
`MySQL`

#### Example Model

<details> 
<summary> Click to view model example </summary>
  
```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName>_model extends MY_Model
{
    // (OPTIONAL) The connection to database based on configuration, default is 'default'
    public $connection = 'default';

    // (REQUIRED) The name of the table
    public $table = '';
    
    // (REQUIRED) The primary key column name, default is 'id'
    public $primaryKey = 'id'; 
    
    // (REQUIRED) The fields that can be filled by insert/update
    public $fillable = [];
    
    // (OPTIONAL) Enable or disable timestamps (created_at & updated_at)
    public $timestamps = TRUE;

    // (OPTIONAL) The timestamp format to save in database, default is 'Y-m-d H:i:s'
    public $timestamps_format = 'Y-m-d H:i:s';

    // (OPTIONAL) The timezone for the timestamp, default is 'Asia/Kuala_Lumpur'
    public $timezone = 'Asia/Kuala_Lumpur';

    // (OPTIONAL) The timestamp column that expected to be saved
    public $created_at = 'created_at';
    public $updated_at = 'updated_at';
    public $deleted_at = 'deleted_at';

    // (OPTIONAL) The soft delete records, expected the existing 'deleted_at' or $_deleted_at_field fields.
    public $softDelete = false;
    
    // (OPTIONAL) Will append the query result with new data from a specific function
    public $appends = [];
    
    // (OPTIONAL) Will remove specific columns from the query result
    public $hidden = [];
    
    // (OPTIONAL) Columns defined here will not be updated or inserted
    public $protected = [];

    // (OPTIONAL) The validation rules for insert & update
    public $_validationRules = [];

    // (OPTIONAL) The validation rules for insert only, will override the $_validation if exists.
    public $_insertValidation = []; 

    // (OPTIONAL) The validation rules for update only, will override the $_validation if exists.
    public $_updateValidation = [];

    public function __construct()
    {
        parent::__construct();
    }
}
```
</details> 

<hr>

#### Query Functions

| Function        | Description                                                                                                                                       |
|-----------------|---------------------------------------------------------------------------------------------------------------------------------------------------|
| `rawQuery()`    | Execute raw SQL queries directly. Useful for complex queries not supported by active record.                                                      |
| `table()`       | Specifies the database table for the query.                                                                                                       |
| `select()`      | Defines the columns to retrieve in a query. Similar to CodeIgniter’s `select()`.                                                                  |
| `where()`       | Adds a basic WHERE clause to the query. Similar to Laravel's `where()`.                                                                           |
| `orWhere()`     | Adds an OR WHERE clause. Similar to Laravel's `orWhere()`.                                                                                        |
| `whereNull()`   | Adds a WHERE clause to check for `NULL` values. Similar to Laravel's `whereNull()`.                                                               |
| `orWhereNull()` | Adds an OR WHERE clause to check for `NULL` values. Similar to Laravel's `orWhereNull()`.                                                         |
| `whereNotNull()`| Adds a WHERE clause to check for non-NULL values. Similar to Laravel's `whereNotNull()`.                                                          |
| `orWhereNotNull()`| Adds an OR WHERE clause to check for non-NULL values. Similar to Laravel's `orWhereNotNull()`.                                                  |
| `whereExists()` | Adds a WHERE EXISTS clause. Similar to Laravel's `whereExists()`.                                                                                 |
| `orWhereExists()`| Adds an OR WHERE EXISTS clause. Similar to Laravel's `orWhereExists()`.                                                                          |
| `whereNotExists()`| Adds a WHERE NOT EXISTS clause. Similar to Laravel's `whereNotExists()`.                                                                        |
| `orWhereNotExists()`| Adds an OR WHERE NOT EXISTS clause. Similar to Laravel's `orWhereNotExists()`.                                                                |
| `whereNot()`    | Adds a WHERE NOT clause for negating conditions. Similar to Laravel's `whereNot()`.                                                               |
| `orWhereNot()`  | Adds an OR WHERE NOT clause for negating conditions. Similar to Laravel's `orWhereNot()`.                                                         |
| `whereTime()`   | Adds a WHERE clause for a time comparison. Similar to Laravel's `whereTime()`.                                                                    |
| `orWhereTime()` | Adds an OR WHERE clause for a time comparison. Similar to Laravel's `orWhereTime()`.                                                              |
| `whereDate()`   | Adds a WHERE clause for a date comparison. Similar to Laravel's `whereDate()`.                                                                    |
| `orWhereDate()` | Adds an OR WHERE clause for a date comparison. Similar to Laravel's `orWhereDate()`.                                                              |
| `whereDay()`    | Adds a WHERE clause for a specific day. Similar to Laravel's `whereDay()`.                                                                        |
| `orWhereDay()`  | Adds an OR WHERE clause for a specific day. Similar to Laravel's `orWhereDay()`.                                                                  |
| `whereYear()`   | Adds a WHERE clause for a specific year. Similar to Laravel's `whereYear()`.                                                                      |
| `orWhereYear()` | Adds an OR WHERE clause for a specific year. Similar to Laravel's `orWhereYear()`.                                                                |
| `whereMonth()`  | Adds a WHERE clause for a specific month. Similar to Laravel's `whereMonth()`.                                                                    |
| `orWhereMonth()`| Adds an OR WHERE clause for a specific month. Similar to Laravel's `orWhereMonth()`.                                                              |
| `whereIn()`     | Adds a WHERE IN clause. Similar to Laravel's `whereIn()`.                                                                                         |
| `orWhereIn()`   | Adds an OR WHERE IN clause. Similar to Laravel's `orWhereIn()`.                                                                                   |
| `whereNotIn()`  | Adds a WHERE NOT IN clause. Similar to Laravel's `whereNotIn()`.                                                                                  |
| `orWhereNotIn()`| Adds an OR WHERE NOT IN clause. Similar to Laravel's `orWhereNotIn()`.                                                                            |
| `whereBetween()`| Adds a WHERE BETWEEN clause. Similar to Laravel's `whereBetween()`.                                                                               |
| `orWhereBetween()`| Adds an OR WHERE BETWEEN clause. Similar to Laravel's `orWhereBetween()`.                                                                       |
| `whereNotBetween()`| Adds a WHERE NOT BETWEEN clause. Similar to Laravel's `whereNotBetween()`.                                                                     |
| `orWhereNotBetween()`| Adds an OR WHERE NOT BETWEEN clause. Similar to Laravel's `orWhereNotBetween()`.                                                             |
| `join()`        | Adds an INNER JOIN to the query. Similar to CodeIgniter’s `join()`.                                                                               |
| `rightJoin()`   | Adds a RIGHT JOIN to the query. Similar to Laravel's `rightJoin()`.                                                                               |
| `leftJoin()`    | Adds a LEFT JOIN to the query. Similar to Laravel's `leftJoin()`.                                                                                 |
| `innerJoin()`   | Adds an INNER JOIN to the query. Same as `join()`.                                                                                                |
| `outerJoin()`   | Adds an OUTER JOIN to the query. Similar to Laravel's `outerJoin()`.                                                                              |
| `limit()`       | Limits the number of records returned. Similar to CodeIgniter's `limit()`.                                                                        |
| `offset()`      | Skips a number of records before starting to return records. Similar to CodeIgniter's `offset()`.                                                 |
| `orderBy()`     | Adds an ORDER BY clause. Similar to Laravel's `orderBy()`.                                                                                        |
| `groupBy()`     | Adds a GROUP BY clause. Similar to Laravel's `groupBy()`.                                                                                         |
| `groupByRaw()`  | Adds a raw GROUP BY clause. Similar to Laravel's `groupByRaw()`.                                                                                  |
| `having()`      | Adds a HAVING clause. Similar to Laravel's `having()`.                                                                                            |
| `havingRaw()`   | Adds a raw HAVING clause. Similar to Laravel's `havingRaw()`.                                                                                     |
| `chunk()`       | Process data in chunks to handle large datasets efficiently. Similar to Laravel's `chunk()`.                                                      |
| `get()`         | Retrieves all data from the database based on the specified criteria.                                                                             |
| `fetch()`       | Retrieves a single record from the database based on the specified criteria.                                                                      |
| `first()`       | Retrieves the first record based on the query.                                                                                                    |
| `last()`        | Retrieves the last record based on the query.                                                                                                     |
| `count()`       | Counts the number of records matching the specified criteria.                                                                                     |
| `find()`        | Finds a record by its primary key (ID).                                                                                                           |
| `withTrashed()` | Retrieves both soft deleted and non-deleted records from the database. When using this method, the results include records that have been soft deleted (i.e., those with a `deleted_at` timestamp) alongside active records.                                                                                                                                         |
| `onlyTrashed()` | Retrieves only the records that have been soft deleted (i.e., records with a `deleted_at` timestamp). This method excludes active (non-deleted) records from the query results. |
| `toSql()`       | Returns the SQL query string (without eager loading query).                                                                                       |

<details> 
<summary> Example Usage of rawQuery($query, $binding) </summary>
  
#### Description
<b>Parameters:</b><br>
`$query` (string): A string query to be executed. <br>
`$binding` (array): An array binding. 
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleSelectRawQueryWithoutBinds()
    {
        $query = $this->any_model->rawQuery('SELECT * FROM exampleTable WHERE id = 1 GROUP BY name');
        echo json_encode($query);
    }

    public function exampleInsertRawQueryWithBinds()
    {
        $query = $this->any_model->rawQuery("INSERT INTO exampleTable (column1, column2) VALUES ('value1', 'value2')");
        echo json_encode($query);
    }

    public function exampleSelectRawQueryWithBinds()
    {
        $query = $this->any_model->rawQuery('SELECT * FROM exampleTable WHERE id = ? GROUP BY name', [1]);
        echo json_encode($query);
    }

    public function exampleInsertRawQueryWithBinds()
    {
        $query = $this->any_model->rawQuery('INSERT INTO exampleTable (column1, column2) VALUES (?, ?)', ['value1', 'value2']);
        echo json_encode($query);
    }

    public function exampleUpdateRawQueryWithBinds()
    {
        $query = $this->any_model->rawQuery('UPDATE exampleTable SET column1 = ? WHERE id = ?', ['value1', 10]);
        echo json_encode($query);
    }
}
```
</details>

<details> 
<summary> Example Usage of where($column, $operator, $value) / orWhere($column, $operator, $value) </summary>
  
#### Description

The `where()` method adds a WHERE clause to your SQL query to filter results based on specific conditions. It supports a variety of operators for different comparison types.

<b>Parameters:</b><br>
- `$column` (string/callback): The name of the column to compare. It can also be a callback function to create a more complex query.
- `$operator` (string): The comparison operator used to filter results. Supported operators are: ['=', '!=', '<', '>', '<=', '>=', '<>', 'LIKE', 'NOT LIKE'].
- `$value` (mixed): The value to compare against the column. This can be a string, number, or pattern (for LIKE/NOT LIKE).

<b>Example Usage:</b>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    // Example without specifying an operator, defaults to '='
    public function exampleWhereWithoutOperator()
    {
        $data = $this->any_model->where('column_name', 'value')->get();
        print_r($data);
    }

    // Example with the '=' operator
    public function exampleWhereWithOperatorEqual()
    {
        $data = $this->any_model->where('column_name', '=', 'value')->get();
        print_r($data);
    }

    // Example with the '!=' operator
    public function exampleWhereWithOperatorNotEqual()
    {
        $data = $this->any_model->where('column_name', '!=', 'value')->get();
        print_r($data);
    }

    // Example with the '<' operator
    public function exampleWhereWithOperatorLessThan()
    {
        $data = $this->any_model->where('column_name', '<', 'value')->get();
        print_r($data);
    }

    // Example with the '>' operator
    public function exampleWhereWithOperatorMoreThan()
    {
        $data = $this->any_model->where('column_name', '>', 'value')->get();
        print_r($data);
    }

    // Example with the 'LIKE' operator
    public function exampleWhereWithOperatorLike()
    {
        $data = $this->any_model->where('column_name', 'LIKE', '%value%')->get();
        print_r($data);
    }

    // Example with the 'NOT LIKE' operator
    public function exampleWhereWithOperatorNotLike()
    {
        $data = $this->any_model->where('column_name', 'NOT LIKE', '%value%')->get();
        print_r($data);
    }

    // Example with the column as a callback for complex WHERE condition
    public function exampleWhereWithColumnAsCallback()
    {
        $data = $this->any_model->where(function ($query) {
            $query->where('column_name2', 'LIKE', '%value%');
        })->get();
        
        print_r($data);
    }
}
```
</details>

<details> 
<summary> Example Usage of whereNull($column) / orWhereNull($column) / whereNotNull($column) / orWhereNotNull($column) </summary>
  
#### Description
<b>Parameters:</b><br>
`$column` (string): The name of the column to compare. 
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleWhereNullOnly()
    {
        $data = $this->any_model->whereNull('column_name')->get();
        print_r($data);
    }

    public function exampleWhereNullWithOrWhereNull()
    {
        $data = $this->any_model->whereNull('column_name1')->orWhereNull('column_name2')->first();
        print_r($data);
    }

    public function exampleWhereNotNullOnly()
    {
        $data = $this->any_model->whereNotNull('column_name')->get();
        print_r($data);
    }

    public function exampleWhereNotNullWithOrWhereNotNull()
    {
        $data = $this->any_model->whereNotNull('column_name1')->orWhereNotNull('column_name2')->first();
        print_r($data);
    }
}
```
</details>


<details> 
<summary> Example Usage of chunk($batchSize, $callback)</summary>
  
#### Description

The `chunk()` method processes a large dataset by breaking it into smaller batches, which can be especially useful for memory management when handling a significant number of records. It retrieves records in chunks of the specified size and applies the callback to each batch. This can be helpful for tasks like exporting data, processing background jobs, or purging old records.

<b>Parameters:</b><br>
- `$batchSize` (integer): The number of records to retrieve per chunk. This determines how many records are processed in each batch.
- `$callback` (callback): A function that is invoked for each batch of data retrieved. The callback will receive the chunk of data as a parameter, allowing you to process each batch individually (e.g., exporting, deleting records, etc.).

#### Example Usage:

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
        $this->load->model('example_model');
    }

    // Example to purge old records in chunks
    public function exampleChunkToPurgeOldRecords()
    {
        return $this->any_model
                ->where('column1', 1)  // Filter to find the desired records
                ->whereYear('created_at', '<=', 2023) // Additional filter for older records
                ->chunk(500, function ($data)  {
                    foreach ($data as $row) {
                        $this->any_model->destroy($row['id']);
                    }
                });
    }

    // Example to export records to an Excel file in chunks
    public function exampleChunkToExportExcelFiles()
    {
        $filesExcel = fopen('exported_file.xlsx', 'w'); // Create and open the Excel file

        $process = $this->any_model
                ->where('column1', 1)  // Filter for the required records
                ->whereYear('created_at', '>', 2023)  // Filter records based on date
                ->chunk(500, function ($data) use ($filesExcel) {
                    // Append the data in Excel for exporting
                    foreach ($data as $row) {
                        // Convert $row data to Excel format and write it to the file
                        fputcsv($filesExcel, (array) $row);
                    }
                });

        fclose($filesExcel); // Close the Excel file

        // Force the download of the file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="exported_file.xlsx"');
        readfile('exported_file.xlsx');
        return true;
    }
}
```
</details>

<hr>

#### Pagination Functions

| Function                 | Description                                                                                                                                      |
|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `setPaginateFilterColumn()` | Sets the filter conditions for pagination. If not set, all columns from the main table are queried.                                           |
| `paginate()`             | Custom pagination method that works without the datatable library. Allows paginating results based on the specified criteria.                    |
| `paginate_ajax()`        | Pagination method specifically designed to work with AJAX requests and integrate with datatables.                                                |

<details> 
<summary> Example Usage of setPaginateFilterColumn($key) </summary>
  
#### Description
<b>Parameters:</b><br>
`$key` (array): An array containing the list of column to be used when making the filter conditions in query (only support main table, eager load are not supported). <br>
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleSetPaginateFilter()
    {
        $paginateData = $this->any_model->whereYear('created_at', '>=', 2024)
            ->setPaginateFilterColumn(
                [
                    null, // used for count the number
                    'column1',
                    'column2'
                ]
            )
            ->paginate_ajax($_POST);

        if (isset($paginateData['data']) && !empty($paginateData['data'])) {
            $i = $_POST['start'] + 1;
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [
                    '<a href="javascript:void(0);" onclick="deleteConfirm(' .  $data['id'] . ')"  data-id="' .  $data['id'] . '"> Delete </a>',
                    '<a href="javascript:void(0);" onclick="updateRecord(' .  $data['id'] . ')" data-id="' .  $data['id'] . '"> Edit </a>',
                ];

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $i++,
                    $member['column1'],
                    $member['column2']
                    '<div class="text-center">' . implode('|', $actionArr) . '</div>'
                ];
            }
        }

        echo json_encode($paginateData);
    }
}
```
</details>

<details> 
<summary> Example Usage of paginate($pageSize, $currentPage, $searchValue) </summary>
  
#### Description
<b>Parameters:</b><br>
`$pageSize` (string): The size of the data want to display, default is 10. [OPTIONAL]<br>
`$currentPage` (string): The current page. Will taking from the url `?page=` if this value is not provide or null [OPTIONAL]<br>
`$searchValue` (string): The search value to filter the records [OPTIONAL]<br>
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function examplePaginate()
    {
        $search = $this->input->post('search', TRUE);
        $paginateData = $this->any_model->whereYear('created_at', '>=', 2024)->paginate(30, 19, $search);

        if (isset($paginateData['data']) && !empty($paginateData['data'])) {
            $i = $paginateData['data'];
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [
                    '<a href="javascript:void(0);" onclick="deleteConfirm(' .  $data['id'] . ')"  data-id="' .  $data['id'] . '"> Delete </a>',
                    '<a href="javascript:void(0);" onclick="updateRecord(' .  $data['id'] . ')" data-id="' .  $data['id'] . '"> Edit </a>',
                ];

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $i++,
                    $member['column1'],
                    $member['column2']
                    '<div class="text-center">' . implode('|', $actionArr) . '</div>'
                ];
            }
        }

        echo json_encode($paginateData);
    }
}
```
</details>

<details> 
<summary> Example Usage of paginate_ajax($dataPost) </summary>
  
#### Description
<b>Parameters:</b><br>
`$dataPost` (array): An array $_POST from the request ajax datatable. <br>
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function examplePaginateAjaxWithoutFilterColumn()
    {
        $paginateData = $this->any_model->whereYear('created_at', '>=', 2024)->paginate_ajax($_POST);

        if (isset($paginateData['data']) && !empty($paginateData['data'])) {
            $i = $_POST['start'] + 1;
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [
                    '<a href="javascript:void(0);" onclick="deleteConfirm(' .  $data['id'] . ')"  data-id="' .  $data['id'] . '"> Delete </a>',
                    '<a href="javascript:void(0);" onclick="updateRecord(' .  $data['id'] . ')" data-id="' .  $data['id'] . '"> Edit </a>',
                ];

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $i++,
                    $member['column1'],
                    $member['column2']
                    '<div class="text-center">' . implode('|', $actionArr) . '</div>'
                ];
            }
        }

        echo json_encode($paginateData);
    }

    public function examplePaginateAjaxWithFilterColumn()
    {
        $paginateData = $this->any_model->whereYear('created_at', '>=', 2024)
            ->setPaginateFilterColumn(
                [
                    null, // used for count the number
                    'column1',
                    'column2'
                ]
            )
            ->paginate_ajax($_POST);

        if (isset($paginateData['data']) && !empty($paginateData['data'])) {
            $i = $_POST['start'] + 1;
            foreach ($paginateData['data'] as $key => $data) {

                $actionArr = [
                    '<a href="javascript:void(0);" onclick="deleteConfirm(' .  $data['id'] . ')"  data-id="' .  $data['id'] . '"> Delete </a>',
                    '<a href="javascript:void(0);" onclick="updateRecord(' .  $data['id'] . ')" data-id="' .  $data['id'] . '"> Edit </a>',
                ];

                // Replace the data with formatted data
                $paginateData['data'][$key] = [
                    $i++,
                    $member['column1'],
                    $member['column2']
                    '<div class="text-center">' . implode('|', $actionArr) . '</div>'
                ];
            }
        }

        echo json_encode($paginateData);
    }
}
```
</details>

<hr>

#### Relationship Functions (in model only)

| Function      | Description                                                                                                                                      |
|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `hasMany()`   | Defines a one-to-many relationship. Similar to Laravel's `hasMany()`.                                                                            |
| `hasOne()`    | Defines a one-to-one relationship. Similar to Laravel's `hasOne()`.                                                                              |
| `belongsTo()` | Defines an inverse one-to-many or one-to-one relationship. Similar to Laravel's `belongsTo()`.                                                   |

<details> 
<summary> Example Usage of hasMany($modelName, $foreignKey, $localKey) </summary>
  
#### Description
<b>Parameters:</b><br>
`$modelName` (string): Indicate the model that want to has the relation [REQUIRED]<br>
`$foreignKey` (string): Indicate the foreign key in related model [REQUIRED] <br>
`$localKey` (string): Indicate the key in current model, usually will taking from $primaryKey value [OPTIONAL]
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function relatedModelWithLocalKey()
    {
         return $this->hasMany('Related_model', 'foreign_id', 'id');
    }

    public function relatedModelWithoutLocalKey()
    {
         return $this->hasMany('Related_model', 'foreign_id');
         // Explanation : will use $primaryKey value as the localKey
    }
}
```
</details> 

<details> 
<summary> Example Usage of hasOne($modelName, $foreignKey, $localKey) </summary>
  
#### Description
<b>Parameters:</b><br>
`$modelName` (string): Indicate the model that want to has the relation [REQUIRED]<br>
`$foreignKey` (string): Indicate the foreign key in related model [REQUIRED] <br>
`$localKey` (string): Indicate the key in current model, usually will taking from `$primaryKey` value [OPTIONAL]
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function relatedModelWithLocalKey()
    {
         return $this->hasOne('Related_model', 'foreign_id', 'id');
    }

    public function relatedModelWithoutLocalKey()
    {
         return $this->hasOne('Related_model', 'foreign_id');
         // Explanation : will use $primaryKey value as the localKey
    }
}
```
</details> 

<details> 
<summary> Example Usage of belongsTo($modelName, $foreignKey, $ownerKey) </summary>
  
#### Description
<b>Parameters:</b><br>
`$modelName` (string): Indicate the model that want to has the relation [REQUIRED]<br>
`$foreignKey` (string): Indicate the foreign key in related model [REQUIRED] <br>
`$ownerKey` (string): Indicate the key in current model, usually will taking from `$primaryKey` value [OPTIONAL]
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function relatedModelWithOwnerKey()
    {
         return $this->belongsTo('Related_model', 'foreign_id', 'id');
    }

    public function relatedModelWithoutOwnerKey()
    {
         return $this->belongsTo('Related_model', 'foreign_id');
         // Explanation : will use $primaryKey value as the ownerKey
    }
}
```
</details> 

<hr>

#### Eager Load Functions

| Function   | Description                                                                                                                                      |
|------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `with()`   | Eager loads related models to avoid the N+1 query issue. Similar to Laravel's `with()`.                                                          |

<details> 
<summary> Example Usage of with($relation) </summary>
  
#### Description
<b>Parameters:</b><br>
`$relation` (array/string/callback): An relation to extends the result with related table. 
<br>

```php
<?php

# MODEL : MAIN / PARENT

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function related1()
    {
        return $this->hasMany('Related1_model', 'anyPK_id', 'id');
    }
}

# MODEL : RELATED / CHILD

class Related1_model extends MY_Model
{
    public $table = 'relatedTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'columnRelated1',
        'columnRelated2',
        'columnRelated3',
        'columnRelated4',
        'anyPK_id'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function function1()
    {
        return $this->hasMany('Related2_model', 'anyRelatedPK_id', 'id');
    }

    public function function2()
    {
        return $this->hasOne('Related3_model', 'anyRelatedPK_id', 'id');
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function simpleEagerLoadUsingParam()
    {
        return $this->any_model->select('id, column1, column2')
                ->whereYear('created_at', '>=', '2024')
                ->orderBy('id', 'DESC')
                ->with('related1', 'related1.function1', 'related1.function2') // USE PARAMS
                ->get(); // can used get(), fetch(), paginate(), first(), last().
    }

    public function simpleEagerLoadUsingArray()
    {
        return $this->any_model->select('id, column1, column2')
                ->whereYear('created_at', '>=', '2024')
                ->orderBy('id', 'DESC')
                ->with(['related1', 'related1.function1', 'related1.function2']) // USE ARRAY 
                ->fetch(); // can used get(), fetch(), paginate(), first(), last().
    }

    public function advancedEagerLoadUsingCallback()
    {
        return $this->model->select('id, name, email, nickname, password, username')
                ->whereYear('created_at', '>=', '2024')
                ->orderBy('id', 'DESC')
                ->with(['related1' => function ($query) {
                    $query->select('columnRelated1, columnRelated2')->whereMonth('created_at', '>=', 2);
                }])
                ->with(['related1.function1' => function ($query) {
                    $query->select('columnRelatedFunc1, columnRelatedFunc4');
                }])
                ->with('related1.function2')
                ->paginate(10, 3);  // can used get(), fetch(), paginate(), first(), last(). 
    }
}
```
</details> 

<hr>

#### CRUD Functions

| Function           | Description                                                                                                                                      |
|--------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `create()`         | Inserts a single new record in the database based on the provided data (will return the last inserted id).                                       |
| `batchCreate()`    | Inserts a multiple new record in the database based on the provided data in one operation.                                                       |
| `patch()`          | Updates a specific record by its primary key (ID) set at `$primaryKey` property in model.                                                          |
| `patchAll()`       | Updates multiple existing records based on specified conditions in one operation.                                                                |
| `batchPatch()`     | Updates a multiple existing record by using specific column/primarykey (does not required any where condition).                                  |
| `destroy()`        | Deletes a specific record by its primary key (ID) set at the `$primaryKey` property in the model. If soft delete is enabled ($softDelete = true), the record is not permanently removed but flagged as deleted by setting a `deleted_at` or `$_deleted_at_field` property to timestamp. |
| `destroyAll()`     | Deletes multiple records based on specified conditions. If soft delete is enabled ($softDelete = true), the record is not permanently removed but flagged as deleted by setting a `deleted_at` or `$_deleted_at_field` property to timestamp. |
| `forceDestroy()`   | Permanently deletes a specific record by its primary key (ID) set at the `$primaryKey` property in the model, bypassing the soft delete mechanism if it is enabled. This method removes the record entirely from the database.  |
| `insertOrUpdate()` | Determines whether to insert or update a record based on given conditions. Similar to Laravel's `updateOrInsert()`.                              |
| `restore()`        | Restores a soft-deleted record by removing the deleted_at timestamp, making the record active again. This method only applies to records that have been soft deleted (i.e., those with a non-null deleted_at timestamp). If soft deletes are enabled ($softDelete = true), the restore() function allows you to undo the soft delete and recover the record.  |
| `toSqlPatch()`  | Returns the SQL query string for updating data.                                                                                                    |
| `toSqlCreate()` | Returns the SQL query string for inserting single data.                                                                                            |
| `toSqlDestroy()`| Returns the SQL query string for deleting single data.                                                                                             |

<details> 
<summary> Example Usage of create($data) / batchCreate($data) / toSqlCreate($data) </summary>
  
#### Description
<b>Parameters:</b><br>
`$data` (array): An array of data to be inserted to database. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleCreate()
    {
        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->create($data)); // return as the json to ajax
    }

    public function exampleBatchCreate()
    {
        $data = [
            [
                'column1' => $this->input->post('column1', TRUE),
                'column2' => $this->input->post('column1', TRUE),
                'column3' => $this->input->post('column1', TRUE)
            ],
            [
                'column1' => $this->input->post('column1', TRUE),
                'column2' => $this->input->post('column1', TRUE),
                'column3' => $this->input->post('column1', TRUE)
            ],
            [
                'column1' => $this->input->post('column1', TRUE),
                'column2' => $this->input->post('column1', TRUE),
                'column3' => $this->input->post('column1', TRUE)
            ],
        ]

        echo json_encode($this->any_model->batchCreate($data)); // return as the json to ajax
    }

    public function exampleToSqlCreate()
    {
        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        print_r($this->any_model->toSqlCreate($data)); // Only return the query INSERT Statement without executing
    }
}
```
</details> 

<details> 
<summary> Example Usage of patch($data, $id) / toSqlPatch($data, $id) </summary>
  
#### Description
<b>Parameters:</b><br>
`$data` (array): An array of data to be update. <br>
`$id` (string): An id (must be a PK set at $primaryKey in model) value to specify which data to be updated. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleUpdate()
    {
        $id = $this->input->post('id', TRUE);

        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->patch($data, $id)); // return as the json to ajax
    }

    public function exampleToSqlPatch()
    {
        $id = $this->input->post('id', TRUE);

        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        print_r($this->any_model->toSqlPatch($data, $id)); // Only return the query UPDATE Statement without executing
    }
}
```
</details> 

<details> 
<summary> Example Usage of patchAll($data) </summary>
  
#### Description
<b>Parameters:</b><br>
`$data` (array): An array of data to be update. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function examplePatchAll()
    {
        // The list of column that will be updated with value
        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->whereYear('create_at', '<', 2024)->patchAll($data)); // return as the json to ajax
        // Explanation : All the data that has been created before year 2024 will be updated with same value
        // Reminder : Please always be careful and provide the valid conditions before using this method or all the data in database will be updated to the provide data.
    }

    public function examplePatchAll2()
    {
        $id = [10, 100, 1000, 20, 200, 2000];

        // The list of column that will be updated with value
        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->whereIn('id', $id)->where('status', 'ACTV')->patchAll($data)); // return as the json to ajax
        // Explanation : All the data with the specific id and the status still active will be updated with same value
        // Reminder : Please always be careful and provide the valid conditions before using this method or all the data in database will be updated to the provide data.
    }
}
```
</details> 

<details> 
<summary> Example Usage of batchPatch($data, $customField) </summary>
  
#### Description
<b>Parameters:</b><br>
`$data` (array): An array of data to be update. <br>
`$customField` (string): Custom field name to use as the update key condition (default is primary key). [OPTIONAL]
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleBatchPatchWithoutCustomField()
    {
        $data = [
            [
                'id' => 1,
                'column1' => 'example value',
                'column2' => 'example value 2'
            ],
            [
                'id' => 2,
                'column1' => 'example value 3',
                'column2' => 'example value 4'
            ],
            ...
        ];

        echo json_encode($this->any_model->batchPatch($data)); // return as the json to ajax
        // Explanation : Without provide any custom fields value, it will using the default (primary key) as a condition.
        // Reminder : Please ensure the primary key value also included and not empty in the $data or the update process will be failed
    }

    public function exampleBatchPatchWithCustomField()
    {
        $data = [
            [
                'id' => 1,
                'column1' => 'example value',
                'column2' => 'example value 2'
            ],
            [
                'id' => 2,
                'column1' => 'example value 3',
                'column2' => 'example value 4'
            ],
            ...
        ];

        echo json_encode($this->any_model->batchPatch($data, 'column1')); // return as the json to ajax
        // Explanation : When provide the value for custom fields, it will use that key as the condition to update, example here is using 'column1' as a condition.
        // Reminder : Please ensure the 'column1' value also included and not empty in the $data or the update process will be failed
    }
}
```
</details> 

<details> 
<summary> Example Usage of destroy($id) / forceDestroy($id) / restore($id) / toSqlDestroy($id) </summary>
  
#### Description
<b>Parameters:</b><br>
`$id` (string): An id (must be a PK set at $primaryKey in model) value to specify which data to be deleted/restored. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleDelete()
    {
        $id = $this->input->post('id', TRUE);
        echo json_encode($this->any_model->destroy($id)); // return as the json to ajax
    }

    public function exampleForceDelete()
    {
        $id = $this->input->post('id', TRUE);
        echo json_encode($this->any_model->forceDestroy($id)); // return as the json to ajax
    }

    public function exampleRestore()
    {
        $id = $this->input->post('id', TRUE);
        echo json_encode($this->any_model->restore($id)); // return as the json to ajax
    }

    public function exampleToSqlDestroy()
    {
        $id = $this->input->post('id', TRUE);
        print_r($this->any_model->toSqlDestroy($id)); // Only return the query DELETE Statement without executing or if $softDelete is true will return the UPDATE Statement.
    }
}
```
</details> 

<details> 
<summary> Example Usage of destroyAll() </summary>
  
#### Description
<b>Parameters:</b><br>
- None
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleDestroyAll()
    {
        echo json_encode($this->any_model->whereIn('id', [1, 2, 3, 4, 5, 6])->destroyAll()); // return as the json to ajax
        // Reminder : Please always be careful and provide the valid conditions before using this method or all the data will be removed.
    }
}
```
</details> 

<details> 
<summary> Example Usage of insertOrUpdate($condition, $data) </summary>
  
#### Description
<b>Parameters:</b><br>
`$condition` (array): An array of data to be use as the condition to determine the records are exist or not in database. <br>
`$data` (array): An array of data to be insert or update. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleInsertOrUpdate1()
    {
        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->insertOrUpdate(['id' => 'value'], $data)); // return as the json to ajax
    }

    public function exampleInsertOrUpdate2()
    {
        $condition = ['id' => 'value', 'column4' => 'example']; // this both condition must be fit in database to be updated, otherwise, it will insert a new record.

        $data = [
            'column1' => $this->input->post('column1', TRUE),
            'column2' => $this->input->post('column1', TRUE),
            'column3' => $this->input->post('column1', TRUE)
        ];

        echo json_encode($this->any_model->insertOrUpdate($condition, $data)); // return as the json to ajax
    }
}
```
</details> 

<hr>

#### CRUD Validation Functions

| Function                   | Description                                                                                                                                      |
|----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `ignoreValidation()`        | Ignores all validation rules for inserts and updates.                                                                                           |
| `setValidationRules()`      | Sets or overrides existing validation rules for the model on the fly.                                                                           |
| `setCustomValidationRules()`| Adds or changes existing validation rules that are already set in the model.                                                                    |

<details> 
<summary> Example Usage of ignoreValidation() </summary>
  
```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    protected $_validation = [
        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'required'],
        'column4' => ['field' => 'column4', 'label' => 'Column 4', 'rules' => 'required|trim'] 
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleCreate()
    {
        return $this->any_model->ignoreValidation()->create($dataToInsert);
    }

    public function exampleUpdate()
    {
        return $this->any_model->ignoreValidation()->patch($dataToUpdate, $id);
    }
}
```
</details> 

<details> 
<summary> Example Usage of setValidationRules($rules) </summary>

#### Description
<b>Parameters:</b><br>
`$rules` (array): An array containing the set of rules. 
<br>
  
```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    // NO VALIDATION RULES SET HERE

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleCreate()
    {
        return $this->any_model
                    ->setValidationRules([
                        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'required|trim|max_length[255]'], // with required
                        'column2' => ['field' => 'column2', 'label' => 'Column 2', 'rules' => 'required|trim|valid_email', 'errors' => ['required' => 'Column 2 adalah wajib.']]
                    ])
                    ->create($dataToInsert);
    }

    public function exampleUpdate()
    {
        return $this->any_model
                    ->setValidationRules([
                        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'trim|max_length[255]'], // without required 
                        'column2' => ['field' => 'column2', 'label' => 'Column 2', 'rules' => 'required|trim', 'errors' => ['required' => 'Column 2 adalah wajib.']],
                    ])
                    ->patch($dataToUpdate, $id);
    }
}
```
</details> 

<details> 
<summary> Example Usage of setCustomValidationRules($rules) </summary>

#### Description
<b>Parameters:</b><br>
`$rules` (array): An array containing the set of rules to change or add. 
<br>
  
```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    protected $_validation = [
        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'required'], // Only have required
        'column4' => ['field' => 'column4', 'label' => 'Column 4', 'rules' => 'required|trim'] 
    ];

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleCreate()
    {
        return $this->any_model
                    ->setCustomValidationRules([
                        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'required|trim|max_length[255]'], // will override the existing validation on models.
                        'column2' => ['field' => 'column2', 'label' => 'Column 2', 'rules' => 'required|trim|valid_email'] // will add new validation for column 2
                    ])
                    ->create($dataToInsert);
    }

    public function exampleUpdate()
    {
        return $this->any_model
                    ->setCustomValidationRules([
                        'column1' => ['field' => 'column1', 'label' => 'Column 1', 'rules' => 'trim|max_length[255]'], // will override the existing validation on models.
                        'column3' => ['field' => 'column3', 'label' => 'Column 3', 'rules' => 'required|trim|valid_email', 'errors' => ['required' => 'Column 3 adalah wajib.']] // will add new validation for column 3
                    ])
                    ->patch($dataToUpdate, $id);
    }
}
```
</details> 

<hr>

#### Security Functions

| Function                   | Description                                                                                                                                      |
|----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `safeOutput()`             | Escapes output to prevent XSS attacks. All data, including eager loaded and appended data, will be filtered.                                     |
| `safeOutputWithException()`| Same as `safeOutput()`, but allows specific fields to be excluded from escaping.                                                                 |

<details> 
<summary> Example Usage of safeOutput() </summary>
  
```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function simpleSafeOutput()
    {
        return $this->any_model->where('column', 'value')->safeOutput()->get();
    }

    public function exampleEagerLoadSafeOutput()
    {
        return $this->any_model->where()
               ->with(['keyRelation' => function ($query) {
                     $query->safeOutput();
                }])
                ->safeOutput()
                ->get();
    }
}
```
</details> 

<details> 
<summary> Example Usage of safeOutputWithException($column) </summary>
  
#### Description
<b>Parameters:</b><br>
`$column` (array): An array containing the column to exclude from escaping. 
<br>

```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function simpleSafeOutputWithException()
    {
        return $this->any_model->where('column', 'value')->safeOutputWithException(['column1', 'column2'])->get();
    }

    public function exampleEagerLoadSafeOutputWithException()
    {
        return $this->any_model->where()
               ->with(['keyRelation' => function ($query) {
                     $query->safeOutputWithException(['columnRelation1', 'columnRelation2']);
                }])
              ->safeOutputWithException(['column1', 'column2'])
              ->get();
    }
}
```
</details> 

<hr>

#### Helper Functions

| Function                   | Description                                                                                                                                      |
|----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| `toArray()`                | Converts the result set to an array format (Default).                                                                                            |
| `toObject()`               | Converts the result set to an object format.                                                                                                     |
| `toJson()`                 | Converts the result set to JSON format.                                                                                                          |
| `showColumnHidden()`       | Displays hidden columns by removing the `$hidden` property temporarily.                                                                          |
| `setColumnHidden()`        | Dynamically sets columns to be hidden, similar to Laravel's `$hidden` model property.                                                            |
| `setAppends()`             | Dynamically appends custom attributes to the result set, similar to Laravel's `$appends` model property.                                         |

<details> 
<summary> Example Usage of toArray() / toObject() / toJson() </summary>
  
```php
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function example returnAsArrayData()
    {
        return $this->any_model->where('column', 'value')->toArray()->get();
    }

    public function example returnAsObjectData()
    {
        return $this->any_model->where('column', 'value')->toObject()->first();
    }

    public function example returnAsJsonData()
    {
        echo $this->any_model->where('column', 'value')->toJson()->fetch();
    }
}
```
</details> 


<details> 
<summary> Example Usage of showColumnHidden() </summary>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    protected $hidden = ['column3']; // Removed the column3 from showing in the result

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleReturnWitColumnHiddenInModel()
    {
        return $this->any_model->where('column', 'value')->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column4' => 'value4', 
        // ];
        // Explanation : column3 is not showing in the result because its already set hidden in the model.
    }

    public function exampleReturnWithoutColumnHidden()
    {
        return $this->any_model->where('column', 'value')->showColumnHidden()->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4' 
        // ];
        // Explanation : Will show all the column. 
    }
}
```
</details> 

<details> 
<summary> Example Usage of setColumnHidden($column) </summary>
  
#### Description
<b>Parameters:</b><br>
`$column` (array): An array containing the column to exclude from showing in the result. 
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    protected $hidden = ['column3']; // removed the column3 from showing in the result

    public function __construct()
    {
        parent::__construct();
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleReturnWithoutColumnHidden()
    {
        return $this->any_model->where('column', 'value')->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column4' => 'value4'
        // ];
        // Explanation : column3 is not showing in the result because its already set hidden in the model.
    }

    public function exampleReturnWithColumnHidden()
    {
        return $this->any_model->where('column', 'value')->setColumnHidden(['column1'])->fetch();
        // Result : [ 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4'
        // ];
        // Explanation : will override the $hidden in model. 
    }

    public function exampleReturnWithColumnHiddenSetToEmpty()
    {
        return $this->any_model->where('column', 'value')->setColumnHidden([])->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4'
        // ];
        // Explanation : will override the $hidden in model. Showing all the data since no column hidden are being set. Use `showColumnHidden()` instead.
    }
}
```
</details> 

<details> 
<summary> Example Usage of setAppends($key) </summary>
  
#### Description
<b>Parameters:</b><br>
`$key` (array): An array containing the functions to be called in the model. The key will be used to append as the new key in the result data. <br>

<b>Explanation:</b><br>
- This function requires creating a method with the prefix `get` and the suffix `Attribute` in the model. If the key contains an underscore ('_'), it will be automatically removed, and the first letter of the following word will be capitalized
<br><br>
Example : <br>
1) `new_data` will become `public function getNewDataAttribute()` - The 'n' and 'd' will be capitalized<br>
2) `testdata` will become `public function getTestdataAttribute()` - Only 't' will be capitalized<br>
3) `appendNewdata` will become `public function getAppendNewdataAttribute()` - Only 'a' will be capitalized<br>
4) `appendNew_data` will become `public function getAppendNewDataAttribute()` - The 'a' and 'd' will be capitalized
<br>

```php
<?php

# MODEL

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Any_model extends MY_Model
{
    public $table = 'anyTable';
    public $primaryKey = 'id'; 
    
    public $fillable = [
        'column1',
        'column2',
        'column3',
        'column4'
    ];

    protected $appends = ['new_data']; // If set in the model like this. all the query to this model will be append the new key

    public function __construct()
    {
        parent::__construct();
    }

    public function getNewDataAppendAttribute() 
    {
        return empty($this->column1) ? 'Will use this value a' : 'Will use this value b';
    }

    public function getOtherAppendExampleAttribute() 
    {
        return 'Will show this others value';
    }
}

# CONTROLLER

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class <ClassName> extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('any_model');
    }

    public function exampleWithoutAppends()
    {
        return $this->any_model->where('column1', 'value')->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4', 
        //     'new_data' => 'Will use this value b'
        // ];
        // Explanation : new_data key is a new data key result from the configuration using `$appends` property in the model.
    }

    public function exampleUsingSingleAppends()
    {
        return $this->any_model->where('column1', 'value')->setAppends(['other_append_example'])->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4', 
        //     'other_append_example' => 'Will show this others value'
        // ];
        // Explanation : other_append_example key is a new data key result. It will override the `$appends` property in the model.
    }

    public function exampleUsingBothAppends()
    {
        return $this->any_model->where('column1', 'value')->setAppends(['new_data', 'other_append_example'])->fetch();
        // Result : [
        //     'column1' => 'value', 
        //     'column2' => 'value2', 
        //     'column3' => 'value3', 
        //     'column4' => 'value4', 
        //     'new_data' => 'Will use this value b',
        //     'other_append_example' => 'Will show this others value'
        // ];
        // Explanation : new_data & other_append_example key is a new data key result. It will override the `$appends` property in the model.
    }
}
```
</details>
