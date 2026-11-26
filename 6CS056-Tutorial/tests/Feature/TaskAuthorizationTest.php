<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_manage_all_tasks(): void
    {
        $admin = $this->createAdmin();
        $owner = User::factory()->create();
        $task = $owner->tasks()->create([
            'title' => 'Another users task',
            'status' => 'pending',
        ]);
        $adminTask = $admin->tasks()->create([
            'title' => 'Admins task',
            'status' => 'in_progress',
        ]);

        $this->actingAs($admin)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Another users task')
            ->assertSee('Admins task');

        $this->actingAs($admin)
            ->get(route('tasks.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('tasks.store'), [
                'title' => 'New task',
                'status' => 'pending',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->actingAs($admin)
            ->put(route('tasks.update', $task), [
                'title' => 'Updated task',
                'status' => 'completed',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated task',
        ]);

        $this->actingAs($admin)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('tasks', ['id' => $adminTask->id]);
    }

    public function test_users_only_see_and_manage_their_own_tasks(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $ownTask = $user->tasks()->create([
            'title' => 'My task',
            'status' => 'pending',
        ]);
        $otherTask = $anotherUser->tasks()->create([
            'title' => 'Other users task',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('My task')
            ->assertDontSee('Other users task');

        $this->actingAs($user)
            ->get(route('tasks.show', $otherTask))
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('error', 'You are not allowed to manage this task.');

        $this->actingAs($user)
            ->put(route('tasks.update', $otherTask), [
                'title' => 'Changed task',
                'status' => 'completed',
            ])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('error', 'You are not allowed to manage this task.');

        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'title' => 'Other users task',
        ]);

        $this->actingAs($user)
            ->put(route('tasks.update', $ownTask), [
                'title' => 'Updated own task',
                'status' => 'completed',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $ownTask->id,
            'title' => 'Updated own task',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'title' => 'Created by user',
                'status' => 'pending',
            ])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Created by user',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('tasks.destroy', $ownTask))
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $ownTask->id]);
    }

    public function test_login_remember_me_sets_a_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $this->get(route('login'))->assertOk();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('tasks.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->getRememberToken());
    }

    public function test_registration_creates_a_non_admin_user(): void
    {
        $this->get(route('register'))->assertOk();

        $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'admin',
        ])->assertRedirect(route('tasks.index'));

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertSame('user', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_api_limits_users_to_their_own_tasks_and_admins_can_see_all(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownTask = $user->tasks()->create([
            'title' => 'My API task',
            'status' => 'pending',
        ]);
        $otherTask = $otherUser->tasks()->create([
            'title' => 'Other API task',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonFragment(['title' => 'My API task'])
            ->assertJsonMissing(['title' => 'Other API task']);

        $this->getJson('/api/tasks/'.$otherTask->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not allowed to manage this task.');

        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonFragment(['title' => 'My API task'])
            ->assertJsonFragment(['title' => 'Other API task']);

        $this->putJson('/api/tasks/'.$otherTask->id, [
            'title' => 'Admin updated task',
            'status' => 'completed',
        ])->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'title' => 'Admin updated task',
        ]);
        $this->assertNotNull($ownTask->user_id);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }
}
