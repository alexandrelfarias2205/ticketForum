<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateReportAction
{
    public function handle(User $author, array $data): Report
    {
        $productId = $this->resolveProductId($author, $data['product_id'] ?? null);

        return Report::create([
            'tenant_id'   => $author->tenant_id,
            'product_id'  => $productId,
            'author_id'   => $author->id,
            'type'        => $data['type'],
            'title'       => $data['title'],
            'description' => $data['description'],
            'status'      => ReportStatus::PendingReview,
        ]);
    }

    /**
     * Verify the product belongs to the author's tenant — prevents cross-tenant assignment.
     */
    private function resolveProductId(User $author, ?string $productId): ?string
    {
        if ($productId === null || $productId === '') {
            return null;
        }

        $exists = Product::withoutGlobalScopes()
            ->where('id', $productId)
            ->where('tenant_id', $author->tenant_id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'product_id' => 'Produto inválido para este tenant.',
            ]);
        }

        return $productId;
    }
}
