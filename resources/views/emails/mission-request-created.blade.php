<h2>
    New Request to Join Mission
</h2>

<p>
    @if($request->company)
        <strong>{{ $request->company->name }}</strong>
        has offered a worker for your mission.
    @else
        A self-employed worker has applied to your mission.
    @endif
</p>

<hr>

<h3>📌 Mission Details</h3>

<p>
    <strong>Title:</strong>
    {{ $request->mission->title }}
</p>

@if($request->message)
    <p>
        <strong>Message:</strong>
        {{ $request->message }}
    </p>
@endif

<hr>

<h3>👷 Worker Information</h3>

<p>
    <strong>Name:</strong>
    {{ $request->worker->name }}
</p>

<p>
    <strong>Role:</strong>
    {{ ucfirst($request->worker->job) }}
</p>

@if($request->worker->company)
    <p>
        <strong>Company:</strong>
        {{ $request->worker->company->name }}
    </p>
@else
    <p>
        <strong>Employment:</strong>
        Self-employed
    </p>
@endif

<hr>

@if($request->company)
    <h3>🏢 Lending Company</h3>

    <p>
        <strong>{{ $request->company->name }}</strong>
    </p>
@endif

<hr>

<p>
    Please log in to review this request.
</p>

<p style="margin-top:20px;">
    <a href="{{ config('app.url') }}/mission-management"
       style="background:#4f46e5;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
        View Request
    </a>
</p>