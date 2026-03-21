<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_approved(): void
    {
        $store = Store::factory()->approved()->create();

        $this->assertTrue($store->isApproved());
        $this->assertFalse($store->isBanned());
    }

    public function test_is_banned(): void
    {
        $store = Store::factory()->banned()->create();

        $this->assertTrue($store->isBanned());
        $this->assertFalse($store->isApproved());
    }

    public function test_add_strike_increments(): void
    {
        $store = Store::factory()->approved()->create(['strikes' => 0]);

        $store->addStrike();

        $this->assertEquals(1, $store->fresh()->strikes);
    }

    public function test_three_strikes_bans_store(): void
    {
        $store = Store::factory()->approved()->create(['strikes' => 2]);

        $store->addStrike();

        $store->refresh();
        $this->assertEquals('banned', $store->status);
        $this->assertNotNull($store->banned_at);
    }

    public function test_store_belongs_to_owner(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['owner_id' => $user->id]);

        $this->assertTrue($store->owner->is($user));
    }

    public function test_store_soft_deletes(): void
    {
        $store = Store::factory()->create();
        $store->delete();

        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }
}