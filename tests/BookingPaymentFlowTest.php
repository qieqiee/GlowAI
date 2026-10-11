<?php

use App\Models\User;
use App\Models\MakeupArtist;
use App\Models\Service;
use App\Models\Availability;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;

class BookingPaymentFlowTest extends \Tests\TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function migrateDatabases()
    {
        // The historical migrations add role twice and use full_name although
        // the running app uses name. Isolate these flow tests from that legacy drift.
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id(); $table->string('name'); $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); $table->string('role');
            $table->string('password'); $table->rememberToken(); $table->timestamps();
        });
        foreach ([
            '2026_08_26_091015_create_makeup_artists_table.php',
            '2026_08_28_175954_add_is_published_to_makeup_artists_table.php',
            '2026_08_30_083648_create_services_table.php',
            '2026_08_30_095448_create_availabilities_table.php',
            '2026_08_30_112937_create_blocked_dates_table.php',
            '2026_08_30_172833_create_availability_overrides_table.php',
            '2026_09_01_115109_create_bookings_table.php',
            '2026_09_07_193451_add_toyyibpay_fields_to_bookings_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
    $this->withoutVite();
    $this->customer = User::factory()->create(['role' => 'customer']);
    $artistUser = User::factory()->create(['role' => 'makeup_artist']);
    $artist = MakeupArtist::create([
        'user_id' => $artistUser->id, 'studio_brand_name' => 'Test Studio',
        'studio_address' => 'Test Address', 'years_experience' => 3,
        'specialized_makeup_look' => 'Bridal', 'is_published' => true,
    ]);
    $this->service = Service::create([
        'makeup_artist_id' => $artist->id, 'service_name' => 'Test Makeup',
        'category' => 'Bridal', 'price' => 200, 'deposit_amount' => 50, 'duration' => 60,
    ]);
    $date = now()->addWeek();
    Availability::create([
        'makeup_artist_id' => $artist->id, 'day_of_week' => $date->format('l'),
        'is_working' => true, 'start_time' => '09:00', 'end_time' => '17:00',
    ]);
    $this->details = [
        'service_id' => $this->service->id, 'booking_date' => $date->format('Y-m-d'),
        'booking_time' => '10:00', 'event_type' => 'Wedding',
        'appointment_location_type' => 'studio', 'customer_phone' => '0123456789',
    ];
    config(['services.toyyibpay.base_url' => 'https://dev.toyyibpay.com',
        'services.toyyibpay.secret_key' => 'test-secret', 'services.toyyibpay.category_code' => 'test-category']);
    }

    public function test_redirects_post_to_a_refreshable_get_summary_without_creating_a_booking(): void
    {
    $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details)
        ->assertStatus(303)->assertRedirect('/customer/booking/payment');
    $this->get('/customer/booking/payment')->assertOk()->assertSee('Test Makeup')->assertSee('RM50.00');
    $this->get('/customer/booking/payment')->assertOk();
    $this->assertDatabaseCount('bookings', 0);
    }

    public function test_handles_missing_and_cross_user_drafts_without_treating_payment_as_a_service_id(): void
    {
    $this->actingAs($this->customer)->get('/customer/booking/payment')->assertRedirect('/customer/search-mua');
    $this->withSession(['booking_payment_draft' => ['customer_id' => $this->customer->id + 1, 'details' => $this->details]])
        ->get('/customer/booking/payment')->assertRedirect('/customer/search-mua');
    }

    public function test_sends_invalid_booking_details_back_to_the_booking_form(): void
    {
    $this->actingAs($this->customer)->post('/customer/booking/payment', [...$this->details, 'booking_time' => 'invalid'])
        ->assertRedirect(route('customer.booking.create', ['service' => $this->service->id, 'date' => $this->details['booking_date']], false))
        ->assertSessionHasErrors('booking_time');
    }

    public function test_requires_login_for_the_summary(): void
    {
    $this->get('/customer/booking/payment')->assertRedirect(route('login'));
    }

    public function test_keeps_both_booking_forms_on_the_same_origin(): void
    {
    $this->actingAs($this->customer)->get('/customer/booking/'.$this->service->id.'?date='.$this->details['booking_date'])
        ->assertOk()->assertSee('action="/customer/booking/payment"', false);
    $this->post('/customer/booking/payment', $this->details);
    $this->get('/customer/booking/payment')->assertSee('action="/customer/booking/payment/process"', false);
    }

    public function test_creates_one_bill_and_redirects_to_the_payment_provider(): void
    {
    Http::fake(['*/createBill' => Http::response([['BillCode' => 'testbill']], 200)]);
    $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details);
    $this->post('/customer/booking/payment/process', $this->details)->assertRedirect('https://dev.toyyibpay.com/testbill');
    $this->assertDatabaseHas('bookings', ['customer_id' => $this->customer->id, 'payment_status' => 'unpaid', 'toyyibpay_bill_code' => 'testbill']);
    Http::assertSent(fn ($request) => $request['billExternalReferenceNo'] && str_contains($request['billCallbackUrl'], '/customer/payment/toyyibpay/callback'));
    }

    public function test_does_not_leave_a_blocking_booking_when_the_provider_connection_fails(): void
    {
    Http::fake(['*' => Http::failedConnection()]);
    $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details);
    $this->post('/customer/booking/payment/process', $this->details)->assertRedirect('/customer/booking/payment')->assertSessionHasErrors('payment');
    $this->get('/customer/booking/payment')->assertOk();
    $this->assertDatabaseCount('bookings', 0);
    }

    public function test_returns_slot_errors_to_a_working_summary(): void
    {
    $this->details['booking_time'] = '22:00';
    $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details);
    $this->from('/customer/booking/payment')->post('/customer/booking/payment/process', $this->details)
        ->assertRedirect('/customer/booking/payment')->assertSessionHasErrors('booking_time');
    $this->get('/customer/booking/payment')->assertOk();
    $this->assertDatabaseCount('bookings', 0);
    }

    public function test_accepts_verified_callbacks_without_login_and_rejects_forged_callbacks(): void
    {
    $booking = Booking::create([...$this->details, 'customer_id' => $this->customer->id,
        'makeup_artist_id' => $this->service->makeup_artist_id, 'status' => 'pending',
        'service_price' => 200, 'deposit_amount' => 50, 'balance_amount' => 150,
        'payment_status' => 'unpaid', 'toyyibpay_bill_code' => 'testbill']);
    $data = ['status' => '1', 'order_id' => (string) $booking->id, 'billcode' => 'testbill', 'refno' => 'testref'];
    $this->post('/customer/payment/toyyibpay/callback', [...$data, 'hash' => 'invalid'])->assertForbidden();
    $this->post('/customer/payment/toyyibpay/callback', [...$data,
        'hash' => md5('test-secret1'.$booking->id.'testrefok')])->assertOk();
    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'payment_status' => 'paid']);
    }

    public function test_forged_browser_success_does_not_mark_payment_paid(): void
    {
        Http::fake(['*/createBill' => Http::response([['BillCode' => 'testbill']], 200),
            '*/getBillTransactions' => Http::response([], 200)]);
        $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details);
        $this->post('/customer/booking/payment/process', $this->details);
        $booking = Booking::firstOrFail();
        $this->get('/customer/payment/toyyibpay/return?status_id=1&billcode=testbill&order_id='.$booking->id)
            ->assertRedirect(route('customer.bookings.show', $booking->id));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'payment_status' => 'unpaid']);
    }

    public function test_verified_provider_success_reaches_confirmation(): void
    {
        Http::fake(['*/createBill' => Http::response([['BillCode' => 'testbill']], 200),
            '*/getBillTransactions' => Http::response([['billpaymentStatus' => '1']], 200)]);
        $this->actingAs($this->customer)->post('/customer/booking/payment', $this->details);
        $this->post('/customer/booking/payment/process', $this->details);
        $booking = Booking::firstOrFail();
        $this->get('/customer/payment/toyyibpay/return?status_id=1&billcode=testbill&order_id='.$booking->id)
            ->assertRedirect(route('customer.booking.confirmation', $booking->id));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'payment_status' => 'paid']);
    }

    public function test_existing_paid_booking_is_not_downgraded_by_cancelled_return(): void
    {
        $booking = Booking::create([...$this->details, 'customer_id' => $this->customer->id,
            'makeup_artist_id' => $this->service->makeup_artist_id, 'status' => 'pending',
            'service_price' => 200, 'deposit_amount' => 50, 'balance_amount' => 150,
            'payment_status' => 'paid', 'toyyibpay_bill_code' => 'testbill']);
        $this->actingAs($this->customer)->get('/customer/payment/toyyibpay/return?status_id=3&billcode=testbill&order_id='.$booking->id)
            ->assertRedirect(route('customer.booking.confirmation', $booking->id));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'payment_status' => 'paid']);
    }

    public function test_invalid_bill_response_does_not_leave_a_booking(): void
    {
        Http::fake(['*' => Http::response('[CATEGORY-NOT-MATCH]', 200)]);
        $this->actingAs($this->customer)->post('/customer/booking/payment/process', $this->details)
            ->assertRedirect(route('customer.booking.create', $this->service->id))->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('bookings', 0);
    }
}
