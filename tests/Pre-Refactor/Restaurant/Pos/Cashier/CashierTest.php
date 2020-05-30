<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class CashierTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    /**
     * @group Cashier
    */
    public function testCashier()
    {
        /*
            Rotina do caixa:
            abre caixa > verifica se está aberto > cria movimentação > pega movimentações > pega estatísticas > deleta movimentação > fecha caixa > verifica se está fechado
        */

        /*
        |--------------------------------------------------------------------------
        | Abre o Caixa
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('PUT', 'restaurant/1/pos/cashier/open-cashier', [
                'value' => 300.00
            ]);
            
            $response->seeStatusCode(200);

            $cashier = \Entities\Cashier::latest()->first();
            // dd($cashier);
            $this->assertEquals($cashier->value, 300.00);
            $this->assertEquals($cashier->open, true);
            $this->assertEquals($cashier->restaurant_id, 1);
        /*
        |--------------------------------------------------------------------------
        | Checa se o caixa está realmente aberto
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('GET', 'restaurant/1/pos/cashier');
            
            $response->seeStatusCode(200);
            
            $cashierStatus = collect(json_decode($response->response->getContent()));
            $this->assertEquals($cashierStatus['open'], true);
        /*
        |--------------------------------------------------------------------------
        | Cria Movimentação
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('PUT', 'restaurant/1/pos/cashier/transaction', [
                "type" => "WITHDRAW",
                "value" => 50.00,
                "description" => "hp"
            ]);
            
            $response->seeStatusCode(200);

            $transaction = \Entities\Transaction::latest()->first();
            $this->assertEquals($transaction->type, 'WITHDRAW');
            $this->assertEquals($transaction->value, 50.00);
            $this->assertEquals($transaction->description, 'hp');
            $this->assertEquals($transaction->restaurant_id, 1);
            $this->assertEquals($transaction->cashier_id, $cashier->id);
        /*
        |--------------------------------------------------------------------------
        | Pega movimentações
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('GET', 'restaurant/1/pos/cashier/transaction');
            
            $response->seeStatusCode(200);

            $transactions = collect(json_decode($response->response->getContent()));
            $this->assertEquals($transactions[0]->type, 'WITHDRAW');
            $this->assertEquals($transactions[0]->value, 50.00);
            $this->assertEquals($transactions[0]->description, 'hp');
            $this->assertEquals($transactions[0]->restaurant_id, 1);
            $this->assertEquals($transactions[0]->cashier_id, $cashier->id);
        /*
        |--------------------------------------------------------------------------
        | Pega estatísticas do caixa
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('GET', 'restaurant/1/pos/cashier/current-statistics');
            
            $response->seeStatusCode(200);

            $cashierStats = collect(json_decode($response->response->getContent()));
            $this->assertEquals($cashierStats['transactions']->totalWithdraws, 50.00);
            $this->assertEquals($cashierStats['total'], 250.00); // 300 - 50
        /*
        |--------------------------------------------------------------------------
        | Deleta Movimentação
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('DELETE', 'restaurant/1/pos/cashier/transaction/'.$transaction->id);

            $response->seeStatusCode(200);

            $lastTransaction = \Entities\Transaction::orderBy('id', 'desc')->first();
            $this->assertEquals($lastTransaction, NULL);
        /*
        |--------------------------------------------------------------------------
        | Fecha o Caixa
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('GET', 'restaurant/1/pos/cashier/close-cashier');
            
            $response->seeStatusCode(200);

            $cashier = \Entities\Cashier::latest()->first();
            // dd($cashier);
            $this->assertEquals($cashier->open, false);
            $this->assertEquals($cashier->restaurant_id, 1);
        /*
        |--------------------------------------------------------------------------
        | Checa se o caixa está realmente fechado
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('GET', 'restaurant/1/pos/cashier');
            
            $response->seeStatusCode(200);
            
            $cashierStatus = collect(json_decode($response->response->getContent()));
            $this->assertEquals($cashierStatus['open'], false);
    }
}
