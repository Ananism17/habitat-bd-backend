<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\AlbumContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // protected $fillable = ['type', 'name', 'title', 'description', 'cover_photo'];

    public function albumContents()
    {
        return $this->hasMany(AlbumContent::class);
    }

    public function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $uniqueSlug = $slug;
        $counter = 2;

        // Check if the generated slug is unique
        while (Album::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $counter++;
        }

        return $uniqueSlug;
    }

    protected static function booted()
    {
        static::deleting(function (Album $album) {
            $album->albumContents()->delete();
        });
    }
}