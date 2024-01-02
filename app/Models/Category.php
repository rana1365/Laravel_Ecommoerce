<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'status','image',
    ];

    public static $rules = [
        'name' => 'required',
        'slug' => 'required|unique:categories',
    ];

    public function sub_category()
    {
        /*** Ex: a single SubCategory can belong to multiple category ***/
        return $this->hasMany(SubCategory::class);
    }
}
