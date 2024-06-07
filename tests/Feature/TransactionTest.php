<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        DB::delete('DELETE FROM `categories`');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'GADGET', 'Laptop', 'Description laptop', '2020-10-10 10:10:10'
            ]);

            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'FOOD', 'Ramen', 'Description ramen', '2020-10-10 10:20:20'
            ]);
        });

        $results = DB::select('SELECT * FROM categories');
        self::assertCount(2, $results);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                    'GADGET', 'Laptop', 'Description laptop', '2020-10-10 10:10:10'
                ]);
    
                DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                    'GADGET', 'Ramen', 'Description ramen', '2020-10-10 10:20:20'
                ]);
            });
        } catch (QueryException $error) {
            // Expected
        }
        
        $results = DB::select('SELECT * FROM categories');
        self::assertCount(0, $results);
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'GADGET', 'Laptop', 'Description laptop', '2020-10-10 10:10:10'
            ]);
    
            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'FOOD', 'Ramen', 'Description ramen', '2020-10-10 10:20:20'
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }
        
        $results = DB::select('SELECT * FROM categories');
        self::assertCount(2, $results);
    }

    public function testManualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'GADGET', 'Laptop', 'Description laptop', '2020-10-10 10:10:10'
            ]);
    
            DB::insert('INSERT INTO `categories` (`id`, `name`, `desc`, `created_at`) VALUES (?,?,?,?)', [
                'GADGET', 'Ramen', 'Description ramen', '2020-10-10 10:20:20'
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }
        
        $results = DB::select('SELECT * FROM categories');
        self::assertCount(0, $results);
    }
}
