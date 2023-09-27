<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function pageContents()
    {
        return $this->hasMany(PageContent::class);
    }

    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $uniqueSlug = $slug;
        $counter = 2;

        // Check if the generated slug is unique
        while (Page::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $counter++;
        }

        return $uniqueSlug;
    }

    protected static function booted()
    {
        static::deleting(function (Page $page) {
            $page->pageContents()->delete();
        });
    }
}