<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('q') ?? $request->input('search') ?? $request->input('term');

        return Category::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'text' => $category->name,
                ];
            });
    }
}
