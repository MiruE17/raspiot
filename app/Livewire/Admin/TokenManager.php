<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TokenManager extends Component
{
    use WithPagination;
    
    // Search and sorting
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Token creation
    public $userId = null; // Untuk memilih user saat membuat token
    public $expiryDate = null;
    
    // Token display
    public $showNewToken = false;
    public $newToken = null;
    
    // Delete confirmation
    public $showDeleteModal = false;
    public $tokenId = null;
    
    protected $rules = [
        'userId' => 'required|exists:users,id',
        'expiryDate' => 'nullable|date|after:today',
    ];
    
    protected $listeners = [
        'show-modal' => 'showModal'
    ];
    
    public function render()
    {
        $tokens = ApiToken::query()
            ->with(['user', 'creator'])
            ->when($this->search, function ($query) {
                return $query->where(function($query) {
                    // Search by ID if numeric
                    if (is_numeric($this->search)) {
                        $query->where('id', 'like', '%' . $this->search . '%');
                    } else {
                        // Search by user info
                        $query->whereHas('user', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        $users = User::orderBy('name')->get();
            
        return view('livewire.admin.admin-token-manager', [
            'tokens' => $tokens,
            'users' => $users,
        ]);
    }
    
    public function showModal()
    {
        // Reset form
        $this->reset(['userId', 'expiryDate']);
        $this->dispatch('open-modal');
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function createToken()
    {
        $this->validate();

       $expiryDate = $this->expiryDate
        ? \Carbon\Carbon::parse($this->expiryDate)->startOfDay()
        : now()->addDay()->startOfDay();

    // Validasi: tidak boleh lebih dari 1 tahun dari hari ini
    $maxDate = now()->addYear()->startOfDay();
    if ($expiryDate->gt($maxDate)) {
        $this->addError('expiryDate', 'Expiry date cannot be more than 1 year from today.');
        return;
    }
        
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);
        
        $token = ApiToken::create([
            'user_id' => $this->userId,
            'create_uid' => Auth::id(), // Ubah creator_id menjadi create_uid sesuai nama kolom di database
            'token' => $hashedToken,
            'expiry_date' => $expiryDate,
            'active' => true,
            'hit_count' => 0
        ]);
        
        if ($token) {
            $this->newToken = [
                'id' => $token->id,
                'full_token' => $token->id . '|' . $plainToken,
            ];
            
            $this->showNewToken = true;
            $this->dispatch('token-created');
        }
    }
    
    public function dismissNewToken()
    {
        $this->showNewToken = false;
        $this->newToken = null;
    }
    
    public function confirmDelete($tokenId)
    {
        $this->tokenId = $tokenId;
        $this->showDeleteModal = true;
    }
    
    public function cancelDelete()
    {
        $this->tokenId = null;
        $this->showDeleteModal = false;
    }
    
    public function delete()
    {
        $token = ApiToken::find($this->tokenId);
        
        if ($token) {
            $token->active = false;
            $token->save();
            
            $this->dispatch('token-revoked');
        }
        
        $this->tokenId = null;
        $this->showDeleteModal = false;
    }
}