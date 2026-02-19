<?php

namespace App\Http\Controllers;

use App\Models\CompanyVision;
use App\Models\CompanyMission;
use App\Models\CompanyCoreValue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CompanyProfileController extends Controller
{
    public function index()
    {
        $ceoId = Auth::user()->ceo_id;

        return Inertia::render('Company/Index', [
            'vision'      => CompanyVision::where('ceo_id', $ceoId)->first(),
            'missions'    => CompanyMission::where('ceo_id', $ceoId)->orderBy('order')->get(),
            'coreValues'  => CompanyCoreValue::where('ceo_id', $ceoId)->orderBy('order')->get(),
            'isCeo'       => Auth::user()->role === 'ceo'
        ]);
    }

    // VISION
    public function updateVision(Request $request)
    {
        $this->authorizeCeo();

        $request->validate(['content' => 'required|string']);

        CompanyVision::updateOrCreate(
            ['ceo_id' => Auth::user()->ceo_id],
            ['content' => $request->content]
        );

        return back()->with('message', 'Visi berhasil diperbarui');
    }

    // MISSIONS
    public function storeMission(Request $request)
    {
        $this->authorizeCeo();

        $request->validate(['content' => 'required|string']);

        CompanyMission::create([
            'ceo_id' => Auth::user()->ceo_id,
            'content' => $request->content,
            'order' => CompanyMission::where('ceo_id', Auth::user()->ceo_id)->count() + 1
        ]);

        return back();
    }

    public function updateMission(Request $request, CompanyMission $mission)
    {
        $this->authorizeCeo($mission->ceo_id);

        $request->validate(['content' => 'required|string']);

        $mission->update(['content' => $request->content]);

        return back();
    }

    public function destroyMission(CompanyMission $mission)
    {
        $this->authorizeCeo($mission->ceo_id);
        $mission->delete();
        return back();
    }

    // CORE VALUES
    public function storeCoreValue(Request $request)
    {
        $this->authorizeCeo();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        CompanyCoreValue::create([
            'ceo_id' => Auth::user()->ceo_id,
            'title' => $request->title,
            'description' => $request->description,
            'order' => CompanyCoreValue::where('ceo_id', Auth::user()->ceo_id)->count() + 1
        ]);

        return back();
    }

    public function updateCoreValue(Request $request, CompanyCoreValue $coreValue)
    {
        $this->authorizeCeo($coreValue->ceo_id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $coreValue->update($request->only('title', 'description'));

        return back();
    }

    public function destroyCoreValue(CompanyCoreValue $coreValue)
    {
        $this->authorizeCeo($coreValue->ceo_id);
        $coreValue->delete();
        return back();
    }

    private function authorizeCeo($ownerId = null)
    {
        if (Auth::user()->role !== 'ceo') {
            abort(403, 'Hanya CEO yang bisa mengubah data ini');
        }

        if ($ownerId && Auth::user()->ceo_id !== $ownerId) {
            abort(403, 'Akses ditolak');
        }
    }
}
