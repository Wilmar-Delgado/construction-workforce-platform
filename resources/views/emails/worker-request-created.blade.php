<h2>
    @if($request->worker->company_id)
        New Request for Your Worker
    @else
        You’ve Been Invited to a Mission
    @endif
</h2>

<p>
    @if($request->worker->company_id)
        <strong>{{ $request->company->name }}</strong> has requested one of your workers for a mission.
    @else
        <strong>{{ $request->company->name }}</strong> has invited you to join a mission.
    @endif
</p>

<hr>

<h3>📌 Mission Details</h3>
<p><strong>Title:</strong> {{ $request->mission->title }}</p>

@if($request->message)
    <p><strong>Message:</strong> {{ $request->message }}</p>
@endif

<hr>

<h3>👷 Worker Details</h3>

@if($request->worker->company_id)
    {{-- Company Owner view --}}
    <p><strong>Worker:</strong> {{ $request->worker->name }}</p>
    <p><strong>Role:</strong> {{ ucfirst($request->worker->job) }}</p>
@else
    {{-- Self-employed view --}}
    <p>This request is specifically for you.</p>
@endif

<hr>

<h3>🏢 Requesting Company</h3>
<p><strong>{{ $request->company->name }}</strong></p>

<hr>

<p>
    Please log in to your account to accept or decline this request.
</p>

<p style="margin-top:20px;">
    <a href="{{ config('app.url') }}/mission-management"
       style="background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
        View Request
    </a>
</p>