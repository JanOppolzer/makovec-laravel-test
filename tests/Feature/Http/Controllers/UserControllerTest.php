<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function an_anonymouse_cannot_see_the_list_of_users(): void
    {
        User::factory()->times(10)->create();

        $this->assertCount(10, User::all());
        $this
            ->followingRedirects()
            ->get(route('users.index'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_a_form_to_add_a_new_user(): void
    {
        $this
            ->followingRedirects()
            ->get(route('users.create'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_add_a_new_user(): void
    {
        $this
            ->followingRedirects()
            ->post(route('users.store'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_users_details(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, User::all());
        $this
            ->followingRedirects()
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_update_a_user(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, User::all());
        $this
            ->followingRedirects()
            ->patch(route('users.update', $user))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_toggle_users_status(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, User::all());
        $this
            ->followingRedirects()
            ->patch(route('users.status', $user))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_toggle_users_role(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, User::all());
        $this
            ->followingRedirects()
            ->patch(route('users.role', $user))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_set_users_categories(): void
    {
        $user = User::factory()->create();

        $this->assertCount(1, User::all());
        $this
            ->followingRedirects()
            ->patch(route('users.categories', $user))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function a_user_cannot_see_the_list_of_users(): void
    {
        $user = User::factory()->create(['active' => true]);
        $user->refresh();
        User::factory()->times(10)->create();

        $this->assertCount(11, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('users.index'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_see_a_form_to_add_a_new_user(): void
    {
        $user = User::factory()->create(['active' => true]);
        $user->refresh();

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('users.create'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_add_a_new_user(): void
    {
        $user = User::factory()->create(['active' => true]);
        $user->refresh();

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->post(route('users.store', [
                'name' => fake()->name(),
                'uniqueid' => fake()->unique()->safeEmail(),
                'email' => 'dummy@cesnet.cz',
            ]))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_see_users_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $another_user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('users.show', $another_user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_can____see_their_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $user->refresh();

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->uniqueid)
            ->assertSee($user->email);
    }

    /** @test */
    public function a_user_cannot_update_users_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $another_user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.update', $another_user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_can_update_own_details(): void
    {
        $old_email = fake()->safeEmail();
        $new_email = fake()->safeEmail();
        $user = User::factory()->create([
            'active' => true,
            'email' => $old_email,
            'emails' => "{$old_email};{$new_email}",
        ]);
        $user->refresh();

        $this->assertCount(1, User::all());
        $this->assertEquals($old_email, $user->email);
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.update', $user), [
                'email' => $new_email,
            ])
            ->assertOk()
            ->assertSeeText(__('users.email_changed'));
        $user->refresh();
        $this->assertEquals($new_email, $user->email);
    }

    /** @test */
    public function a_user_cannot_toggle_users_status(): void
    {
        $user = User::factory()->create(['active' => true]);
        $another_user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.status', $another_user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_toggle_own_status(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.status', $user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_toggle_users_role(): void
    {
        $user = User::factory()->create(['active' => true]);
        $another_user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.role', $another_user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_toggle_own_role(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.role', $user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_set_users_categories(): void
    {
        $user = User::factory()->create(['active' => true]);
        $another_user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.categories', $another_user))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_set_own_categories(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('users.categories', $user))
            ->assertForbidden();
    }

    /** @test */
    public function an_admin_can_see_the_list_of_users(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        User::factory()->times(15)->create();

        $this->assertCount(16, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('users.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Showing', '1', 'to', '15', 'of', '16']);
    }

    /** @test */
    public function an_admin_can_see_a_form_to_add_a_new_user(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('users.create'))
            ->assertOk()
            ->assertSeeText(__('common.add'));
    }

    /** @test */
    public function an_admin_can_add_a_new_user(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->post(route('users.store', [
                'name' => $userName = fake()->name(),
                'uniqueid' => $userUniqueId = fake()->unique()->safeEmail(),
                'email' => $userEmail = 'dummy@cesnet.cz',
            ]))
            ->assertOk()
            ->assertSeeText(__('users.added', ['name' => $userName]));
        $this->assertCount(2, User::all());
        $user = User::orderBy('id', 'desc')->first();
        $this->assertEquals($userName, $user->name);
        $this->assertEquals($userUniqueId, $user->uniqueid);
        $this->assertEquals($userEmail, $user->email);
    }

    /** @test */
    public function an_admin_can_see_users_details(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSeeText($user->name)
            ->assertSeeText($user->uniqueid)
            ->assertSeeText($user->email);
    }

    /** @test */
    public function an_admin_can_see_their_details(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('users.show', $admin))
            ->assertOk()
            ->assertSeeText($admin->name)
            ->assertSeeText($admin->uniqueid)
            ->assertSeeText($admin->email);
    }

    /** @test */
    public function an_admin_can_update_a_users_details(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $old_email = fake()->safeEmail();
        $new_email = fake()->safeEmail();
        $user = User::factory()->create([
            'active' => true,
            'email' => $old_email,
            'emails' => "{$old_email};{$new_email}",
        ]);

        $this->assertCount(2, User::all());
        $this->assertEquals($old_email, $user->email);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.update', $user), [
                'email' => $new_email,
            ])
            ->assertOk()
            ->assertSeeText(__('users.email_changed'));
        $user->refresh();
        $this->assertEquals($new_email, $user->email);
    }

    /** @test */
    public function an_admin_can_update_own_details(): void
    {
        $old_email = fake()->safeEmail();
        $new_email = fake()->safeEmail();
        $admin = User::factory()->create([
            'active' => true,
            'admin' => true,
            'email' => $old_email,
            'emails' => "{$old_email};{$new_email}",
        ]);

        $this->assertCount(1, User::all());
        $this->assertEquals($old_email, $admin->email);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.update', $admin), [
                'email' => $new_email,
            ])
            ->assertOk()
            ->assertSeeText(__('users.email_changed'));
        $admin->refresh();
        $this->assertEquals($new_email, $admin->email);
    }

    /** @test */
    public function an_admin_can_toggle_users_status(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $user = User::factory()->create(['active' => true]);

        $this->assertCount(2, User::all());
        $this->assertTrue($user->active);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.status', $user))
            ->assertOk()
            ->assertSeeText(__('users.inactive', ['name' => $user->name]));
        $user->refresh();
        $this->assertFalse($user->active);
    }

    /** @test */
    public function an_admin_cannot_toggle_own_status(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this->assertTrue($admin->active);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.status', $admin))
            ->assertOk()
            ->assertSeeText(__('users.cannot_toggle_your_status'));
        $admin->refresh();
        $this->assertTrue($admin->active);
    }

    /** @test */
    public function an_admin_can_toggle_users_role(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $user = User::factory()->create(['active' => true]);
        $user->refresh();

        $this->assertCount(2, User::all());
        $this->assertFalse($user->admin);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.role', $user))
            ->assertOk()
            ->assertSeeText(__('users.admined', ['name' => $user->name]));
        $user->refresh();
        $this->assertTrue($user->admin);
    }

    /** @test */
    public function an_admin_cannot_toggle_own_role(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $admin->refresh();

        $this->assertCount(1, User::all());
        $this->assertTrue($admin->admin);
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.role', $admin))
            ->assertOk()
            ->assertSeeText(__('users.cannot_toggle_your_role'));
        $this->assertTrue($admin->admin);
    }

    /** @test */
    public function an_admin_can_set_users_categories(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $user = User::factory()->create(['active' => true]);
        $user->refresh();

        $this->assertCount(2, User::all());
        $this->assertCount(0, $user->categories()->get());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.categories', $user))
            ->assertOk()
            ->assertSeeText(__('users.roles_updated'));
    }

    /** @test */
    public function an_admin_cannot_set_own_categories(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('users.categories', $admin))
            ->assertOk()
            ->assertSeeText(__('users.cannot_tweak_your_roles'));
    }
}
