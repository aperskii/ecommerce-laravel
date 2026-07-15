<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderby('id','DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function brandAdd(Request $request)
    {
        return view('admin.brand-add');
    }

public function brandStore(Request $request)
{
    $slug = $request->filled('slug')
        ? Str::slug($request->slug)
        : Str::slug($request->name);

    $request->merge([
        'slug' => $slug,
    ]);

    $request->validate([
        'name'   => 'required|string|max:255',
        'slug'   => 'required|string|max:255|unique:brands,slug',
        'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'status' => 'nullable|boolean',
    ]);


    $brand = new Brand();

    $brand->name = $request->name;
    $brand->slug = $request->slug;
    $brand->status = $request->has('status');


    if ($request->hasFile('image')) {

        $image = $request->file('image');

        $imageName = time() . '_' . uniqid() . '.' . $image->extension();


        // Save original image
        $image->move(
            public_path('uploads/brands'),
            $imageName
        );


        // Create thumbnail
        $this->generateThumbnailImage(
            public_path('uploads/brands/' . $imageName),
            $imageName,
            'uploads/brands',
            124,
            124
        );


        $brand->image = $imageName;
    }


    $brand->save();


    return redirect()
        ->route('admin.brands')
        ->with('success', 'Brand added successfully.');
}
    public function generateThumbnailImage($image, $imageName, $folder, $width = 124, $height = 124) {
        $thumbnailPath = public_path($folder . '/thumbnails/');
        if (!file_exists($thumbnailPath)) {
            mkdir($thumbnailPath,0755, true);
        }

        Image::decode($image)->resize($width, $height)->save($thumbnailPath . '/' . $imageName);
    }
}
