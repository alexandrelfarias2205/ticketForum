<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Users;

use App\Actions\Users\UpdateUserAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditUser extends Component
{
    public User $user;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'tenant_user';
    public bool $is_active = true;

    public function mount(User $user): void
    {
        abort_unless(
            $user->tenant_id === auth()->user()->tenant_id,
            403,
            'Acesso não autorizado.'
        );

        $this->user      = $user;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->role->value;
        $this->is_active = $user->is_active;
    }

    protected function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password'  => ['nullable', 'string', 'min:12', 'confirmed'],
            'role'      => ['required', 'string', 'in:tenant_admin,tenant_user'],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'      => 'O nome é obrigatório.',
            'email.required'     => 'O e-mail é obrigatório.',
            'email.email'        => 'Informe um e-mail válido.',
            'email.unique'       => 'Este e-mail já está em uso.',
            'password.min'       => 'A senha deve ter no mínimo 12 caracteres.',
            'password.confirmed' => 'A confirmação de senha não coincide.',
            'role.required'      => 'O papel é obrigatório.',
            'role.in'            => 'Papel inválido.',
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'role'      => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->password !== '') {
            $data['password'] = $this->password;
        }

        app(UpdateUserAction::class)->handle($this->user, $data);

        $this->dispatch('notify', message: 'Usuário atualizado com sucesso.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.tenant.users.edit-user');
    }
}
