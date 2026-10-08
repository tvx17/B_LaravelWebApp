<?php

namespace Tests\Feature;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SluggableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sluggable_fixtures', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function test_slugs_are_generated_and_duplicate_titles_receive_a_suffix(): void
    {
        $first = SluggableFixture::create(['title' => 'Hallo Laravel!']);
        $second = SluggableFixture::create(['title' => 'Hallo Laravel!']);

        $this->assertSame('hallo-laravel', $first->fresh()->slug);
        $this->assertSame('hallo-laravel-2', $second->fresh()->slug);
    }

    public function test_title_updates_preserve_existing_urls(): void
    {
        $model = SluggableFixture::create(['title' => 'Alter Titel']);
        $model->update(['title' => 'Neuer Titel']);

        $this->assertSame('alter-titel', $model->fresh()->slug);
    }
}

class SluggableFixture extends Model
{
    use Sluggable;

    protected $guarded = ['id'];

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'title']];
    }
}
