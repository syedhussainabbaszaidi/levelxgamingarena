@php($customer = $customer ?? null)

<div class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="neon-label" for="name">Name</label>
            <input type="text" name="name" id="name" required value="{{ old('name', $customer->name ?? '') }}"
                   placeholder="Full name" class="input-neon @error('name') input-invalid @enderror">
            <x-neon-error name="name" />
        </div>
        <div>
            <label class="neon-label" for="phone">Phone</label>
            <input type="text" name="phone" id="phone" required value="{{ old('phone', $customer->phone ?? '') }}"
                   placeholder="03xx-xxxxxxx" class="input-neon @error('phone') input-invalid @enderror">
            <x-neon-error name="phone" />
        </div>
    </div>

    <div>
        <label class="neon-label" for="email">Email (optional)</label>
        <input type="email" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}"
               placeholder="name@example.com" class="input-neon @error('email') input-invalid @enderror">
        <x-neon-error name="email" />
    </div>

    <div>
        <label class="neon-label" for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="3" placeholder="Preferences, reminders…"
                  class="input-neon @error('notes') input-invalid @enderror">{{ old('notes', $customer->notes ?? '') }}</textarea>
        <x-neon-error name="notes" />
    </div>
</div>
