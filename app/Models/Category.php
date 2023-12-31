<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'status',
    ];

    public static $rules = [
        'name' => 'required',
        'slug' => 'required|unique:categories',
    ];
}
