<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Users;

use App\Actions\Users\CreateUserAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateUser extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'tenant_user';

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role'     => ['required', 'string', 'in:tenant_admin,tenant_user'],
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
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        $this->dispatch('notify', message: 'Usuário criado com sucesso.', type: 'success');

        $this->redirect(route('app.admin.users.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenant.users.create-user');
    }
}
