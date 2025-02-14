<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }

    public function test_customer_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.index'));

        $response->assertOk();
    }

    public function test_customer_can_be_created(): void
    {
        $logo = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'company' => 'Test Company',
                'address' => '123 Test St',
                'logo' => $logo,
            ]);

        $response->assertRedirect(route('customers.index'));
        
        $customer = Customer::first();
        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
        
        $this->assertTrue($customer->getMedia('logo')->isNotEmpty());
    }

    public function test_customer_can_be_created_without_logo(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'company' => 'Test Company',
                'address' => '123 Test St',
            ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
    }

    public function test_customer_can_be_updated(): void
    {
        $customer = Customer::factory()->create();
        $newLogo = UploadedFile::fake()->image('new-logo.jpg');

        $response = $this->actingAs($this->user)
            ->put(route('customers.update', $customer), [
                'name' => 'Updated Customer',
                'email' => 'updated@example.com',
                'phone' => '0987654321',
                'company' => 'Updated Company',
                'address' => '321 Updated St',
                'logo' => $newLogo,
            ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
        ]);

        $customer->refresh();
        $this->assertTrue($customer->getMedia('logo')->isNotEmpty());
    }

    public function test_customer_can_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        $logo = UploadedFile::fake()->image('logo.jpg');
        $customer->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertEmpty($customer->getMedia('logo'));
    }

    public function test_customer_validation_rules(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'logo' => UploadedFile::fake()->create('document.pdf', 100),
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'logo']);
    }

    public function test_customer_show_page_displays_customer_details(): void
    {
        $customer = Customer::factory()->create();
        $logo = UploadedFile::fake()->image('logo.jpg');
        $customer->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', $customer));

        $response->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email);
    }

    public function test_customer_edit_page_displays_current_values(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('customers.edit', $customer));

        $response->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email);
    }
} 