<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\View\View;

class LabelController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Label::class);

        return view('root.labels.index');
    }

    public function create(): View
    {
        $this->authorize('create', Label::class);

        return view('root.labels.create');
    }

    public function edit(Label $label): View
    {
        $this->authorize('update', $label);

        return view('root.labels.edit', compact('label'));
    }
}
