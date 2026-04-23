<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('root.users.index');
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('root.users.create');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('root.users.edit', compact('user'));
    }
}
