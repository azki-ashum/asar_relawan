<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetType;

class AssetController extends Controller
{
    // admin index: show both types and assets (two-section page)
    public function adminIndex()
    {
        $types = AssetType::orderBy('display_name')->get();
        $assets = Asset::with('type')->orderBy('name')->paginate(20);
        return view('admin.assets.index', compact('types', 'assets'));
    }

    public function create()
    {
        $types = AssetType::orderBy('display_name')->get();
        return view('admin.assets.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_id' => 'required|exists:asset_types,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,inactive,maintenance,retired',
            'code' => 'nullable|string|max:64|unique:assets,code',
        ]);
        $data['status'] = $data['status'] ?? 'active';
        $data['is_admin_only'] = $request->has('is_admin_only') ? 1 : 0;
        // create without code first to obtain asset id
        $code = $data['code'] ?? null;
        unset($data['code']);
        $asset = Asset::create($data);

        // generate code using actual asset id if not provided
        if (empty($code)) {
            $type = AssetType::find($asset->type_id);
            $code = Asset::generateCode($type, $asset->name, $asset->id);
        }
        $asset->update(['code' => $code]);
        return redirect()->route('admin.assets.index')->with('success', 'Asset created.');
    }

    public function edit(Asset $asset)
    {
        $types = AssetType::orderBy('display_name')->get();
        return view('admin.assets.edit', compact('asset', 'types'));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'type_id' => 'required|exists:asset_types,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,inactive,maintenance,retired',
            'code' => 'nullable|string|max:64|unique:assets,code,' . $asset->id,
        ]);
        $data['status'] = $data['status'] ?? $asset->status;
        $data['is_admin_only'] = $request->has('is_admin_only') ? 1 : 0;
        $code = $data['code'] ?? null;
        unset($data['code']);
        $asset->update($data);

        // Do not auto-generate code on update. Only change code if a non-empty code was provided.
        if ($code !== null && $code !== '') {
            $asset->update(['code' => $code]);
        }
        return redirect()->route('admin.assets.index')->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('admin.assets.index')->with('success', 'Asset deleted.');
    }
}
