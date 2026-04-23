<?php declare(strict_types=1);

namespace App\Livewire\Root\Users;

use App\Actions\Users\CreateUserAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateUser extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'tenant_user';
    public string $tenant_id = '';

    #[Computed]
    public function tenants(): \Illuminate\Database\Eloquent\Collection
    {
        return Tenant::query()->where('is_active', true)->orderBy('name')->get();
    }

    protected function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'  => ['required', 'string', 'min:12', 'confirmed'],
            'role'      => ['required', 'string', 'in:root,tenant_admin,tenant_user'],
            'tenant_id' => [Rule::requiredIf(fn () => $this->role !== 'root'), 'nullable', 'exists:tenants,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'      => 'O nome é obrigatório.',
            'email.required'     => 'O e-mail é obrigatório.',
            'email.email'        => 'Informe um e-mail válido.',
            'email.unique'       => 'Este e-mail já está em uso.',
            'password.required'  => 'A senha é obrigatória.',
            'password.min'       => 'A senha deve ter no mínimo 12 caracteres.',
            'password.confirmed' => 'A confirmação de senha não coincide.',
            'role.required'      => 'O papel é obrigatório.',
            'role.in'            => 'Papel inválido.',
            'tenant_id.required' => 'A empresa é obrigatória para este papel.',
            'tenant_id.exists'   => 'Empresa inválida.',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', User::class);

        $this->validate();

        app(CreateUserAction::class)->handle([
            'name'      => $this->name,
            'email'     => $this->email,
            'password'  => $this->password,
            'role'      => $this->role,
            'tenant_id' => $this->role !== 'root' ? $this->tenant_id : null,
        ]);

        $this->dispatch('notify', message: 'Usuário criado com sucesso.', type: 'success');

        $this->redirect(route('root.users.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.root.users.create-user');
    }
}
