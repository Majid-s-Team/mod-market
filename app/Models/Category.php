<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
