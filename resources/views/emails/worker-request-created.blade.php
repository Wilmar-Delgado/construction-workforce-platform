<h2>
    @if($request->worker->company_id)
        {{ __('app.emails.worker_request.company_heading') }}
    @else
        {{ __('app.emails.worker_request.self_employed_heading') }}
    @endif
</h2>

<p>
    @if($request->worker->company_id)
        {!! __('app.emails.worker_request.company_intro', ['company' => '<strong>' . e($request->company->name) . '</strong>']) !!}
    @else
        {!! __('app.emails.worker_request.self_employed_intro', ['company' => '<strong>' . e($request->company->name) . '</strong>']) !!}
    @endif
</p>

<hr>

<h3>📌 {{ __('app.emails.common.project_details') }}</h3>
<p><strong>{{ __('app.emails.common.title') }}:</strong> {{ $request->project->title }}</p>

@if($request->message)
    <p><strong>{{ __('app.emails.common.message') }}:</strong> {{ $request->message }}</p>
@endif

<hr>

<h3>👷 {{ __('app.emails.worker_request.worker_details') }}</h3>

@if($request->worker->company_id)
    {{-- Company Owner view --}}
    <p><strong>{{ __('app.emails.common.worker') }}:</strong> {{ $request->worker->name }}</p>
    <p><strong>{{ __('app.emails.common.role') }}:</strong> {{ __('app.profiles_page.jobs.' . $request->worker->job) }}</p>
@else
    {{-- Self-employed view --}}
    <p>{{ __('app.emails.worker_request.self_employed_notice') }}</p>
@endif

<hr>

<h3>🏢 {{ __('app.emails.worker_request.requesting_company') }}</h3>
<p><strong>{{ $request->company->name }}</strong></p>

<hr>

<p>
    {{ __('app.emails.worker_request.login_instruction') }}
</p>

<p style="margin-top:20px;">
    <a href="{{ config('app.url') }}/project-management"
       style="background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
        {{ __('app.emails.common.view_request') }}
    </a>
</p>
