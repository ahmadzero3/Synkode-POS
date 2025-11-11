<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Sale\Sale;
use App\Models\Items\ItemTransaction;

class CustomizationController extends Controller
{
    public function edit()
    {
        $color = DB::table('customizations')->where('key', 'card_header_color')->value('value');
        $borderColor = DB::table('customizations')->where('key', 'card_border_color')->value('value');
        $headingColor = DB::table('customizations')->where('key', 'heading_color')->value('value');
        $toggle_switch = DB::table('customizations')->where('key', 'toggle_switch')->value('value');

        // Get image values
        $image1 = DB::table('customizations')->where('key', 'image_1')->value('value');
        $image2 = DB::table('customizations')->where('key', 'image_2')->value('value');
        $image3 = DB::table('customizations')->where('key', 'image_3')->value('value');

        return view('customization.edit', compact('color', 'borderColor', 'headingColor', 'toggle_switch', 'image1', 'image2', 'image3'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'color' => 'required|string|max:7',
            'border_color' => 'required|string|max:7',
            'heading_color' => 'required|string|max:7',
            'image_1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $toggleState = $request->has('toggle_switch') ? 'active' : 'not_active';

        DB::table('customizations')->updateOrInsert(['key' => 'toggle_switch'], ['value' => $toggleState]);
        DB::table('customizations')->updateOrInsert(['key' => 'card_header_color'], ['value' => $request->input('color')]);
        DB::table('customizations')->updateOrInsert(['key' => 'card_border_color'], ['value' => $request->input('border_color')]);
        DB::table('customizations')->updateOrInsert(['key' => 'heading_color'], ['value' => $request->input('heading_color')]);

        // Handle image uploads
        $this->handleImageUpload($request, 'image_1');
        $this->handleImageUpload($request, 'image_2');
        $this->handleImageUpload($request, 'image_3');

        return response()->json([
            'status' => true,
            'message' => 'Customization updated successfully!',
        ]);
    }

    /**
     * Delete specific image
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_key' => 'required|string|in:image_1,image_2,image_3',
            'image_name' => 'required|string'
        ]);

        try {
            $imageKey = $request->input('image_key');
            $imageName = $request->input('image_name');

            // Delete the image file from storage
            $imagePath = 'public/images/customization/' . $imageName;
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }

            // Instead of setting to null (which causes NOT NULL constraint error),
            // set to empty string or delete the row entirely
            DB::table('customizations')
                ->where('key', $imageKey)
                ->update(['value' => '']); // Set to empty string instead of null

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Image deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle image upload and storage
     */
    private function handleImageUpload(Request $request, $fieldName)
    {
        if ($request->hasFile($fieldName)) {
            $image = $request->file($fieldName);

            // Use original file name but sanitize it
            $originalName = $image->getClientOriginalName();
            $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

            // Store image in public disk with sanitized name
            $image->storeAs('public/images/customization', $sanitizedName);

            // Update or insert in database
            DB::table('customizations')->updateOrInsert(
                ['key' => $fieldName],
                ['value' => $sanitizedName]
            );
        }
    }

    /**
     * Return the top 4 trending item IDs by total sold quantity.
     */
    public function trendingItems(): array
    {
        // Use the model's morph class to match the stored transaction_type
        $transactionType = (new Sale())->getMorphClass();

        return ItemTransaction::query()
            ->select([
                'item_transactions.item_id',
                DB::raw('SUM(item_transactions.quantity) as total_quantity')
            ])
            ->where('item_transactions.transaction_type', $transactionType)
            ->when(optional(auth()->user())->can('dashboard.can.view.self.dashboard.details.only'), function ($query) {
                return $query->where('item_transactions.created_by', auth()->user()->id);
            })
            ->groupBy('item_transactions.item_id')
            ->orderByDesc('total_quantity')
            ->limit(4)
            ->pluck('item_id')
            ->toArray();
    }

    /**
     * Get customization images for customer display slider
     */
    public function getCustomerDisplayImages()
    {
        try {
            $images = [];
            $imageKeys = ['image_1', 'image_2', 'image_3'];

            foreach ($imageKeys as $key) {
                $imageName = DB::table('customizations')->where('key', $key)->value('value');

                // Check if image name is not empty and file exists
                if (!empty($imageName) && Storage::exists('public/images/customization/' . $imageName)) {
                    $images[] = [
                        'url' => asset('storage/images/customization/' . $imageName),
                        'name' => $imageName,
                        'key' => $key
                    ];
                }
            }

            // Add fallback if no images found
            if (empty($images)) {
                $images[] = [
                    'url' => asset('storage/images/noimages/no-image-found.jpg'),
                    'name' => 'default',
                    'key' => 'default'
                ];
            }

            return response()->json([
                'success' => true,
                'images' => $images,
                'total' => count($images)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching customer display images: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'images' => [
                    [
                        'url' => asset('storage/images/noimages/no-image-found.jpg'),
                        'name' => 'error',
                        'key' => 'error'
                    ]
                ],
                'message' => 'Failed to load images'
            ], 500);
        }
    }
}