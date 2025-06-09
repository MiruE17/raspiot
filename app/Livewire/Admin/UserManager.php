<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserManager extends Component
{
    use WithPagination;
    use WithFileUploads;
    
    protected $paginationTheme = 'tailwind';
    
    // Properties
    public $userId;
    public $name;
    public $email;
    public $is_admin = 0; // Default to user (not admin)
    public $password;
    public $password_confirmation;
    public $changePassword = false;
    public $viewMode = false;
    public $selectedUser;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showDeleteModal = false;
    public $userToDelete = null;
    
    // Validation rules
    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:50',
            'email' => ['required', 'email', 'max:255', $this->userId ? 'unique:users,email,'.$this->userId : 'unique:users,email'],
            'is_admin' => 'required|boolean',
            'password' => $this->userId && !$this->changePassword ? '' : 'required|min:8|confirmed',
        ];
    }
    
    // Lifecycle hooks
    public function mount()
    {
        $this->resetInputFields();
    }
    
    public function render()
    {
        $users = User::where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->paginate(10);
                    
        return view('livewire.admin.admin-user-manager', [
            'users' => $users
        ]);
    }
    
    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    // Create new user
    public function create()
    {
        $this->resetInputFields();
        $this->viewMode = false;
        $this->dispatch('show-modal');
    }
    
    // View user details
    public function view($id)
    {
        $this->viewMode = true;
        $this->selectedUser = User::findOrFail($id);
        $this->dispatch('show-modal');
    }
    
    // Edit user
    public function edit($id)
    {
        $this->resetInputFields();
        $this->viewMode = false;
        
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_admin = $user->is_admin ? 1 : 0; // Tetapkan nilai boolean ke integer untuk form radio
        
        $this->dispatch('show-modal');
    }
    
    // Store user data
    public function store()
    {
        $this->validate();
        
        // Prepare user data
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => (bool)$this->is_admin, // Konversi dari input form ke boolean
        ];
        
        // Add password only if it's a new user or password is being changed
        if (!$this->userId || $this->changePassword) {
            $userData['password'] = Hash::make($this->password);
        }
        
        if ($this->userId) {
            // Update existing user
            $user = User::find($this->userId);
            $user->update($userData);
            session()->flash('message', 'User updated successfully.');
        } else {
            // Create new user
            User::create($userData);
            session()->flash('message', 'User created successfully.');
        }
        
        $this->resetInputFields();
        $this->dispatch('hide-modal');
    }
    
    // Delete confirmation
    public function confirmDelete($id)
    {
        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }
    
    // Delete user
    public function delete()
    {
        $id = $this->userToDelete;
        $user = User::find($id);
        
        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }
        
        // Delete user
        $user->delete();
        
        session()->flash('message', 'User deleted successfully.');
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }
    
    // Cancel delete
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }
    
    // Reset input fields
    private function resetInputFields()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->is_admin = 0; // Default to user
        $this->password = '';
        $this->password_confirmation = '';
        $this->changePassword = false;
        $this->viewMode = false;
        $this->selectedUser = null;
    }
}