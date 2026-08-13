<?php

namespace Tests\Feature;

use App\Repositories\BasketRepository;
use Tests\TestCase;

class PublicBasketSessionContractTest extends TestCase
{
    public function test_remove_requires_a_valid_basket_index_without_mutating_session(): void
    {
        $basket = [['name' => 'Canvas', 'price' => 20, 'count' => 1]];

        $this->withSession(['basket' => $basket])
            ->post('/basket/remove')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('basketId')
            ->assertSessionHas('basket', $basket);
    }

    public function test_count_update_rejects_zero_negative_and_excessive_values(): void
    {
        foreach ([0, -1, 100] as $count) {
            $this->withSession([
                'basket' => [['name' => 'Canvas', 'price' => 20, 'count' => 1]],
            ])->post('/basket/update/count', [
                'index' => 0,
                'count' => $count,
            ])->assertUnprocessable()
                ->assertJsonValidationErrors('count');
        }
    }

    public function test_count_update_persists_the_valid_session_state(): void
    {
        $basket = [['name' => 'Canvas', 'price' => 20, 'count' => 1]];
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn($basket);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with([['name' => 'Canvas', 'price' => 20, 'count' => 3, 'total_item_price' => 60]]);

        $this->withSession(['basket' => $basket])
            ->post('/basket/update/count', ['index' => 0, 'count' => 3])
            ->assertOk()
            ->assertContent('{"success":1,"price":"60\u20ac"}')
            ->assertSessionHas('basket.0.count', 3)
            ->assertSessionHas('basket.0.total_item_price', 60);
    }

    public function test_remove_persists_the_remaining_session_state(): void
    {
        $basket = [
            ['name' => 'Canvas', 'price' => 20, 'count' => 1],
            ['name' => 'Portrait', 'price' => 40, 'count' => 1],
        ];
        $remaining = [1 => $basket[1]];
        $repository = $this->mock(BasketRepository::class);
        $repository->shouldReceive('normalizeBasket')->once()->andReturn($basket);
        $repository->shouldReceive('saveBasketToAbandonedCartModel')
            ->once()
            ->with($remaining);

        $this->withSession(['basket' => $basket])
            ->post('/basket/remove', ['basketId' => 0])
            ->assertOk()
            ->assertContent('{"success":1}')
            ->assertSessionHas('basket', $remaining);
    }
}
