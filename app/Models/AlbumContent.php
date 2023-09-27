<?php

namespace App\Models;

use App\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlbumContent extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
    
}