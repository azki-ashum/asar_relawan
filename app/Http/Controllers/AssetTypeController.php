<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetType;

class AssetTypeController extends Controller
{
    public function create()
    {
        return view('admin.asset_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
        ]);
        AssetType::create($data);
        return redirect()->route('admin.assets.index')->with('success', 'Asset type created.');
    }

    public function edit(AssetType $type)
    {
        return view('admin.asset_types.edit', compact('type'));
    }

    public function update(Request $request, AssetType $type)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
        ]);
        $type->update($data);
        return redirect()->route('admin.assets.index')->with('success', 'Asset type updated.');
    }

    public function destroy(AssetType $type)
    {
        if ($type->assets()->exists()) {
            return redirect()->route('admin.assets.index')->with('error', 'Cannot delete type while assets exist.');
        }
        $type->delete();
        return redirect()->route('admin.assets.index')->with('success', 'Asset type deleted.');
    }
}
