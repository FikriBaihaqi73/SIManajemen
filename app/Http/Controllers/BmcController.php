<?php

namespace App\Http\Controllers;

use App\Models\BmcItem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BmcController extends Controller
{
    public function index()
    {
        $this->authorizeView();
        
        $ceoId = Auth::user()->ceo_id;
        $items = BmcItem::where('ceo_id', $ceoId)->get()->groupBy('block');

        return Inertia::render('Bmc/Index', [
            'items' => $items,
            'canEdit' => $this->canEdit()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeEdit();

        $request->validate([
            'block' => 'required|string',
            'content' => 'required|string',
            'color' => 'nullable|string'
        ]);

        BmcItem::create([
            'ceo_id' => Auth::user()->ceo_id,
            'block' => $request->block,
            'content' => $request->content,
            'color' => $request->color ?? 'blue'
        ]);

        return back();
    }

    public function update(Request $request, BmcItem $bmcItem)
    {
        $this->authorizeEdit($bmcItem);

        $request->validate([
            'content' => 'required|string',
            'color' => 'nullable|string'
        ]);

        $bmcItem->update([
            'content' => $request->content,
            'color' => $request->color ?? $bmcItem->color
        ]);

        return back();
    }

    public function destroy(BmcItem $bmcItem)
    {
        $this->authorizeEdit($bmcItem);
        $bmcItem->delete();
        return back();
    }

    private function authorizeView()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['ceo', 'director', 'manajer'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    private function authorizeEdit($item = null)
    {
        $user = Auth::user();
        if (!$this->canEdit()) {
            abort(403, 'Hanya CEO dan Director yang dapat mengubah data.');
        }

        if ($item && $item->ceo_id !== $user->ceo_id) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function canEdit()
    {
        $user = Auth::user();
        return in_array($user->role, ['ceo', 'director']);
    }
}
