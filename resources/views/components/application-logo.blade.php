<img src="{{ asset('img/logo.png') }}"
     alt="{{ \App\Models\Business::primary()?->name ?? config('app.name') }}"
     width="1400" height="379" decoding="async"
     {{ $attributes->merge(['class' => 'w-auto object-contain']) }}>
