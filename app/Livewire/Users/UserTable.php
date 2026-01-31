<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;

final class UserTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'users-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('users_export_' . now()->format('Y_m_d'))
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('username')
            ->add('email')
            ->add('created_at_formatted', fn (User $model) => $model->created_at->format('d/m/Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->hidden(),

            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Username', 'username')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(User $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>')
                ->class('bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-blue-700 dark:hover:bg-blue-800')
                ->dispatch('show-user', ['user' => $row->id])
                ->tooltip('View Details'),

            Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-amber-700 dark:hover:bg-amber-800')
                ->dispatch('edit-user', ['user' => $row->id])
                ->tooltip('Edit User'),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('bg-red-500 hover:bg-red-600 text-white p-2 rounded-md flex items-center justify-center dark:bg-red-700 dark:hover:bg-red-800')
                ->dispatch('open-delete-modal', [
                    'component' => 'users.user-table',
                    'method' => 'delete-user',
                    'params' => ['rowId' => $row->id],
                    'title' => 'Delete User?',
                    'description' => "Are you sure you want to delete user '{$row->name}'? This action cannot be undone.",
                ])
                ->tooltip('Delete User'),
        ];
    }

    #[\Livewire\Attributes\On('delete-user')]
    public function delete($rowId): void
    {
        $user = User::find($rowId);

        if ($user) {
            try {
                if ($user->id === auth()->id()) {
                    $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
                    return;
                }

                $name = $user->name;
                $user->delete();
                $this->dispatch('pg:eventRefresh-users-table');
                $this->dispatch('toast', message: "User '{$name}' deleted successfully.", type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Failed to delete user: ' . $e->getMessage(), type: 'error');
            }
        }
    }
}
