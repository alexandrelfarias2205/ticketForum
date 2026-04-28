@props([
    /**
     * Accepted values:
     *  - A ReportStatus / ReportType enum instance (auto-resolves tone + label)
     *  - A string status key: open|in_review|approved|rejected|resolved|published|in_progress|pending|done
     *  - A semantic tone: neutral|info|brand|accent|success|warning|danger
     */
    'status' => null,
    'tone' => null,
    'label' => null,
])

@php
    use App\Enums\ReportStatus;
    use App\Enums\ReportType;

    // Resolve tone + label from enum or string.
    $resolvedTone = $tone;
    $resolvedLabel = $label ?? $slot;

    if ($status instanceof ReportStatus) {
        $resolvedTone ??= $status->badgeTone();
        $resolvedLabel = $resolvedLabel ?: $status->label();
    } elseif ($status instanceof ReportType) {
        $resolvedTone ??= $status->badgeTone();
        $resolvedLabel = $resolvedLabel ?: $status->label();
    } elseif (is_string($status)) {
        $map = [
            'open'         => ['warning', 'Aberto'],
            'pending'      => ['warning', 'Pendente'],
            'in_review'    => ['warning', 'Em Revisão'],
            'pending_review' => ['warning', 'Aguardando Revisão'],
            'approved'     => ['success', 'Aprovado'],
            'rejected'     => ['danger', 'Rejeitado'],
            'resolved'     => ['success', 'Resolvido'],
            'published'    => ['brand', 'Publicado'],
            'published_for_voting' => ['brand', 'Em Votação'],
            'in_progress'  => ['info', 'Em Andamento'],
            'done'         => ['neutral', 'Concluído'],
            'active'       => ['success', 'Ativo'],
            'inactive'     => ['neutral', 'Inativo'],
            // Generic semantic tones
            'neutral'      => ['neutral', null],
            'info'         => ['info', null],
            'brand'        => ['brand', null],
            'accent'       => ['accent', null],
            'success'      => ['success', null],
            'warning'      => ['warning', null],
            'danger'       => ['danger', null],
        ];
        if (isset($map[$status])) {
            $resolvedTone ??= $map[$status][0];
            $resolvedLabel = $resolvedLabel ?: ($map[$status][1] ?? $status);
        } else {
            $resolvedTone ??= 'neutral';
            $resolvedLabel = $resolvedLabel ?: $status;
        }
    }

    $resolvedTone ??= 'neutral';

    $toneClass = match($resolvedTone) {
        'info'    => 'badge-info',
        'brand'   => 'badge-brand',
        'accent'  => 'badge-accent',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger'  => 'badge-danger',
        default   => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$toneClass}"]) }}>
    {{ $resolvedLabel }}
</span>
