<?php

namespace MarkSitko\LaravelUnsplash\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use MarkSitko\LaravelUnsplash\Unsplash;

class UnsplashAsset extends Model
{
    protected $fillable = [
        'unsplash_id',
        'name',
        'author',
        'author_link',
    ];

    /**
     * Creates a new instance of the unsplash api client.
     * @return MarkSitko\LaravelUnsplash\Unsplash
     */
    public static function api()
    {
        return new Unsplash();
    }

    /**
     * The booting method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // cleanup while deleting an asset
        static::deleting(function (UnsplashAsset $unsplashAsset) {
            if (Storage::disk(config('unsplash.disk', 'local'))->exists("{$unsplashAsset->name}")) {
                Storage::disk(config('unsplash.disk', 'local'))->delete("{$unsplashAsset->name}");
            }

            DB::delete('delete from unsplashables where unsplash_asset_id = ?', [$unsplashAsset->id]);
        });
    }

    /**
     * Get all unsplashables by given model.
     * @param $model Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function assets(Model $model)
    {
        return $this->morphedByMany($model, 'unsplashables');
    }

    /**
     * Returns a complete copyright link for a given data set.
     * @return string
     */
    public function getFullCopyrightLink()
    {
        return "Photo by <a href='{$this->author_link}?utm_source=" . config('unsplash.app_name', 'your_app_name') . "&utm_medium=referral' target='_blank'>{$this->author}</a> on <a href='https://unsplash.com/?utm_source=" . config('unsplash.app_name', 'your_app_name') . "&utm_medium=referral' target='_blank'>Unsplash</a>";
    }
}
