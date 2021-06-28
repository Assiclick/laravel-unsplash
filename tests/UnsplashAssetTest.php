<?php

namespace MarkSitko\LaravelUnsplash\Tests;

use PDOException;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use MarkSitko\LaravelUnsplash\Unsplash;
use MarkSitko\LaravelUnsplash\Models\UnsplashAsset;
use MarkSitko\LaravelUnsplash\UnsplashServiceProvider;

class UnsplashAssetTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('unsplash.access_key', 'ABCD1234');

        $this->setupDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [UnsplashServiceProvider::class];
    }

    /** @test */
    public function it_has_these_massasignable_values()
    {
        $this->assertCount(0, UnsplashAsset::all());

        UnsplashAsset::create([
            'unsplash_id' => 'abc-def-g12',
            'name' => 'some-image.jpg',
            'author' => 'John Doe',
            'author_link' => 'https://unsplash.com/@john_doe',
        ]);

        $this->assertCount(1, UnsplashAsset::all());
    }

    /** @test */
    public function it_throws_an_pdo_exception_when_a_name_is_used_multiple_times()
    {
        $data = [
            'unsplash_id' => 'abc-def-g12',
            'name' => 'some-image.jpg',
            'author' => 'John Doe',
            'author_link' => 'https://unsplash.com/@john_doe',
        ];

        UnsplashAsset::create($data);

        $this->expectException(PDOException::class);

        UnsplashAsset::create($data);
    }

    /** @test */
    public function it_can_return_the_api_client()
    {
        $this->assertInstanceOf(Unsplash::class, UnsplashAsset::api());
    }

    /** @test */
    public function it_can_return_the_full_copyright_link()
    {
        $asset = UnsplashAsset::create([
            'unsplash_id' => 'abc-def-g12',
            'name' => 'some-image.jpg',
            'author' => 'John Doe',
            'author_link' => 'https://unsplash.com/@john_doe',
        ]);

        $appName = config('unsplash.app_name', 'your_app_name');

        $this->assertEquals(
            $asset->getFullCopyrightLink(),
            "Photo by <a href='{$asset->author_link}?utm_source={$appName}&utm_medium=referral' target='_blank'>{$asset->author}</a> on <a href='https://unsplash.com/?utm_source={$appName}&utm_medium=referral' target='_blank'>Unsplash</a>"
        );
    }

    protected function setupDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_unsplash_assets_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_unsplashables_table.php.stub';

        (new \CreateUnsplashAssetsTable())->up();
        (new \CreateUnsplashablesTable())->up();

        return $this;
    }
}
