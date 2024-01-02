<?php

//echo "<h3> I Love Laravel and Php..</h3>";

use App\Models\Category;

function getCategories()
{
    return Category::orderBy('name', 'ASC')
        ->with('sub_category')
        ->where('status', 1)
        ->where('show_home', 'Yes')
        ->get();
}
