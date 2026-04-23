<?php declare(strict_types=1);

namespace App\Http\Controllers\Root;

use Illuminate\View\View;

final class DeliveredController
{
    public function __invoke(): View
    {
        return view('root.delivered.index');
    }
}
