<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_id', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
