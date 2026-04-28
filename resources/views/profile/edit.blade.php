<x-layouts.app title="Perfil">
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <h1 class="page-title">Perfil</h1>
            <p class="page-subtitle">Atualize seus dados, troque sua senha ou exclua sua conta.</p>
        </header>

        <section class="card">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="card">
            @include('profile.partials.update-password-form')
        </section>

        <section class="card">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
</x-layouts.app>
