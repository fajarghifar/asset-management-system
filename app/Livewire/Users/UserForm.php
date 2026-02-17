<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm extends Component
{
    public bool $isEditing = false;
    public ?User $user = null;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user?->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'password' => [
                $this->isEditing ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.users.user-form');
    }

    #[On('create-user')]
    public function create(): void
    {
        $this->reset(['name', 'username', 'email', 'password', 'password_confirmation', 'user', 'isEditing']);
        $this->dispatch('open-modal', name: 'user-form-modal');
    }

    #[On('edit-user')]
    public function edit(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->password = ''; // Don't show password
        $this->password_confirmation = '';
        $this->isEditing = true;
        $this->dispatch('open-modal', name: 'user-form-modal');
    }

    public function validationAttributes(): array
    {
        return [
            'name' => __('Name'),
            'username' => __('Username'),
            'email' => __('Email'),
            'password' => __('Password'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->isEditing && $this->user) {
                $data = [
                    'name' => $this->name,
                    'username' => $this->username,
                    'email' => $this->email,
                ];

                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }

                $this->user->update($data);
                $message = __('User updated successfully.');
            } else {
                User::create([
                    'name' => $this->name,
                    'username' => $this->username,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);
                $message = __('User created successfully.');
            }

            $this->dispatch('close-modal', name: 'user-form-modal');
            $this->dispatch('pg:eventRefresh-users-table');
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: __('An unexpected error occurred.'), type: 'error');
        }
    }
}
