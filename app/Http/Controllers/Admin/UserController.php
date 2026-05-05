<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Danh sách người dùng.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Khoá/Mở khoá tài khoản.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->deleted_at) {
            $user->restore();
            $message = 'Đã mở khoá tài khoản.';
        } else {
            $user->delete();
            $message = 'Đã khoá tài khoản.';
        }

        return back()->with('success', $message);
    }
}
