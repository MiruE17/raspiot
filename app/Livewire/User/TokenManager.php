<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TokenManager extends Component
{
    use WithPagination;
    
    // Search and sorting
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Form properties
    public $expiryDate = null;
    
    // Token display after creation
    public $newToken = null;
    public $showNewToken = false;
    
    // Delete confirmation
    public $showDeleteModal = false;
    public $tokenId = null;
    
    // Create modal
    public $showCreateModal = false;

    protected $rules = [
        'expiryDate' => 'nullable|date|after:today',
    ];
    
    protected $listeners = [
        'show-modal' => 'showModal',
        'copy-token-event' => 'copyToken'
    ];

    public function render()
    {
        $userId = Auth::id();
        
        $tokens = ApiToken::where('user_id', $userId)
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    if (is_numeric($this->search)) {
                        $query->where('id', 'like', '%' . $this->search . '%');
                    } else {
                        $query->where('token', 'like', '%' . $this->search . '%');
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.user.user-token-manager', [
            'tokens' => $tokens,
        ]);
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
        
        $userId = Auth::id();
        
        // Convert expiry date to proper format if provided
        $expiryDate = $this->expiryDate ? Carbon::parse($this->expiryDate) : null;
        
        // Create the token
        list($token, $displayToken) = ApiToken::createToken($userId, $expiryDate);
        
        // Store token for display (will only be shown once)
        $this->newToken = $displayToken;
        $this->showNewToken = true;
        
        // Reset the form
        $this->expiryDate = null;
        
        // Dispatch event for notifications
        $this->dispatch('token-created');
    }
    
    public function copyToken()
    {
        // Add debug log
        \Log::debug('copyToken method called');
        
        if ($this->newToken && isset($this->newToken['full_token']) && !empty($this->newToken['full_token'])) {
            $tokenText = (string) $this->newToken['full_token'];
            
            // Debug log for token value (first 5 chars only for security)
            \Log::debug('Token value (first 5 chars): ' . substr($tokenText, 0, 5) . '...');
            
            // PENTING: Gunakan format array seperti ini
            $this->dispatch('copy-to-clipboard', [
                'text' => $tokenText
            ]);
            
            // Tambahkan log untuk memverifikasi event dispatched
            \Log::debug('copy-to-clipboard event dispatched');
        } else {
            // Log debug information about what's missing
            \Log::warning('Cannot copy token: ' . 
                ($this->newToken ? 'newToken exists, ' : 'newToken missing, ') . 
                (isset($this->newToken['full_token']) ? 'full_token set, ' : 'full_token missing, ') . 
                (!empty($this->newToken['full_token']) ? 'full_token not empty' : 'full_token empty'));
                
            $this->dispatch('token-copy-error');
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
        $this->showDeleteModal = false;
        $this->tokenId = null;
    }
    
    public function delete()
    {
        if ($this->tokenId) {
            $token = ApiToken::where('id', $this->tokenId)
                ->where('user_id', Auth::id())
                ->first();
                
            if ($token) {
                $token->update(['active' => false]);
                $this->dispatch('token-revoked');
            }
        }
        
        $this->showDeleteModal = false;
        $this->tokenId = null;
    }
}