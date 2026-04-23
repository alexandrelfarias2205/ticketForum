<?php declare(strict_types=1);

namespace App\Actions\Labels;

use App\Models\Label;

class UpdateLabelAction
{
    public function handle(Label $label, array $data): Label
    {
        $label->update([
            'name'  => $data['name'],
            'color' => $data['color'],
        ]);

        return $label->fresh();
    }
}
