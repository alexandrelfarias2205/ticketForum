<?php declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('app.admin.users.index');
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('app.admin.users.create');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('app.admin.users.edit', compact('user'));
    }
}
