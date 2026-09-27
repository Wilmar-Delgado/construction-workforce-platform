<h2>
    {{ __('app.emails.project_request.heading') }}
</h2>

<p>
    @if($request->company)
        <strong>{{ $request->company->name }}</strong>
        {!! __('app.emails.project_request.company_intro', ['company' => '<strong>' . e($request->company->name) . '</strong>']) !!}
    @else
        {{ __('app.emails.project_request.self_employed_intro') }}
    @endif
</p>

<hr>

<h3>📌 {{ __('app.emails.common.project_details') }}</h3>

<p>
    <strong>{{ __('app.emails.common.title') }}:</strong>
    {{ $request->project->title }}
</p>

@if($request->message)
    <p>
        <strong>{{ __('app.emails.common.message') }}:</strong>
        {{ $request->message }}
    </p>
@endif

<hr>

<h3>👷 {{ __('app.emails.project_request.worker_information') }}</h3>

<p>
    <strong>{{ __('app.emails.common.name') }}:</strong>
    {{ $request->worker->name }}
</p>

<p>
    <strong>{{ __('app.emails.common.role') }}:</strong>
    {{ __('app.profiles_page.jobs.' . $request->worker->job) }}
</p>

@if($request->worker->company)
    <p>
        <strong>{{ __('app.emails.common.company') }}:</strong>
        {{ $request->worker->company->name }}
    </p>
@else
    <p>
        <strong>{{ __('app.emails.project_request.employment') }}:</strong>
        {{ __('app.self_employed') }}
    </p>
@endif

<hr>

@if($request->company)
    <h3>🏢 {{ __('app.emails.project_request.lending_company') }}</h3>

    <p>
        <strong>{{ $request->company->name }}</strong>
    </p>
@endif

<hr>

<p>
    {{ __('app.emails.project_request.login_instruction') }}
</p>

<p style="margin-top:20px;">
    <a href="{{ config('app.url') }}/project-management"
       style="background:#4f46e5;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
        {{ __('app.emails.common.view_request') }}
    </a>
</p>
