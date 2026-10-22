@php
    $businessId = \App\Models\Business::primary()?->id ?? 1;
    $resource = $resource ?? null;
    $defaultCover = asset('img/activities/default.svg');
    $currentCover = $resource?->imageUrl() ?: $defaultCover;
    $fallbackCover = $resource?->fallbackImageUrl() ?? $defaultCover;

    // Pricing values are edited in the activity's own unit (per hour, per
    // minute or a fixed package price) and converted by the controller.
    $pricingType = old('pricing_type', $resource->pricing_type ?? \App\Models\Resource::PRICING_HOURLY);
    $rateValue = old('price', ! $resource ? '' : ($resource->isFixed()
        ? $resource->fixed_price
        : ($resource->isPerMinute() ? round($resource->hourly_rate / 60, 2) : $resource->hourly_rate)));
    $saleValue = old('sale_price', ! $resource || $resource->sale_price === null ? '' : ($resource->isFixed()
        ? $resource->sale_price
        : ($resource->isPerMinute() ? round($resource->sale_price / 60, 2) : $resource->sale_price)));
@endphp

@push('scripts')
<script>
(function () {
    var searchInput = document.getElementById('image-search');
    var searchBtn = document.getElementById('image-search-btn');
    var results = document.getElementById('image-search-results');
    var status = document.getElementById('image-search-status');
    var preview = document.getElementById('image-preview');
    var libraryInput = document.getElementById('image-library');
    var sourceInput = document.getElementById('image-source');
    var fileInput = document.getElementById('image-input');
    var defaultCover = @json($defaultCover);

    if (!searchBtn || !results || !preview) return;

    function setPreview(url) {
        preview.onerror = function () {
            this.onerror = null;
            this.src = preview.dataset.fallback || defaultCover;
        };
        preview.src = url;
    }

    function clearSelectionHighlight() {
        Array.prototype.forEach.call(results.children, function (child) {
            child.classList.remove('ring-2', 'ring-neon-500');
        });
    }

    function select(item, button) {
        clearSelectionHighlight();
        button.classList.add('ring-2', 'ring-neon-500');

        setPreview(item.preview);

        if (item.file) {
            libraryInput.value = item.file;
            sourceInput.value = '';
        } else {
            sourceInput.value = item.url || '';
            libraryInput.value = '';
        }

        // Choosing from search replaces any pending manual upload.
        if (fileInput) fileInput.value = '';
        status.textContent = 'Selected "' + item.title + '" — save to store it with this activity.';
    }

    function render(items) {
        results.innerHTML = '';

        if (!items.length) {
            status.textContent = 'No images found. Try a different activity name or keyword.';
            return;
        }

        status.textContent = 'Click an image to use it as the activity cover.';

        items.forEach(function (item) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'group relative h-20 overflow-hidden rounded-lg border border-white/10 transition hover:border-neon-500/60 focus:outline-none';
            button.title = item.title;

            var img = document.createElement('img');
            img.src = item.preview;
            img.alt = item.title;
            img.loading = 'lazy';
            img.className = 'h-full w-full object-cover transition duration-300 group-hover:scale-105';
            img.addEventListener('error', function () { this.src = defaultCover; });
            button.appendChild(img);

            var label = document.createElement('span');
            label.className = 'absolute inset-x-0 bottom-0 truncate bg-ink-950/80 px-1.5 py-0.5 text-left text-[10px] text-zinc-200';
            label.textContent = item.title;
            button.appendChild(label);

            button.addEventListener('click', function () { select(item, button); });
            results.appendChild(button);
        });
    }

    function run() {
        var query = (searchInput.value || '').trim();

        if (!query) {
            var nameField = document.querySelector('input[name="name"]');
            if (nameField) query = nameField.value.trim();
        }

        status.textContent = 'Searching…';
        results.innerHTML = '';

        fetch('{{ route('admin.activities.image-search') }}?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok) {
                    render(data.results || []);
                } else {
                    throw new Error('bad response');
                }
            })
            .catch(function () {
                status.textContent = 'Image search failed. Please try again.';
            });
    }

    searchBtn.addEventListener('click', run);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); run(); }
    });

    // Manual upload wins over a search selection and previews instantly.
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file || (file.type && file.type.indexOf('image/') !== 0)) return;

            libraryInput.value = '';
            sourceInput.value = '';

            var objectUrl = URL.createObjectURL(file);
            setPreview(objectUrl);
            preview.onload = function () { URL.revokeObjectURL(objectUrl); };
            status.textContent = 'Ready to upload "' + file.name + '" — save to store it.';
        });
    }
})();
</script>
@endpush

