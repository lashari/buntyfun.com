<?php

namespace Tests\Feature;

use App\Models\NameEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_reach_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_editor_cannot_publish_but_admin_can(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@test.com', 'password' => 'password', 'role' => 'editor']);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'role' => 'admin']);
        $name = NameEntry::create([
            'name_en' => 'Test', 'gender' => 'unisex', 'meaning' => 'A test name',
            'source_ref' => 'A real verified reference', 'slug' => 'test', 'status' => 'in_review',
        ]);

        $this->actingAs($editor)->post(route('admin.name-entries.publish', $name))->assertStatus(403);
        $this->assertSame('in_review', $name->fresh()->status);

        $this->actingAs($admin)->post(route('admin.name-entries.publish', $name))->assertRedirect();
        $this->assertSame('published', $name->fresh()->status);
    }

    public function test_name_entry_with_placeholder_source_cannot_be_published(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin2@test.com', 'password' => 'password', 'role' => 'admin']);
        $name = NameEntry::create([
            'name_en' => 'Test', 'gender' => 'unisex', 'meaning' => 'A test name',
            'source_ref' => 'PLACEHOLDER — verify before publishing', 'slug' => 'test2', 'status' => 'in_review',
        ]);

        $this->actingAs($admin)->post(route('admin.name-entries.publish', $name))->assertStatus(422);
        $this->assertSame('in_review', $name->fresh()->status);
    }

    public function test_editor_cannot_delete_content_but_admin_can(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor2@test.com', 'password' => 'password', 'role' => 'editor']);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin3@test.com', 'password' => 'password', 'role' => 'admin']);
        $name = NameEntry::create([
            'name_en' => 'Test', 'gender' => 'unisex', 'meaning' => 'A test name',
            'source_ref' => 'A real verified reference', 'slug' => 'test3', 'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->delete(route('admin.name-entries.destroy', $name))
            ->assertStatus(403);
        $this->assertDatabaseHas('name_entries', ['id' => $name->id]);

        $this->actingAs($admin)
            ->delete(route('admin.name-entries.destroy', $name))
            ->assertRedirect();
        $this->assertDatabaseMissing('name_entries', ['id' => $name->id]);
    }
}
