<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_anonymouse_cannot_see_the_list_of_devices(): void
    {
        Device::factory()->for(Category::factory())->count(10)->create();

        $this->assertCount(10, Device::all());
        $this
            ->followingRedirects()
            ->get(route('devices.index'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_a_form_to_add_a_new_device(): void
    {
        $this
            ->followingRedirects()
            ->get(route('devices.create'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_add_a_new_device(): void
    {
        $device = Device::factory()->make()->toArray();

        $this
            ->followingRedirects()
            ->post(route('devices.store', $device))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_devices_details(): void
    {
        $device = Device::factory()->for(Category::factory())->create();

        $this->assertCount(1, Device::all());
        $this
            ->followingRedirects()
            ->get(route('devices.show', $device))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_update_a_device(): void
    {
        $device = Device::factory()->for(Category::factory())->create();

        $this->assertCount(1, Device::all());
        $this
            ->followingRedirects()
            ->patch(route('devices.update', $device))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_delete_a_device(): void
    {
        $device = Device::factory()->for(Category::factory())->create();

        $this->assertCount(1, Device::all());
        $this
            ->followingRedirects()
            ->delete(route('devices.destroy', $device))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function a_user_without_permissions_cannot_see_the_list_of_devices(): void
    {
        $user = User::factory()->create(['active' => true]);
        Device::factory()->for(Category::factory())->count(10)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(10, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('devices.index'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_without_permissions_cannot_see_a_form_to_add_a_new_device(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->get(route('devices.create'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_without_permissions_cannot_add_a_new_device(): void
    {
        // FIXME: It redirects to '/', there's no 403! WHY?! :-)

        $user = User::factory()->create(['active' => true]);
        $user->refresh();
        $category = Category::factory()->create();
        $device = Device::factory()->make(['type' => $category->type])->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->post(route('devices.store', $device))
            ->assertOk()
            ->assertDontSeeText(__('devices.added', ['name' => $device['mac'], 'model' => $device['type']]));
        $this->assertCount(0, Device::all());
    }

    /** @test */
    public function a_user_without_permissions_cannot_see_devices_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $device = Device::factory()->for(Category::factory())->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('devices.show', $device))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_without_permissions_cannot_update_a_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $device = Device::factory()->for(Category::factory())->create();
        $device_new = Device::factory()->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('devices.update', $device), $device_new)
            ->assertForbidden();
    }

    /** @test */
    public function a_user_without_permissions_cannot_delete_a_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $device = Device::factory()->for(Category::factory())->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->delete(route('devices.destroy', $device))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_with_permissions_can_see_the_list_of_devices(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();
        Device::factory()->times(10)->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(10, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('devices.index'))
            ->assertOk()
            ->assertSeeTextInOrder(Device::orderBy('mac')->select('mac')->get()->pluck('mac')->toArray());
    }

    /** @test */
    public function a_user_with_permissions_can_see_a_form_to_add_a_new_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('devices.create'))
            ->assertOk()
            ->assertSeeTextInOrder([
                __('common.category'),
                __('common.mac'),
                __('common.name'),
                __('common.description'),
                __('common.enabled'),
                __('common.valid_from'),
                __('common.valid_to'),
            ]);
    }

    /** @test */
    public function a_user_with_permissions_can_add_a_new_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();
        $device = Device::factory()->for($category)->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->post(route('devices.store', $device))
            ->assertOk()
            ->assertSeeText(__('devices.added', ['name' => $device['mac'], 'model' => $category->type]));
        $this->assertCount(1, Device::all());
    }

    /** @test */
    public function a_user_with_permissions_can_see_devices_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();
        $device = Device::factory()->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('devices.show', $device))
            ->assertOk()
            ->assertSeeTextInOrder([$device->mac, $device->name, $device->description ?? '--']);
    }

    /** @test */
    public function a_user_with_permissions_can_update_a_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();
        $device = Device::factory()->for($category)->create();
        $device_new = Device::factory()->for($category)->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('devices.update', $device), $device_new)
            ->assertOk()
            ->assertSeeText(__('devices.updated', ['name' => $device_new['mac'], 'model' => $category->type]));
    }

    /** @test */
    public function a_user_with_permissions_can_delete_a_device(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $user->categories()->attach($category);
        $user->refresh();
        $device = Device::factory()->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->delete(route('devices.destroy', $device))
            ->assertOk()
            ->assertSeeText(__('devices.deleted', ['name' => $device->mac, 'model' => $device->type]));
    }

    /** @test */
    public function an_admin_can_see_the_list_of_devices(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $devices = Device::factory()->times(10)->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(10, Device::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('devices.index'))
            ->assertOk()
            ->assertSeeTextInOrder(Device::orderBy('mac')->select('mac')->get()->pluck('mac')->toArray());
    }

    /** @test */
    public function an_admin_can_see_a_form_to_add_a_new_device(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('devices.create'))
            ->assertOk()
            ->assertSeeTextInOrder([
                __('common.category'),
                __('common.mac'),
                __('common.name'),
                __('common.description'),
                __('common.enabled'),
                __('common.valid_from'),
                __('common.valid_to'),
            ]);
    }

    /** @test */
    public function an_admin_can_add_a_new_device(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $device = Device::factory()->for($category)->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->post(route('devices.store', $device))
            ->assertOk()
            ->assertSeeText(__('devices.added', ['name' => $device['mac'], 'model' => $category->type]));
        $this->assertCount(1, Device::all());
    }

    /** @test */
    public function an_admin_can_see_devices_details(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $device = Device::factory()->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('devices.show', $device))
            ->assertOk()
            ->assertSeeTextInOrder([$device->mac, $device->name, $device->description ?? '--']);
    }

    /** @test */
    public function an_admin_can_update_a_device(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $device = Device::factory()->for($category)->create();
        $device_new = Device::factory()->for($category)->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('devices.update', $device), $device_new)
            ->assertOk()
            ->assertSeeText(__('devices.updated', ['name' => $device_new['mac'], 'model' => $category->type]));
    }

    /** @test */
    public function an_admin_can_delete_a_device(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $device = Device::factory()->for($category)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this->assertCount(1, Device::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->delete(route('devices.destroy', $device))
            ->assertOk()
            ->assertSeeText(__('devices.deleted', ['name' => $device->mac, 'model' => $device->type]));
    }

    /** @test */
    public function a_form_to_add_a_device_shows_all_texts(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('devices.create'))
            ->assertOk()
            ->assertSeeInOrder([
                __('devices.add'),
                __('devices.device_profile'),
                __('common.category'),
                __('devices.choose_category'),
                __('common.mac'),
                __('inputs.placeholder_mac'),
                __('common.name'),
                __('inputs.placeholder_name'),
                __('common.description'),
                __('inputs.placeholder_description'),
                __('common.status'),
                __('common.enabled'),
                __('common.disabled'),
                __('common.valid_from'),
                __('common.valid_to'),
                __('common.back'),
                __('common.add'),
            ]);
    }
}
