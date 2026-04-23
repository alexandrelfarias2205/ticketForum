<?php declare(strict_types=1);

namespace App\Actions\Labels;

use App\Models\Label;

class DeleteLabelAction
{
    public function handle(Label $label): void
    {
        $label->reports()->detach();
        $label->delete();
    }
}
