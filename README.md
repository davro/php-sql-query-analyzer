# SQLQueryAnalyzer

SQLQueryAnalyzer is a PHP class designed to estimate the computational complexity of SQL queries based on their execution plans. This can help developers understand the performance characteristics of their queries and identify potential bottlenecks.

## Features

- Estimates the computational complexity of SQL queries.
- Analyzes subqueries and includes their complexity in the overall estimate.
- Supports a variety of query types, including full table scans, index scans, range scans, and constant lookups.

## Requirements

- PHP 7.4 or higher
- PDO extension enabled
- A MySQL database

## Installation

You can install SQLQueryAnalyzer via Composer. Add the following to your `composer.json` file:

```json
{
    "require": {
        "davro/sql-query-analyzer": "dev-main"
    }
}
```

## Usage
```
<?php
require 'vendor/autoload.php';

use Davro\SQLQueryAnalyzer;

// Create a PDO instance
$pdo = new PDO('mysql:host=your_host;dbname=your_db', 'your_username', 'your_password');

// Define your SQL query
$sql = "SELECT e.name, d.department_name, p.project_name
        FROM Employees e
        JOIN Departments d ON e.department_id = d.department_id
        JOIN Projects p ON e.employee_id = p.employee_id
        WHERE e.salary > (
            SELECT AVG(salary)
            FROM Employees
            WHERE department_id = e.department_id
        )
        AND e.department_id IN (
            SELECT department_id
            FROM Departments
            WHERE location IN (
                SELECT location
                FROM Offices
                WHERE region = 'North America'
            )
        )
        ORDER BY e.name";

// Get the execution plan for the query
$stmt = $pdo->prepare('EXPLAIN ' . $sql);
$stmt->execute();
$executionPlan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an instance of SQLQueryAnalyzer
$analyzer = new SQLQueryAnalyzer();

// Estimate the complexity of the query
$complexity = $analyzer->estimateComplexity($pdo, $executionPlan);

// Print the estimated complexity
echo $complexity;
?>
```


## Methods

### `estimateComplexity(PDO $pdo, array $executionPlan): string`

Estimates the complexity of an SQL query based on its execution plan.

**Parameters**:
- `PDO $pdo`: The PDO connection object.
- `array $executionPlan`: The execution plan of the SQL query.

**Returns**: 
- A string describing the estimated complexity of the query.

### `getSubqueryPlan(PDO $pdo, string $query): array`

Retrieves the execution plan for a subquery.

**Parameters**:
- `PDO $pdo`: The PDO connection object.
- `string $query`: The subquery for which to get the execution plan.

**Returns**:
- An array representing the execution plan of the subquery.

## License

This project is licensed under the GNU General Public License - see the [LICENSE](LICENSE) file for details.

## Author

David Stevens - [mail.davro@gmail.com](mailto:mail.davro@gmail.com)