<div class="grid gap-6 lg:grid-cols-3">
    <div class="neon-card space-y-4 p-5 lg:col-span-2">
        <input type="hidden" name="business_id" value="{{ old('business_id', $businessId) }}">

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="neon-label">Name</label>
                <input type="text" name="name" required value="{{ old('name', $resource->name ?? '') }}" class="input-neon" placeholder="Snooker Table 1">
            </div>
            <div>
                <label class="neon-label">Code (optional)</label>
                <input type="text" name="code" value="{{ old('code', $resource->code ?? '') }}" class="input-neon" placeholder="SNK-1">
            </div>
        </div>

        <div>
            <label class="neon-label">Category</label>
            <select name="category_id" required class="select-neon">
                <option value="">— select —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $resource->category_id ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="neon-label">Description</label>
            <textarea name="description" rows="3" class="input-neon" placeholder="Shown on the customer booking page">{{ old('description', $resource->description ?? '') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="neon-label">Capacity (players)</label>
                <input type="number" name="capacity" min="1" required value="{{ old('capacity', $resource->capacity ?? 1) }}" class="input-neon">
            </div>
            <div>
                <label class="neon-label">Default duration (min)</label>
                <input type="number" name="duration_minutes" min="15" step="15" required value="{{ old('duration_minutes', $resource->duration_minutes ?? 60) }}" class="input-neon">
            </div>
            <div>
                <label class="neon-label">Pricing type</label>
                <select name="pricing_type" id="pricing-type" class="select-neon" required>
                    @foreach(\App\Models\Resource::PRICING_TYPES as $value => $label)
                        <option value="{{ $value }}" @selected($pricingType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="rounded-xl border border-neon-500/20 bg-neon-500/[0.04] p-4">
            <p class="text-xs font-bold uppercase tracking-widest text-neon-300">Pricing</p>

            <div class="mt-3 grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="neon-label" for="price-input" id="price-label">Rate per hour</label>
                    <input id="price-input" type="number" step="0.01" min="0" name="price"
                           value="{{ $rateValue }}" class="input-neon" placeholder="0.00">
                    <p id="price-hint" class="mt-1 text-xs text-zinc-500">Charged for every hour booked.</p>
                    @error('price')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    @error('hourly_rate')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="neon-label">Sale price (optional)</label>
                    <input type="number" step="0.01" min="0" name="sale_price"
                           value="{{ $saleValue }}" class="input-neon" placeholder="Leave empty for no promotion">
                    <p class="mt-1 text-xs text-zinc-500">Same unit as the rate above. Shown struck through on the site.</p>
                    @error('sale_price')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="neon-label">Promotion label (optional)</label>
                    <input type="text" name="promo_label" maxlength="60"
                           value="{{ old('promo_label', $resource->promo_label ?? '') }}" class="input-neon" placeholder="Opening discount">
                    <p class="mt-1 text-xs text-zinc-500">Shown next to the discounted price.</p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2" data-pricing="fixed">
                <div>
                    <label class="neon-label">Package duration (minutes)</label>
                    <input type="number" name="package_minutes" min="15" step="15"
                           value="{{ old('package_minutes', $resource->package_minutes ?? '') }}" class="input-neon"
                           placeholder="e.g. 300 for a 5-hour package">
                    <p class="mt-1 text-xs text-zinc-500">Leave empty for a one-time charge (like an account opening fee).</p>
                </div>
                <div class="flex items-end">
                    <p class="rounded-lg border border-white/10 bg-ink-900/60 p-3 text-xs text-zinc-400">
                        Fixed packages charge the package price once, for the full block of time.
                    </p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label">Minimum booking (minutes)</label>
                    <input type="number" name="min_minutes" min="15" step="15"
                           value="{{ old('min_minutes', $resource->min_minutes ?? '') }}" class="input-neon"
                           placeholder="Business default">
                    <p class="mt-1 text-xs text-zinc-500">Optional. Rate windows can raise this (e.g. 3-hour PC special).</p>
                    @error('min_minutes')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="neon-label">Maximum booking (minutes)</label>
                    <input type="number" name="max_minutes" min="15" step="15"
                           value="{{ old('max_minutes', $resource->max_minutes ?? '') }}" class="input-neon"
                           placeholder="No limit">
                    <p class="mt-1 text-xs text-zinc-500">Optional. Bookings still cannot run past closing time.</p>
                    @error('max_minutes')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label">Add-on label (optional)</label>
                    <input type="text" name="addon_label" maxlength="80"
                           value="{{ old('addon_label', $resource->addon_label ?? '') }}" class="input-neon"
                           placeholder="additional controller">
                </div>
                <div>
                    <label class="neon-label">Add-on price / hour (optional)</label>
                    <input type="number" step="0.01" min="0" name="addon_price"
                           value="{{ old('addon_price', $resource->addon_price ?? '') }}" class="input-neon"
                           placeholder="0.00">
                    <p class="mt-1 text-xs text-zinc-500">Displayed as an extra: +300 RS additional controller.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="neon-label">Status</label>
                <select name="status" class="select-neon">
                    @foreach(['active' => 'Active (bookable)', 'inactive' => 'Inactive', 'maintenance' => 'Under maintenance'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $resource->status ?? 'active') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-zinc-500">Maintenance activities cannot be booked.</p>
            </div>
            <div>
                <label class="neon-label">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $resource->sort_order ?? 0) }}" class="input-neon">
                <p class="mt-1 text-xs text-zinc-500">Controls the order customers see activities in.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="neon-card p-5">
            <label class="neon-label">Activity image</label>

            {{-- Live preview. Always visible; broken/missing sources fall back to themed artwork. --}}
            <div class="mb-3 overflow-hidden rounded-lg border border-white/10 bg-ink-900/60">
                <img id="image-preview"
                     src="{{ $currentCover }}"
                     data-fallback="{{ $fallbackCover }}"
                     onerror="this.onerror=null;this.src=this.dataset.fallback;"
                     alt="Activity image preview"
                     class="h-40 w-full object-cover">
            </div>

            {{-- Selected cover: a bundled gallery file or a remote search result. --}}
            <input type="hidden" name="image_library" id="image-library" value="{{ old('image_library') }}">
            <input type="hidden" name="image_source" id="image-source" value="{{ old('image_source') }}">

            <div class="space-y-2">
                <label class="neon-label" for="image-search">Search cover art</label>
                <div class="flex gap-2">
                    <input id="image-search" type="text" class="input-neon"
                           value="{{ old('name', $resource->name ?? '') }}"
                           placeholder="e.g. PS5 Gaming">
                    <button type="button" id="image-search-btn" class="btn-neon whitespace-nowrap !px-3" aria-label="Search images">
                        <x-icon name="search" class="h-4 w-4" />
                    </button>
                </div>
                <p id="image-search-status" class="text-xs text-zinc-500">
                    Search by activity name, then click a result to use it as the cover.
                </p>
                <div id="image-search-results" class="grid grid-cols-3 gap-2"></div>
            </div>

            <div class="mt-4 border-t border-white/[0.06] pt-4">
                <label class="neon-label" for="image-input">Or upload your own</label>
                <input id="image-input" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="input-neon !p-2 text-xs file:mr-3 file:rounded file:border-0 file:bg-neon-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                <p class="mt-2 text-xs text-zinc-500">JPG, PNG or WebP up to 2 MB. Uploading replaces the selected search result.</p>
                @error('image')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror

                @if($resource?->image_path)
                    <label class="mt-3 flex items-center gap-2 text-xs text-zinc-400">
                        <input type="checkbox" name="remove_image" value="1"
                               class="rounded border-white/20 bg-ink-900 text-rose-500 focus:ring-rose-500">
                        Remove the current image (falls back to the category artwork)
                    </label>
                @endif
            </div>
        </div>

        <div class="neon-card space-y-3 p-5">
            <h3 class="text-sm font-semibold text-white">Availability</h3>
            <label class="flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-white/[0.03]">
                <span class="text-sm text-zinc-300">Bookable online</span>
                <input type="checkbox" name="is_online_bookable" value="1"
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                       @checked(old('is_online_bookable', $resource->is_online_bookable ?? true))>
            </label>
            <label class="flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-white/[0.03]">
                <span class="text-sm text-zinc-300">Featured on homepage</span>
                <input type="checkbox" name="featured" value="1"
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                       @checked(old('featured', $resource->featured ?? false))>
            </label>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var type = document.getElementById('pricing-type');
    var label = document.getElementById('price-label');
    var hint = document.getElementById('price-hint');

    function sync() {
        var value = type.value;
        var copy = {
            hourly: ['Rate per hour', 'Charged for every hour booked. Hours are multiplied automatically.'],
            per_minute: ['Rate per minute', 'Charged per minute played (e.g. 15 for 15 RS/minute).'],
            fixed: ['Package price', 'Charged once for the whole package, regardless of duration.'],
        }[value] || ['Rate', '']; // eslint-disable-line

        if (label) label.textContent = copy[0];
        if (hint) hint.textContent = copy[1];

        document.querySelectorAll('[data-pricing]').forEach(function (el) {
            var types = (el.dataset.pricing || '').split(',');
            el.classList.toggle('hidden', types.indexOf(value) === -1);
        });
    }

    if (type) {
        type.addEventListener('change', sync);
        sync();
    }
})();
</script>
@endpush
