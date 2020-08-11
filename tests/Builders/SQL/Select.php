<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Select Statement Test Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Test\Builders\SQL;
use \phpOpenFW\Builders\SQL;

//**************************************************************************************
/**
 * Select Class
 */
//**************************************************************************************
class Select
{
    //=========================================================================
    //=========================================================================
    // Test
    //=========================================================================
    //=========================================================================
    public static function Test()
    {
        //---------------------------------------------------------------
        // DB Types
        //---------------------------------------------------------------
        $db_types = [
            'mysql',
            'pgsql',
            'oracle',
            'sqlsrv'
        ];

        //---------------------------------------------------------------
        // Build SQL Select Statements for each database type
        //---------------------------------------------------------------
        foreach ($db_types as $db_type) {

            //----------------------------------------------------------------
            // Test Header
            //----------------------------------------------------------------
            $disp_db_type = ucfirst($db_type);
            print "\n-------------------------------------------------------";
            print "\n*** {$disp_db_type} Select Statements";
            print "\n-------------------------------------------------------\n\n";

            //---------------------------------------------------------------
            // Test Values
            //---------------------------------------------------------------
            $test_value = 2;

            //---------------------------------------------------------------
            // Short Query #1
            //---------------------------------------------------------------
            $query1 = SQL::Select('contacts')
            ->SetDbType($db_type)
            ->Where('last_name', '=', '12', 'd');

            //---------------------------------------------------------------
            // Short Query #2
            //---------------------------------------------------------------
            $query2 = SQL::Select('contacts')
            ->SetDbType($db_type)
            ->Where('first_name', '=', '13', 'd');

            //---------------------------------------------------------------
            // Short Query #3
            //---------------------------------------------------------------
            $query3 = SQL::Select('contacts')
            ->SetDbType($db_type)
            ->Select('id')
            ->Where('id', '>=', '4', 'i');

            //---------------------------------------------------------------
            // Create / Start SQL Select Statement
            //---------------------------------------------------------------
            $query = SQL::Select('contacts a')
                ->SetDbType($db_type)
                ->Union($query1)
                ->Select('a.id, a.worker_id')
                ->Select('a.org_id, a.person_id')
                ->SelectRaw("concat(b.first_name, ' ', b.last_name as full_name")
                ->LeftJoin('join_table b', 'a.worker_id', '=', 'b.id')
                ->InnerJoin('join_table c', function ($join) {
                    $join->On('test_col1', 'test_col2')
                    ->Where('test1', '=', 0);
                })
                ->OuterJoin('join_table c', function ($join) use ($test_value) {
                    $join->On('test_col1', 'test_col2')
                    ->Where('test2', '=', 1)
                    ->Where('test3', '!=', $test_value);
                })
                //->From('test_table')
                //->From('test_table2 a, test_table3 z, ')
                //->CrossJoin('join_table b', 'a.worker_id', '=', 'b.id')
                //->From(['test1', 'test3'])
                ->GroupBy('worker_id')
                //->GroupBy('test1, test2')
                ->OrderBy(['child_id', 'id desc'])
                ->Where('test4', '>=', 3, 'i')
                /*
                ->Where([
                    ['test4_1', '>=', '4_1', 'd'],
                    ['test4_2', '>=', '4_2', 'd'],
                ])
                */
                ->WhereIn('test_id', $query3)
                ->Where(function ($query) use ($test_value) {
                    $query->WhereColumn('test5', 'test6')
                    ->OrWhereColumn('test7', 'test8')
                    ->OrWhere('test9', '=', 5)
                    ->Where('test10', '!=', 3 + 3) // 6
                    ->Where(function ($query) use ($test_value) {
                        $query->WhereNotBetween('test11', [7, 8], 'i')
                        ->WhereIn('test12', [9, 10, 11], 'i')
                        ->WhereNull('test13');
                    });
                })
                ->WhereRaw("raw_field = 'test'")
                ->Having('id', '>', 14)
                ->HavingRaw('test_id = 10')
                ->Having(function ($query) use ($test_value) {
                    $query->WhereNotBetween('test11', [15, 16], 'i')
                    ->WhereIn('test12', [17, 18, 19], 'i')
                    ->WhereNull('test13');
                })
                ->Limit(50, 2)
                
                ->UnionAll($query2);

            //---------------------------------------------------------------
            // Output Query / Bind Parameters
            //---------------------------------------------------------------
            print $query . "\n";
            print_r($query->GetBindParams());
        }
    }
    
}
