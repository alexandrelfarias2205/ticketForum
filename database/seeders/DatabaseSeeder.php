<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Labels\CreateLabelAction;
use App\Actions\Tenants\CreateTenantAction;
use App\Actions\Users\CreateUserAction;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Enums\UserRole;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Root user
        $root = User::factory()->root()->create([
            'name'  => 'Root Admin',
            'email' => 'root@ticketforum.local',
            'password' => Hash::make('password123456'),
        ]);

        // Labels
        $labelAction = app(CreateLabelAction::class);
        $bugLabel         = $labelAction->handle($root, ['name' => 'bug', 'color' => '#ef4444']);
        $uxLabel          = $labelAction->handle($root, ['name' => 'ux', 'color' => '#8b5cf6']);
        $performanceLabel = $labelAction->handle($root, ['name' => 'performance', 'color' => '#f59e0b']);
        $featureLabel     = $labelAction->handle($root, ['name' => 'feature', 'color' => '#10b981']);

        // Tenant A
        $tenantA    = Tenant::factory()->create(['name' => 'Empresa Alpha', 'slug' => 'empresa-alpha']);
        $adminA     = User::factory()->tenantAdmin($tenantA)->create(['name' => 'Admin Alpha', 'email' => 'admin@alpha.local', 'password' => Hash::make('password123456')]);
        $userA1     = User::factory()->tenantUser($tenantA)->create(['name' => 'João Silva', 'email' => 'joao@alpha.local', 'password' => Hash::make('password123456')]);
        $userA2     = User::factory()->tenantUser($tenantA)->create(['name' => 'Maria Santos', 'email' => 'maria@alpha.local', 'password' => Hash::make('password123456')]);

        // Tenant B
        $tenantB    = Tenant::factory()->create(['name' => 'Empresa Beta', 'slug' => 'empresa-beta']);
        $adminB     = User::factory()->tenantAdmin($tenantB)->create(['name' => 'Admin Beta', 'email' => 'admin@beta.local', 'password' => Hash::make('password123456')]);
        $userB1     = User::factory()->tenantUser($tenantB)->create(['name' => 'Carlos Oliveira', 'email' => 'carlos@beta.local', 'password' => Hash::make('password123456')]);

        // Reports — various statuses
        // Pending review
        Report::factory()->bug()->create(['tenant_id' => $tenantA->id, 'author_id' => $userA1->id, 'title' => 'Erro ao salvar formulário de contato', 'description' => 'Ao clicar em salvar no formulário de contato, a página recarrega mas os dados não são salvos.']);
        Report::factory()->improvement()->create(['tenant_id' => $tenantA->id, 'author_id' => $userA2->id, 'title' => 'Melhorar tempo de carregamento do dashboard', 'description' => 'O dashboard leva mais de 5 segundos para carregar. Seria ótimo se pudesse ser reduzido para menos de 2 segundos.']);

        // Approved
        $approved = Report::factory()->featureRequest()->approved()->create([
            'tenant_id' => $tenantB->id,
            'author_id' => $userB1->id,
            'title' => 'Exportação de relatórios em PDF',
            'description' => 'Seria muito útil poder exportar os relatórios em PDF.',
            'enriched_title' => 'Funcionalidade de Exportação de Relatórios em PDF',
            'enriched_description' => 'Implementar endpoint de geração de PDF usando headless browser ou biblioteca de PDF, com opções de filtro de data e formato de página.',
            'reviewer_id' => $root->id,
            'reviewed_at' => now()->subDays(2),
        ]);
        $approved->labels()->attach([$featureLabel->id]);

        // Published for voting (multiple)
        $published1 = Report::factory()->improvement()->publishedForVoting()->create([
            'tenant_id' => $tenantA->id,
            'author_id' => $userA1->id,
            'title' => 'Modo escuro na interface',
            'description' => 'Adicionar suporte a dark mode.',
            'enriched_title' => 'Suporte a Dark Mode (Modo Escuro)',
            'enriched_description' => 'Implementar variáveis CSS para tema dark, detectar preferência do sistema via prefers-color-scheme, e salvar preferência do usuário.',
            'reviewer_id' => $root->id,
            'reviewed_at' => now()->subDays(5),
            'published_at' => now()->subDays(4),
            'vote_count' => 2,
        ]);
        $published1->labels()->attach([$uxLabel->id, $featureLabel->id]);

        $published2 = Report::factory()->featureRequest()->publishedForVoting()->create([
            'tenant_id' => $tenantB->id,
            'author_id' => $userB1->id,
            'title' => 'Integração com Slack para notificações',
            'description' => 'Receber notificações no Slack quando um relatório for aprovado.',
            'enriched_title' => 'Notificações via Webhook Slack',
            'enriched_description' => 'Configurar webhook Slack por tenant, disparar notificação em eventos: aprovação, publicação, criação de issue.',
            'reviewer_id' => $root->id,
            'reviewed_at' => now()->subDays(3),
            'published_at' => now()->subDays(2),
            'vote_count' => 1,
        ]);
        $published2->labels()->attach([$featureLabel->id]);

        // Votes
        \App\Models\Vote::create(['report_id' => $published1->id, 'user_id' => $userA1->id]);
        \App\Models\Vote::create(['report_id' => $published1->id, 'user_id' => $userB1->id]);
        \App\Models\Vote::create(['report_id' => $published2->id, 'user_id' => $userA2->id]);

        $this->command->info('Seeder concluído. Usuários criados:');
        $this->command->table(
            ['Email', 'Senha', 'Papel', 'Empresa'],
            [
                ['root@ticketforum.local', 'password123456', 'Root', '-'],
                ['admin@alpha.local', 'password123456', 'Admin', 'Empresa Alpha'],
                ['joao@alpha.local', 'password123456', 'Usuário', 'Empresa Alpha'],
                ['maria@alpha.local', 'password123456', 'Usuário', 'Empresa Alpha'],
                ['admin@beta.local', 'password123456', 'Admin', 'Empresa Beta'],
                ['carlos@beta.local', 'password123456', 'Usuário', 'Empresa Beta'],
            ]
        );
    }
}
