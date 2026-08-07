<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->slug ??= Str::slug($category->name).'-'.Str::random(5);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active', 'parent_id'])
            ->logOnlyDirty()
            ->useLogName('category');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('name', 'ilike', "%{$term}%") : $query;
    }
}
