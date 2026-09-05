<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $status = $request->get('status');

        $query = InventoryItem::query();

        if ($search) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        if ($category) {
            $query->where('category', $category);
        }

        match ($status) {
            'Available' => $query->whereColumn('quantity_available', '>', 'low_stock_threshold'),
            'Low Stock' => $query->where('quantity_available', '>', 0)->whereColumn('quantity_available', '<=', 'low_stock_threshold'),
            'Out of Stock' => $query->where('quantity_available', 0),
            'In Use' => $query->where('quantity_in_use', '>', 0),
            'Damaged' => $query->where('quantity_damaged', '>', 0),
            default => null,
        };

        $items = $query->orderBy('item_name')->paginate(10)->withQueryString();

        return view('admin.inventory.index', compact('items', 'search', 'category', 'status'));
    }

    public function create()
    {
        return view('admin.inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);

        $validated['quantity_available'] = $validated['quantity_total'];

        InventoryItem::create($validated);

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item added successfully.');
    }

    public function show(InventoryItem $inventoryItem)
    {
        $inventoryItem->load(['assignments.booking.client.user']);

        return view('admin.inventory.show', compact('inventoryItem'));
    }

    public function edit(InventoryItem $inventoryItem)
    {
        return view('admin.inventory.edit', compact('inventoryItem'));
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(InventoryItem::CATEGORIES)],
            'quantity_total' => ['required', 'integer', 'min:0'],
            'quantity_available' => ['required', 'integer', 'min:0'],
            'quantity_damaged' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // quantity_in_use and quantity_returned are system-managed via assignments,
        // not editable here — an admin correcting a physical recount can only
        // touch total/available/damaged/threshold directly.
        $inventoryItem->update($validated);

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        if ($inventoryItem->assignments()->where('status', 'In Use')->exists()) {
            return back()->with('error', 'This item is currently in use at an event and cannot be deleted.');
        }

        $inventoryItem->delete();

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item deleted.');
    }

    protected function validateItem(Request $request): array
    {
        return $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(InventoryItem::CATEGORIES)],
            'quantity_total' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
