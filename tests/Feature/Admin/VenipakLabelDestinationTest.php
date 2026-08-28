<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\Api\VinepakApiController;
use App\Models\User;
use App\Models\VenipakData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VenipakLabelDestinationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->registerCreateLabelRouteForTest();
    }

    public function test_pickup_orders_force_pickup_payload_even_when_address_fields_are_present(): void
    {
        $admin = $this->createUser(1, 1, 'admin@example.test');
        $this->createVenipakConfig();
        $this->createOrder(17332, 1, [
            'sposob' => 'pickup_at_viar_workshop',
            'pickup_workshop_id' => 7,
            'country' => 'LV',
            'city' => 'Wrong city',
            'address' => 'Wrong street 1',
            'postal_index' => 'LV-0000',
            'email' => 'client@example.test',
            'first_name' => 'Sanda',
            'last_name' => 'Anševica',
            'phone' => '+37120000000',
        ]);

        $controller = new class extends VinepakApiController {
            public $capturedXml = null;

            protected function sendVenipakLabelRequest(string $xmlText): string
            {
                $this->capturedXml = $xmlText;

                return '<?xml version="1.0" encoding="UTF-8"?><response type="1"><text>V08354E7717334</text></response>';
            }
        };

        $this->app->instance(VinepakApiController::class, $controller);

        $response = $this->actingAs($admin)->post($this->createLabelEndpoint(), $this->pickupPayload([
            'destination' => 'pickup',
            'g_city_pickup' => 'Vilnius',
            'g_address_pickup' => 'Rygos g. 2B',
            'g_post_pickup' => 'LT-05259',
            'g_name_pickup' => 'Venipak Pickup, Saurida',
            'g_code_pickup' => '102147740',
            'g_city' => 'Wrong city',
            'g_address' => 'Wrong street',
            'g_post' => 'LV-0000',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success_vin');

        $this->assertNotNull($controller->capturedXml);
        $this->assertStringContainsString('<name>Venipak Pickup, Saurida</name>', $controller->capturedXml);
        $this->assertStringContainsString('<company_code>102147740</company_code>', $controller->capturedXml);
        $this->assertStringContainsString('<city>Vilnius</city>', $controller->capturedXml);
        $this->assertStringContainsString('<address>Rygos g. 2B</address>', $controller->capturedXml);
        $this->assertStringContainsString('<post_code>LT-05259</post_code>', $controller->capturedXml);
        $this->assertStringNotContainsString('<name>Urbelyte Rimante</name>', $controller->capturedXml);
        $this->assertStringNotContainsString('Wrong street', $controller->capturedXml);
    }

    public function test_pickup_orders_reject_missing_pickup_point_identity(): void
    {
        $admin = $this->createUser(2, 1, 'admin2@example.test');
        $this->createVenipakConfig();
        $this->createOrder(17333, 1, [
            'sposob' => 'pickup_at_viar_workshop',
            'pickup_workshop_id' => 7,
            'country' => 'LV',
            'city' => 'Wrong city',
            'address' => 'Wrong street 1',
            'postal_index' => 'LV-0000',
            'email' => 'client2@example.test',
            'first_name' => 'Sanda',
            'last_name' => 'Anševica',
            'phone' => '+37120000001',
        ]);

        $controller = new class extends VinepakApiController {
            public $capturedXml = null;

            protected function sendVenipakLabelRequest(string $xmlText): string
            {
                $this->capturedXml = $xmlText;

                return '<?xml version="1.0" encoding="UTF-8"?><response type="1"><text>V08354E7717335</text></response>';
            }
        };

        $this->app->instance(VinepakApiController::class, $controller);

        $response = $this->actingAs($admin)->post($this->createLabelEndpoint(), $this->pickupPayload([
            'order_id' => 17333,
            'destination' => 'pickup',
            'g_city_pickup' => 'Vilnius',
            'g_address_pickup' => 'Rygos g. 2B',
            'g_post_pickup' => 'LT-05259',
            'g_name_pickup' => '',
            'g_code_pickup' => '',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error_vin', 'Для pickup-этикетки выберите пункт выдачи из списка Venipak.');
        $this->assertNull($controller->capturedXml);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('venipak_data');

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->text('settings')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->text('items')->nullable();
            $table->text('delivery')->nullable();
            $table->string('labels')->nullable();
            $table->timestamps();
        });

        Schema::create('venipak_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user');
            $table->string('pass');
            $table->string('login_id');
            $table->string('import_url');
            $table->string('print_url');
            $table->string('s_name')->nullable();
            $table->string('s_code')->nullable();
            $table->string('s_country')->nullable();
            $table->string('s_city')->nullable();
            $table->string('s_address')->nullable();
            $table->string('s_post')->nullable();
            $table->string('s_contact_p')->nullable();
            $table->string('s_contact_t')->nullable();
            $table->string('email_sender')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'display_name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function registerCreateLabelRouteForTest(): void
    {
        Route::post('/test-create-label', [VinepakApiController::class, 'create_label']);
    }

    private function createLabelEndpoint(): string
    {
        return Route::has('create_label') ? route('create_label') : '/test-create-label';
    }

    private function createUser(int $id, int $roleId, string $email): User
    {
        DB::table('users')->insert([
            'id' => $id,
            'role_id' => $roleId,
            'email' => $email,
            'password' => Hash::make('secret'),
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'country' => 'LV',
            'phone' => '+37120000000',
            'avatar' => 'users/default.png',
            'settings' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($id);
    }

    private function createVenipakConfig(): void
    {
        VenipakData::query()->create([
            'user' => 'test-user',
            'pass' => 'test-pass',
            'login_id' => '08354',
            'import_url' => 'https://example.test/import/send.php',
            'print_url' => 'https://example.test/ws/print_label',
            's_name' => 'VIARSTUDIA',
            's_code' => '123456789',
            's_country' => 'LV',
            's_city' => 'Daugavpils',
            's_address' => 'Test street 1',
            's_post' => 'LV-5401',
            's_contact_p' => 'Test Person',
            's_contact_t' => '+37120000000',
            'email_sender' => 'sender@example.test',
            'is_active' => true,
        ]);
    }

    private function createOrder(int $id, int $userId, array $deliveryOverrides = []): void
    {
        $delivery = array_merge([
            'sposob' => 'pickup_at_viar_workshop',
            'pickup_workshop_id' => 7,
            'country' => 'LV',
            'city' => 'Vilnius',
            'address' => 'Rygos g. 2B',
            'postal_index' => 'LT-05259',
            'email' => 'client@example.test',
            'first_name' => 'Sanda',
            'last_name' => 'Anševica',
            'phone' => '+37120000000',
        ], $deliveryOverrides);

        DB::table('orders')->insert([
            'id' => $id,
            'user_id' => $userId,
            'items' => json_encode([
                [
                    'sizeId' => '40x60',
                    'name' => 'Canvas',
                    'price' => 45,
                ],
            ]),
            'delivery' => json_encode($delivery),
            'labels' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function pickupPayload(array $overrides = []): array
    {
        return array_merge([
            'order_id' => 17332,
            'doc_no' => '102147539',
            'man_no' => '1',
            'delivery_type' => 'pickup',
            'delivery_express' => '0',
            'cod' => '0',
            'cod_type' => '0',
            'comment_call' => '',
            'four_hands' => '0',
            'p_svoris' => ['1'],
            'p_turis' => ['1'],
            'p_pallet' => ['1'],
            'destination' => 'pickup',
            'g_name' => 'Urbelyte Rimante',
            'g_code' => '',
            'r_country' => 'LT',
            'g_city_pickup' => 'Vilnius',
            'g_address_pickup' => 'Rygos g. 2B',
            'g_post_pickup' => 'LT-05259',
            'g_name_pickup' => 'Venipak Pickup, Saurida',
            'g_code_pickup' => '102147740',
            'g_city' => 'Wrong city',
            'g_address' => 'Wrong street',
            'g_house' => '1',
            'g_flat' => '2',
            'g_post' => 'LV-0000',
            'g_contact_p' => '17332 (40x60)',
            'g_contact_t' => '+37060547456',
            'email_receiver' => 'recipient@example.test',
            's_name' => 'VIARSTUDIA, SIA',
            's_code' => '123456789',
            's_country' => 'LV',
            's_city' => 'Daugavpils',
            's_address' => 'Muitas iela 3',
            's_post' => 'LV-5401',
            's_contact_p' => 'Vladislavs',
            's_contact_t' => '+37127044470',
            'email_sender' => 'sender@example.test',
            'door_code' => '',
            'office_no' => '',
            'warehous_no' => '',
        ], $overrides);
    }
}
