@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="admin-header">
    <h1 class="admin-page-title">Product Management</h1>
    <a href="{{ route('admin.products.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Create New Product
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Products</h2>
        <div class="admin-card-actions">
            <form method="GET" action="{{ route('admin.products.index') }}" class="admin-search-form">
                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}" class="admin-search-input">
                <button type="submit" class="btn-admin-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>SKU</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        @else
                            <div style="width: 50px; height: 50px; background: rgba(138, 43, 226, 0.2); border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box" style="color: rgba(138, 43, 226, 0.5);"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $product->title }}</strong>
                        @if($product->slug)
                            <br><small style="color: rgba(255, 255, 255, 0.5);">{{ $product->slug }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background-color: rgba(138, 43, 226, 0.3);">
                            {{ ucfirst(str_replace('_', ' ', $product->type)) }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $product->currency }} ${{ number_format($product->price, 2) }}</strong>
                    </td>
                    <td>
                        <form action="{{ route('admin.products.toggle-active', $product) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="status-toggle" style="background: none; border: none; cursor: pointer; padding: 0;">
                                @if($product->is_active)
                                    <span class="badge" style="background-color: rgba(34, 197, 94, 0.7);">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge" style="background-color: rgba(239, 68, 68, 0.7);">
                                        <i class="fas fa-times-circle"></i> Inactive
                                    </span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td>{{ $product->sku ?? '-' }}</td>
                    <td>{{ $product->created_at->format('M d, Y') }}</td>
                    <td class="actions-cell">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn-admin-secondary" title="View Product" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn-admin-secondary" title="Edit Product">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-admin-secondary" title="Delete Product">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 3rem; color: rgba(255, 255, 255, 0.5);">
                        <i class="fas fa-box" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No products found</p>
                        <a href="{{ route('admin.products.create') }}" class="btn-admin-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Create Your First Product
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .admin-search-form {
        display: flex;
        gap: 0.5rem;
    }

    .admin-search-input {
        padding: 0.5rem 1rem;
        background: rgba(10, 10, 30, 0.5);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        color: white;
        min-width: 250px;
    }

    .admin-search-input:focus {
        outline: none;
        border-color: rgba(138, 43, 226, 0.6);
    }

    .status-toggle:hover {
        opacity: 0.8;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .actions-cell form {
        margin: 0;
    }

    .actions-cell .btn-admin-secondary {
        margin-right: 0.25rem;
    }
</style>
@endpush