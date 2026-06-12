<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use App\Support\Uploads;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $setting = ShopSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['company_name' => $request->user()->name, 'currency' => '৳']
        );

        return view('settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['logo']);

        $setting = ShopSetting::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['company_name' => $request->user()->name, 'currency' => '৳']
        );

        if ($request->hasFile('logo')) {
            $data['logo_path'] = Uploads::storeImage(
                $request->file('logo'),
                'logos/'.$request->user()->id,
                $setting->logo_path
            );
        }

        $setting->update($data);

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }
}
