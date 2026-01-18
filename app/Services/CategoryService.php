<?php

namespace App\Services;

use Exception;
use App\Models\Category;
use App\DTOs\CategoryData;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CategoryException;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Create a new category.
     */
    public function createCategory(CategoryData $data): Category
    {
        return DB::transaction(function () use ($data) {
            try {
                $slug = Str::slug($data->name);

                $category = Category::create([
                    'name' => $data->name,
                    'slug' => $slug,
                    'description' => $data->description,
                ]);

                return $category;
            } catch (Exception $e) {
                throw CategoryException::createFailed($e->getMessage(), $e);
            }
        });
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category, CategoryData $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            try {
                $updateData = [
                    'name' => $data->name,
                    'description' => $data->description,
                ];

                if ($data->name !== $category->name) {
                    $updateData['slug'] = Str::slug($data->name);
                }

                $category->update($updateData);
                $category->refresh();

                return $category;
            } catch (Exception $e) {
                throw CategoryException::updateFailed((string) $category->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            try {
                if ($category->products()->exists()) {
                    throw CategoryException::inUse("Cannot delete category '{$category->name}' because it has associated products.");
                }

                $category->delete();

                return true;
            } catch (CategoryException $e) {
                throw $e;
            } catch (Exception $e) {
                throw CategoryException::deletionFailed((string) $category->id, $e->getMessage(), $e);
            }
        });
    }

    /**
     * Get all categories, ordered by newest.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return Category::latest()->get();
    }
}
