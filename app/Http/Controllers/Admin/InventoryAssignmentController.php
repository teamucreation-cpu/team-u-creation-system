<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\InventoryAssignment;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAssignmentController extends Controller
{
    public function index()
    {
        $assignments = InventoryAssignment::with(['inventoryItem', 'booking.client.user'])
            ->latest()
            ->paginate(10);

        return view('admin.inventory.assignments.index', compact('assignments'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::where('status', 'Approved')->with('client.user')->orderBy('event_date')->get();
        $items = InventoryItem::where('quantity_available', '>', 0)->orderBy('item_name')->get();
        $selectedBookingId = $request->query('booking_id');
        $selectedItemId = $request->query('item_id');

        return view('admin.inventory.assignments.create', compact('bookings', 'items', 'selectedBookingId', 'selectedItemId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity_assigned' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        if ($booking->status !== 'Approved') {
            return back()->withInput()->with('error', 'Inventory can only be assigned to an approved event.');
        }

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        if ($validated['quantity_assigned'] > $item->quantity_available) {
            return back()->withInput()->with('error', "Only {$item->quantity_available} unit(s) of \"{$item->item_name}\" are available.");
        }

        DB::transaction(function () use ($validated, $item) {
            InventoryAssignment::create([
                'inventory_item_id' => $item->id,
                'booking_id' => $validated['booking_id'],
                'quantity_assigned' => $validated['quantity_assigned'],
                'status' => 'In Use',
                'notes' => $validated['notes'] ?? null,
            ]);

            $item->quantity_available -= $validated['quantity_assigned'];
            $item->quantity_in_use += $validated['quantity_assigned'];
            $item->save();
        });

        return redirect()->route('admin.inventory.assignments.index')->with('success', 'Inventory item assigned to event successfully.');
    }

    /**
     * Mark an in-use assignment as Returned or Damaged, settling the item's
     * quantity pools accordingly. Inventory resolution is an admin-only
     * responsibility — staff no longer have a portal for this.
     */
    public function resolve(Request $request, InventoryAssignment $inventoryAssignment)
    {
        if ($inventoryAssignment->status !== 'In Use') {
            return back()->with('error', 'This item has already been resolved and can no longer be updated.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Returned,Damaged'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($inventoryAssignment, $validated) {
            $item = $inventoryAssignment->inventoryItem;
            $qty = $inventoryAssignment->quantity_assigned;

            if ($validated['status'] === 'Returned') {
                $item->quantity_available += $qty;
                $item->quantity_in_use -= $qty;
                $item->quantity_returned += $qty;
            } else {
                $item->quantity_in_use -= $qty;
                $item->quantity_damaged += $qty;
            }

            $item->save();

            $inventoryAssignment->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $inventoryAssignment->notes,
            ]);
        });

        return back()->with('success', 'Inventory assignment updated.');
    }

    public function destroy(InventoryAssignment $inventoryAssignment)
    {
        DB::transaction(function () use ($inventoryAssignment) {
            // Only the simple "still in use" case is cleanly reversible — quantities
            // already settled into available/damaged pools by a Returned or Damaged
            // assignment are not unwound, to avoid double-counting bugs.
            if ($inventoryAssignment->status === 'In Use') {
                $item = $inventoryAssignment->inventoryItem;
                $item->quantity_available += $inventoryAssignment->quantity_assigned;
                $item->quantity_in_use -= $inventoryAssignment->quantity_assigned;
                $item->save();
            }

            $inventoryAssignment->delete();
        });

        return back()->with('success', 'Inventory assignment removed.');
    }
}
