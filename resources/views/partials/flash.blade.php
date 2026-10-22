@if(session('success'))
    <div class="mb-4 animate-fade-up rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="mb-4 animate-fade-up rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
        {{ session('warning') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 animate-fade-up rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
        {{ session('error') }}
    </div>
@endif

@if(session('status'))
    <div class="mb-4 animate-fade-up rounded-xl border border-sky-500/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-200">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 animate-fade-up rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
        <p class="mb-1 font-semibold">Please fix the following:</p>
        <ul class="list-disc space-y-0.5 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
