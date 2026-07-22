<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Category;


class AdminController extends Controller
{

    public function index()
    {
        return view('admin.index');
    }
    public function brands()
    {
        $query = Brand::query();

        if (request()->filled('search')) {
            $search = request('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%');
            });
        }

        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        $brands = $query
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

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
        $brand->status = $request->boolean('status');

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
    public function generateThumbnailImage($image, $imageName, $folder, $width = 124, $height = 124)
    {
        $thumbnailPath = public_path($folder . '/thumbnails/');
        if (!file_exists($thumbnailPath)) {
            mkdir($thumbnailPath, 0755, true);
        }

        Image::decode($image)->resize($width, $height)->save($thumbnailPath . '/' . $imageName);
    }

    public function brandEdit($id, Request $request)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brandUpdate(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:brands,slug,' . $id,
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $brand = Brand::findOrFail($id);
        $brand->name = $request->name;
        $brand->slug = $request->slug ? Str::slug($request->slug) : $request->name;
        $brand->status = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            // Delete old images
            if ($brand->image) {
                @unlink(public_path('uploads/brands/' . $brand->image));
                @unlink(public_path('uploads/brands/thumbnails' . $brand->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $this->generateThumbnailImage($request->image, $imageName, 'uploads/brands', 124, 124);
            $request->image->move(public_path('uploads/brands'), $imageName);
            $brand->image = $imageName;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('success', 'Brand updated successfully!');
    }

    public function brandDelete($id)
    {
        $brand = Brand::findOrFail($id);

        // Delete images
        if ($brand->delete()) {
            @unlink(public_path('uploads/brands/' . $brand->image));
            @unlink('uploads/brands/thumbnails/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('success', 'Brand deleted successfully');
    }

    public function categories()
    {
        $query = Category::query();

        if (request()->filled('search')) {
            $search = request('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%');
            });
        }

        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        $categories = $query
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories', compact('categories'));
    }

    public function categoryAdd()
    {
        return view('admin.category-add');
    }

    public function categoryStore(Request $request)
    {
        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $request->merge([
            'slug' => $slug,
        ]);

        $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:categories,slug',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|boolean',
        ]);


        $category = new Category();

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->boolean('status');

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = time() . '_' . uniqid() . '.' . $image->extension();


            // Save original image
            $image->move(
                public_path('uploads/categories'),
                $imageName
            );


            // Create thumbnail
            $this->generateThumbnailImage(
                public_path('uploads/categories/' . $imageName),
                $imageName,
                'uploads/categories',
                124,
                124
            );


            $category->image = $imageName;
        }

        $category->save();

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Category added successfully.');
    }
}
