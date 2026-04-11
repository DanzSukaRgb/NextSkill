<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_category_can_be_updated_via_json_patch(): void
    {
        $category = Category::create([
            'name' => 'Sebelum Update JSON',
            'description' => 'Deskripsi lama',
        ]);

        $response = $this->patchJson("/api/categories/{$category->id}", [
            'name' => 'Sesudah Update JSON',
            'description' => 'Deskripsi baru',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', 'Sesudah Update JSON')
            ->assertJsonPath('data.description', 'Deskripsi baru');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Sesudah Update JSON',
            'description' => 'Deskripsi baru',
        ]);
    }

    public function test_category_can_be_updated_via_raw_multipart_put_request(): void
    {
        $category = Category::create([
            'name' => 'Sebelum Multipart',
            'description' => 'Deskripsi multipart lama',
        ]);

        $boundary = '----NextSkillBoundary';
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"name\"\r\n\r\n"
            . "Sesudah Multipart\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"description\"\r\n\r\n"
            . "Deskripsi multipart baru\r\n"
            . "--{$boundary}--\r\n";

        $request = \Illuminate\Http\Request::create(
            "/api/categories/{$category->id}",
            'PUT',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => "multipart/form-data; boundary={$boundary}",
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body
        );

        $response = $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Sesudah Multipart',
            'description' => 'Deskripsi multipart baru',
        ]);
    }

    public function test_empty_update_request_returns_validation_error(): void
    {
        $category = Category::create([
            'name' => 'Tidak Berubah',
            'description' => 'Tetap sama',
        ]);

        $response = $this->patchJson("/api/categories/{$category->id}", []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['request']);
    }
}
