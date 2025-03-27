<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class UserAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'customer') {
                $query->where('is_admin', false);
            }
        }

        // Sort logic
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'email', 'created_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate(15)->withQueryString();

        // Handle AJAX request
        if ($request->ajax() || $request->filled('ajax')) {
            return view('admin.users.partials.users_table', compact('users'));
        }

        return view('admin.users.index', compact('users'));
    }


    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'is_admin' => 'boolean',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => $request->has('is_admin') ? true : false,
            ]);

            Log::info('User created by admin', [
                'admin_id' => auth()->id(),
                'created_user_id' => $user->id,
                'created_user_email' => $user->email
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$validated['name']}' created successfully.");
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', Password::defaults()],
            'is_admin' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'is_admin' => $request->has('is_admin') ? true : false,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            Log::info('User updated by admin', [
                'admin_id' => auth()->id(),
                'updated_user_id' => $user->id,
                'updated_user_email' => $user->email
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$user->name}' updated successfully.");
        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // Check if user has purchases
        $hasPurchases = $user->purchases()->exists();
        
        if ($hasPurchases) {
            $message = "Cannot delete user '{$user->name}' because they have purchase history. Consider deactivating the account instead.";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('admin.users.index')
                ->with('error', $message);
        }
        
        try {
            // Save user info for logging
            $userName = $user->name;
            $userEmail = $user->email;
            
            // Delete the user
            $user->delete();
            
            Log::info('User deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_user_name' => $userName,
                'deleted_user_email' => $userEmail
            ]);
            
            $message = "User '{$userName}' deleted successfully.";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('admin.users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = 'Failed to delete user: ' . $e->getMessage();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('admin.users.index')
                ->with('error', $message);
        }
    }
    /**
     * View user profile with purchase history
     */
    public function show(User $user)
    {
        // Load user's purchases with items
        $purchases = Purchase::with(['items'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Get user's owned content
        $ownedChapters = $user->chapters()->get();
        $ownedSpells = $user->spells()->get();

        foreach ($ownedChapters as $chapter) {
            // Get spells that are free with this chapter
            $freeSpells = $chapter->spells()
                ->wherePivot('is_free_with_chapter', true)
                ->get();
                
            $ownedSpells = $ownedSpells->concat($freeSpells)->unique('id');;
        }

        return view('admin.users.show', compact('user', 'purchases', 'ownedChapters', 'ownedSpells'));
    }
    
    /**
     * Update the user's owned content
     */
    public function updateOwnedContent(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'chapter_ids' => 'nullable|array',
            'chapter_ids.*' => 'exists:chapters,id',
            'spell_ids' => 'nullable|array',
            'spell_ids.*' => 'exists:spells,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Invalid content selection');
        }

        try {
            // Update chapters
            if ($request->has('chapter_ids')) {
                $user->chapters()->sync($request->chapter_ids);
            } else {
                $user->chapters()->detach();
            }
            
            // Update spells
            if ($request->has('spell_ids')) {
                $user->spells()->sync($request->spell_ids);
            } else {
                $user->spells()->detach();
            }
            
            Log::info('User content updated by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'chapter_count' => count($request->chapter_ids ?? []),
                'spell_count' => count($request->spell_ids ?? [])
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', "User content updated successfully.");
        } catch (\Exception $e) {
            Log::error('Error updating user content', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update user content: ' . $e->getMessage());
        }
    }
}