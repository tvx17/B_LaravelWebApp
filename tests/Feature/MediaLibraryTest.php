<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_be_stored_retrieved_and_deleted_with_its_model(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $model = MediaLibraryFixture::findOrFail($user->id);

        $media = $model->addMediaFromString('Media library integration test')
            ->usingFileName('document.txt')
            ->toMediaCollection('attachments');

        $this->assertSame('local', $media->disk);
        $this->assertSame($media->id, $model->fresh()->getFirstMedia('attachments')->id);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'collection_name' => 'attachments']);
        $relativePath = $media->getPathRelativeToRoot();
        Storage::disk('local')->assertExists($relativePath);
        $this->assertSame('Media library integration test', Storage::disk('local')->get($relativePath));

        $model->delete();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('local')->assertMissing($relativePath);
    }
}

class MediaLibraryFixture extends User implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'users';
}
