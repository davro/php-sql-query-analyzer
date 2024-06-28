<?php

/**
 * SQL Query Analyzer
 *
 * This file contains the SQLQueryAnalyzer class which is used to estimate
 * the computational complexity of SQL queries based on their execution plans.
 *
 * PHP version 7.4
 *
 * @category Database
 * @package  SQLQueryAnalyzerPackage
 * @author   David Stevens <mail.davro@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License
 * @link     http://example.com/SQLQueryAnalyzer
 */

namespace Davro;

/**
 * Class SQLQueryAnalyzer
 *
 * Analyzes SQL queries and estimates their computational complexity.
 *
 * @category Database
 * @package  SQLQueryAnalyzerPackage
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License
 * @link     http://example.com/SQLQueryAnalyzer
 */
class SQLQueryAnalyzer
{
    /**
     * Estimates the complexity of an SQL query based on its execution plan.
     *
     * @param PDO   $pdo           The PDO connection object.
     * @param array $executionPlan The execution plan of the SQL query.
     *
     * @return string The estimated complexity of the query.
     */
    public function estimateComplexity($pdo, $executionPlan)
    {
        $complexity = [];
        $complexityDescription = [];
        $dominantComplexity = 'O(1)';

        foreach ($executionPlan as $step) {
            $type = $step['type'];

            switch ($type) {
                case 'ALL':
                    $complexity[] = 'O(n)';
                    $complexityDescription[] = 'O(n) Full Table Scan';
                    break;
                case 'index':
                    $complexity[] = 'O(log n)';
                    $complexityDescription[] = 'O(log n) Index Scan';
                    break;
                case 'range':
                    $complexity[] = 'O(log n)';
                    $complexityDescription[] = 'O(log n) Range Scan';
                    break;
                case 'ref':
                case 'eq_ref':
                    $complexity[] = 'O(1)';
                    $complexityDescription[] = 'O(1) Index Lookup';
                    break;
                case 'const':
                    $complexity[] = 'O(1)';
                    $complexityDescription[] = 'O(1) Constant Lookup';
                    break;
                case 'subquery':
                    $subqueryPlan
                        = $this->getSubqueryPlan($pdo, $step['query']);
                    $subqueryComplexity
                        = $this->estimateComplexity($pdo, $subqueryPlan);

                    $complexity[] = $subqueryComplexity;
                    $complexityDescription[] = 'Subquery: (' .
                        $subqueryComplexity . ')';
                    break;
                default:
                    $complexity[] = 'Unknown';
            }
        }

        // Determine the dominant complexity
        if (in_array('Unknown', $complexity)) {
            $dominantComplexity = 'Unknown';
        } elseif (in_array('O(n^2)', $complexity)) {
            $dominantComplexity = 'O(n^2)';
        } elseif (in_array('O(n log n)', $complexity)) {
            $dominantComplexity = 'O(n log n)';
        } elseif (in_array('O(n)', $complexity)) {
            $dominantComplexity = 'O(n)';
        } elseif (in_array('O(log n)', $complexity)) {
            $dominantComplexity = 'O(log n)';
        } else {
            $dominantComplexity = 'O(1)';
        }

        return
            "Estimated Complexity. \n" .
            "Description:\t" . implode(' + ', $complexityDescription) . ".\n" .
            "Complexity:\t" . implode(' + ', $complexity) . "\n" .
            "Dominant Complexity: " . $dominantComplexity . "\n";
    }

    /**
     * Retrieves the execution plan for a subquery.
     *
     * @param PDO    $pdo   The PDO connection object.
     * @param string $query The subquery for which to get the execution plan.
     *
     * @return array The execution plan of the subquery.
     */
    public function getSubqueryPlan($pdo, $query)
    {
        $stmt = $pdo->prepare('EXPLAIN ' . $query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
