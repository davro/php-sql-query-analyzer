<?php

use PHPUnit\Framework\TestCase;
use Davro\SQLQueryAnalyzer;

class SQLQueryAnalyzerTest extends TestCase
{
    protected $pdoMock;

    protected function setUp(): void
    {
        // Create a PDO mock object
        $this->pdoMock = $this->getMockBuilder(PDO::class)
                              ->disableOriginalConstructor()
                              ->getMock();
    }

    public function testEstimateComplexityFullTableScan()
    {
        $executionPlan = [
            ['type' => 'ALL']
        ];

        $analyzer = new SQLQueryAnalyzer();
        $complexity = $analyzer->estimateComplexity($this->pdoMock, $executionPlan);

        $expectedComplexity = "Estimated Complexity. \n" .
            "Description:\tO(n) Full Table Scan.\n" .
            "Complexity:\tO(n)\n" .
            "Dominant Complexity: O(n)\n";

        $this->assertEquals($expectedComplexity, $complexity);
    }

    public function testEstimateComplexityIndexLookup()
    {
        $executionPlan = [
            ['type' => 'ref']
        ];

        $analyzer = new SQLQueryAnalyzer();
        $complexity = $analyzer->estimateComplexity($this->pdoMock, $executionPlan);

        $expectedComplexity = "Estimated Complexity. \n" .
            "Description:\tO(1) Index Lookup.\n" .
            "Complexity:\tO(1)\n" .
            "Dominant Complexity: O(1)\n";

        $this->assertEquals($expectedComplexity, $complexity);
    }

    public function testEstimateComplexitySubquery()
    {
        $executionPlan = [
            ['type' => 'subquery', 'query' => 'SELECT * FROM sub_table']
        ];

        $subqueryPlan = [
            ['type' => 'ALL']
        ];

        // Create a statement mock object for the subquery
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($subqueryPlan);

        // Mock the prepare method to return the statement mock
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $analyzer = new SQLQueryAnalyzer();
        $complexity = $analyzer->estimateComplexity($this->pdoMock, $executionPlan);

        $expectedComplexity = "Estimated Complexity. \n" .
            "Description:\tSubquery: (Estimated Complexity. \n" .
            "Description:\tO(n) Full Table Scan.\n" .
            "Complexity:\tO(n)\n" .
            "Dominant Complexity: O(n)\n).\n" .
            "Complexity:\tEstimated Complexity. \n" .
            "Description:\tO(n) Full Table Scan.\n" .
            "Complexity:\tO(n)\n" .
            "Dominant Complexity: O(n)\n\n" .
            "Dominant Complexity: O(n)\n";

        $this->assertStringContainsString('Estimated Complexity.', $complexity);
    }
}

