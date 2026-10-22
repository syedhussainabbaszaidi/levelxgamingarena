<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingRuleController extends Controller
{
    public function index(): View
    {
        $rules = PricingRule::with(['category', 'resource', 'business'])
            ->orderByDesc('priority')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.pricing.index', [
            'rules' => $rules,
            'categories' => Category::orderBy('name')->get(),
            'resources' => \App\Models\Resource::orderBy('name')->get(),
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        PricingRule::create($this->validated($request));

        return back()->with('success', 'Pricing rule created.');
    }

    public function update(Request $request, PricingRule $rule)
    {
        $rule->update($this->validated($request));

        return back()->with('success', 'Pricing rule updated.');
    }

    public function destroy(PricingRule $rule)
    {
        $rule->delete();

        return back()->with('success', 'Pricing rule deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'resource_id' => ['nullable', 'exists:resources,id'],
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'overnight' => ['boolean'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'min_minutes' => ['required', 'integer', 'min:15'],
            'slot_minutes' => ['required', 'integer', 'min:15'],
            'priority' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $data['category_id'] = $data['category_id'] ?? null;
        $data['resource_id'] = $data['resource_id'] ?? null;
        $data['day_of_week'] = $data['day_of_week'] ?? null;
        $data['overnight'] = $request->boolean('overnight');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
