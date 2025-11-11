<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    /**
     * Save user's POS sorting preference (including manual sorting order)
     */
    public function saveSortingPreference(Request $request)
    {
        $data = $request->validate([
            'sorting_preference' => 'required|in:a_to_z,z_to_a,latest_product,oldest_product,manual_sorting',
            'manual_order'       => 'nullable|array',
            'manual_order.*'     => 'integer',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $userData = [
            'pos_sorting_preference' => $data['sorting_preference'],
        ];

        if ($data['sorting_preference'] === 'manual_sorting' && !empty($data['manual_order'])) {
            $userData['pos_manual_order'] = json_encode($data['manual_order']); // Save as JSON
        } else {
            $userData['pos_manual_order'] = null; // Clear it if switching to another mode
        }

        $user->update($userData);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Sorting preference saved successfully.',
            'preference'  => $data['sorting_preference'],
            'manualOrder' => $data['manual_order'] ?? [],
        ]);
    }

    /**
     * Add other POS-related methods here
     */
}