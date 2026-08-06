<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\AdminRole;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                return response()->json([
                    'status' => false,
                    'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }
            $data = $request->only('email', 'password');
            $validator = Validator::make($data, [
                'email' => ['required', 'email', 'max:40'],
                'password' => ['required', 'string', 'max:20'],
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (Auth::guard('admin')->attempt([
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => 1,
            ])) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful.',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }
            RateLimiter::hit($throttleKey, 300);
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password.',
            ], 422);
        }

        return view('admin.login');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('admin/login');
    }

    public function users(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $title = $admin->name;
        $search = trim((string) $request->query('search', ''));
        if (in_array($admin->type, ['superadmin', 'admin'])) {
            $users = Admin::select('id', 'image', 'ap_id', 'name', 'email', 'type', 'mobile', 'status')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                }))
                ->latest('id')
                ->cursorPaginate($this->perPage($request))
                ->withQueryString()
                ->through(fn (Admin $user) => $user->only(['id', 'image', 'ap_id', 'name', 'email', 'type', 'mobile', 'status']));
        } else {
            $users = collect([$admin]);
        }
        return view('admin.accounts.admin-user', compact('title', 'users'));
    }
    public function showUser(Admin $user)
    {
        return response()->json([
            'user' => $user,
            'image_url' => $user->image ? asset('admin/adminimage/'.$user->image) : null,
        ]);
    }

    // public function storeUser(Request $request)
    // {
    //     Admin::create($this->validateUser($request));
    //     return response()->json(['message' => 'User added successfully.'], 201);
    // }
    public function storeUser(Request $request)
    {
        $data = $this->validateUser($request);
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
        }
        $admin = Admin::create($data);
        $admin->ap_id = 1000 + $admin->id;
        $admin->save();
        $this->clearUserCache();
        return response()->json([
            'message' => 'User added successfully.'
        ], 201);
    }
   public function updateUser(Request $request, Admin $user)
    {
        $data = $this->validateUser($request, $user);
        if ($request->hasFile('image')) {
            $this->deleteOldImage($user->image);
            $data['image'] = $this->uploadImage($request->file('image'));
        }
        $user->update($data);
        $this->clearUserCache();
        return response()->json(['message' => 'User updated successfully.']);
    }

    public function deleteUser(Admin $user)
    {
        if ($user->is(Auth::guard('admin')->user())) {
            return response()->json(['message' => 'You cannot delete the logged-in user.'], 422);
        }
        $this->deleteOldImage($user->image);
        $user->delete();
        $this->clearUserCache();
        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function updateUserStatus(Admin $user)
    {
        $user->update(['status' => ! $user->status]);
        $this->clearUserCache();
        return response()->json(['message' => 'User status updated successfully.']);
    }

    private function validateUser(Request $request, ?Admin $user = null): array
    {
        $data = $request->validate([
            'ap_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($user)],
            'password' => $user ? ['nullable', 'string', 'min:6', 'max:255'] : ['required', 'string', 'min:6', 'max:255'],
            'status' => ['required', 'boolean'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'email.unique' => 'This email address is already in use.',
            'image.image' => 'Please select a valid image file.',
            'image.mimes' => 'The image must be a JPG, JPEG, PNG, GIF, or WEBP file.',
            'image.max' => 'The image size must not exceed 2 MB.',
        ]);

        if ($user && blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    private function uploadImage($file): string
    {
        $destinationPath = public_path('admin/adminimage');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Keep the longest side at 1200px and store a compressed WebP copy.
        // This preserves a good visual quality while reducing server storage.
        $imageName = time() . '_' . Str::random(10) . '.webp';
        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown(width: 1200, height: 1200);
        $image->encodeUsingFormat(Format::WEBP, quality: 75)
            ->save($destinationPath . '/' . $imageName);

        return $imageName;
    }

    private function deleteOldImage(?string $imageName): void
    {
        if ($imageName) {
            $imagePath = public_path('admin/adminimage/' . $imageName);
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
    }

    private function clearUserCache(): void
    {
    }


    public function permissionUser($id)
    {
        $user = Admin::findOrFail($id);
        $title = "Set Permission for " . $user->name;
        $userPermissions = AdminRole::where('admin_id', $user->id)
            ->get()
            ->keyBy('module')
            ->map(fn (AdminRole $role) => $role->only([
                'view_access', 'add_access', 'edit_access', 'delete_access', 'no_access',
            ]))
            ->all();
        $modules = $this->permissionModules();
        return view('admin.accounts.permission', compact('user', 'title', 'userPermissions', 'modules'));
    }
    public function updatePermissionUser(Request $request, $id)
    {
        $user = Admin::findOrFail($id);
        $permissions = $request->input('permissions', []);

        // Modules come directly from the existing admin_roles table.
        // This also clears permissions when every checkbox is unchecked.
        $modules = $this->permissionModules();

        foreach ($modules as $module) {
            $access = $permissions[$module] ?? [];
            $noAccess = isset($access['no_access']);
            $fullAccess = ! $noAccess && isset($access['full_access']);

            AdminRole::updateOrCreate(
                [
                    'admin_id' => $user->id,
                    'module'   => $module
                ],
                [
                    'view_access' => $fullAccess || (! $noAccess && isset($access['view_access'])) ? 1 : 0,
                    'edit_access' => $fullAccess || (! $noAccess && isset($access['edit_access'])) ? 1 : 0,
                    'add_access'  => $fullAccess || (! $noAccess && isset($access['add_access'])) ? 1 : 0,
                    'delete_access' => $fullAccess || (! $noAccess && isset($access['delete_access'])) ? 1 : 0,
                    'no_access'   => $noAccess ? 1 : 0,
                ]
            );
        }
        return response()->json([
            'status' => true,
            'message' => 'Permissions updated successfully!'
        ]);
    }

    private function permissionModules()
    {
        return AdminRole::query()->select('module')->distinct()->pluck('module')
            ->merge(['admin', 'section', 'category', 'setting', 'tag'])->unique()->sort()->values();
    }
}
