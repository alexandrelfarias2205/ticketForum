<?php declare(strict_types=1);

namespace App\Actions\Labels;

use App\Models\Label;
use App\Models\User;

class CreateLabelAction
{
    public function handle(User $creator, array $data): Label
    {
        return Label::create([
            'name'       => $data['name'],
            'color'      => $data['color'] ?? '#6366f1',
            'created_by' => $creator->id,
        ]);
    }
}
