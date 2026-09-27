<h2>{{ __('app.company_team.email.heading') }}</h2>

<p>{{ __('app.company_team.email.introduction', [
    'company' => $invitation->company->name,
    'inviter' => $invitation->inviter->name,
]) }}</p>

<p>
    <strong>{{ __('app.company_team.email.company') }}:</strong>
    {{ $invitation->company->name }}
</p>

<p>
    <strong>{{ __('app.company_team.email.inviter') }}:</strong>
    {{ $invitation->inviter->name }}
</p>

<p>
    <strong>{{ __('app.company_team.email.role') }}:</strong>
    {{ __('app.company_team.roles.planning_manager') }}
</p>

<p>
    <a href="{{ $acceptanceUrl }}" style="background:#4f46e5;color:#ffffff;padding:10px 20px;text-decoration:none;border-radius:5px;">
        {{ __('app.company_team.email.accept') }}
    </a>
</p>

<p>{{ __('app.company_team.email.expires', ['date' => $invitation->expires_at->toDateString()]) }}</p>
