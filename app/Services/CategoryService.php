<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    /**
     * Create a new category with auto-generated slug and transaction.
     *
     * @param array $data
     * @return Category
     * @throws \Exception
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            try {
                if (empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                $category = Category::create($data);

                Log::info("Category created: ID {$category->id} by User " . Auth::id());

                return $category;
            } catch (\Exception $e) {
                Log::error("Failed to create category: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Update an existing category.
     *
     * @param Category $category
     * @param array $data
     * @return Category
     * @throws \Exception
     */
    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            try {
                if (isset($data['name']) && empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                $category->update($data);

                Log::info("Category updated: ID {$category->id} by User " . Auth::id());

                return $category;
            } catch (\Exception $e) {
                Log::error("Failed to update category ID {$category->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Delete a category safely.
     *
     * @param Category $category
     * @return bool
     * @throws \Exception
     */
    public function deleteCategory(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            try {
                if ($category->products()->exists()) {
                    throw new \Exception("Cannot delete category '{$category->name}' because it has related products.");
                }

                $category->delete();

                Log::info("Category deleted: ID {$category->id} by User " . Auth::id());

                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete category ID {$category->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }
}
