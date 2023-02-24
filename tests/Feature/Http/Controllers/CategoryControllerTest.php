<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_anonymouse_cannot_see_the_list_of_categories(): void
    {
        Category::factory()->times(5)->create();

        $this->assertCount(5, Category::all());
        $this
            ->followingRedirects()
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_a_form_to_add_a_new_category(): void
    {
        $this
            ->followingRedirects()
            ->get(route('categories.create'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_add_a_new_category(): void
    {
        $this
            ->followingRedirects()
            ->post(route('categories.store'))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_see_categories_details(): void
    {
        $category = Category::factory()->create();

        $this
            ->followingRedirects()
            ->get(route('categories.show', $category))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_update_a_category(): void
    {
        $category = Category::factory()->create();

        $this
            ->followingRedirects()
            ->patch(route('categories.update', $category))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function an_anonymouse_cannot_delete_a_category(): void
    {
        $category = Category::factory()->create();

        $this
            ->followingRedirects()
            ->delete(route('categories.destroy', $category))
            ->assertOk()
            ->assertSeeText('login');
    }

    /** @test */
    public function a_user_cannot_see_the_list_of_categories(): void
    {
        $user = User::factory()->create(['active' => true]);
        Category::factory()->times(5)->create();

        $this->assertCount(5, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('categories.index'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_see_a_form_to_add_a_new_category(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('categories.create'))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_add_a_new_category(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->make()->toArray();

        $this
            ->actingAs($user)
            ->followingRedirects()
            ->post(route('categories.store', $category))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_see_categories_details(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();

        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->get(route('categories.show', $category))
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_update_a_category(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();
        $category_new = Category::factory()->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->patch(route('categories.update', $category), $category_new)
            ->assertForbidden();
    }

    /** @test */
    public function a_user_cannot_delete_a_category(): void
    {
        $user = User::factory()->create(['active' => true]);
        $category = Category::factory()->create();

        $this->assertCount(1, Category::all());
        $this
            ->actingAs($user)
            ->followingRedirects()
            ->delete(route('categories.destroy', $category))
            ->assertForbidden();
    }

    /** @test */
    public function an_admin_can_see_the_list_of_categories(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        Category::factory()->times(5)->create();

        $this->assertCount(1, User::all());
        $this->assertCount(5, Category::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSeeTextInOrder(Category::pluck('type')->toArray());
    }

    /** @test */
    public function an_admin_can_see_a_form_to_add_a_new_category(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('categories.create'))
            ->assertOk();
    }

    /** @test */
    public function an_admin_can_add_a_new_category(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->make()->toArray();

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->post(route('categories.store', $category))
            ->assertOk()
            ->assertSeeText(__('categories.added', ['type' => $category['type']]));
    }

    /** @test */
    public function an_admin_can_see_categories_details(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('categories.show', $category))
            ->assertOk()
            ->assertSeeInOrder(Category::select('type', 'description', 'vlan')->first()->toArray());
    }

    /** @test */
    public function an_admin_can_update_a_category(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();
        $category_new = Category::factory()->make()->toArray();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->patch(route('categories.update', $category), $category_new)
            ->assertOk()
            ->assertSeeText(__('categories.updated', ['type' => $category_new['type']]));
    }

    /** @test */
    public function an_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);
        $category = Category::factory()->create();

        $this->assertCount(1, User::all());
        $this->assertCount(1, Category::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->delete(route('categories.destroy', $category))
            ->assertOk()
            ->assertSeeText(__('categories.deleted', ['type' => $category->type]));
        $this->assertCount(0, Category::all());
    }

    /** @test */
    public function a_form_to_add_a_category_shows_all_texts(): void
    {
        $admin = User::factory()->create(['active' => true, 'admin' => true]);

        $this->assertCount(1, User::all());
        $this
            ->actingAs($admin)
            ->followingRedirects()
            ->get(route('categories.create'))
            ->assertOk()
            ->assertSeeInOrder([
                __('categories.add'),
                __('categories.profile'),
                __('common.type'),
                __('inputs.placeholder_type'),
                __('common.description'),
                __('inputs.placeholder_description'),
                __('common.vlan'),
                __('inputs.placeholder_vlan'),
                __('categories.vlan_regexp'),
                __('common.back'),
                __('common.add'),
            ]);
    }
}
