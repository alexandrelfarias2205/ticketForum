<?php declare(strict_types=1);

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DuplicateReportNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Report $report,
        public readonly string $matchedExternalIssueId,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Seu reporte foi marcado como duplicata')
            ->greeting('Olá!')
            ->line('Seu reporte "' . $this->report->title . '" foi identificado como duplicata de um problema já existente.')
            ->line('Issue relacionada: ' . $this->matchedExternalIssueId)
            ->line('Adicionamos suas informações ao caso original. Acompanhe o progresso pela mesma issue.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id'                 => $this->report->id,
            'matched_external_issue_id' => $this->matchedExternalIssueId,
            'message'                   => 'Seu reporte foi identificado como duplicata e vinculado a uma issue existente.',
        ];
    }
}
