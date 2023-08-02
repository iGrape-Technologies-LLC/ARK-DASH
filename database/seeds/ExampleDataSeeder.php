<?php

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\NoticeCategory;
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Feature;
use App\Models\FeatureValue;

class ExampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attr2 = new Property();
        $attr2->name = 'Talle';
        $attr2->sort = 1;
        $attr2->save();

        $attr7 = new Property();
        $attr7->name = 'Color';
        $attr7->sort = 2;
        $attr7->save();

        $cat = new Category();
        $cat->name = 'Hombres';
        $cat->save();

        $cat = new Category();
        $cat->name = 'Mujeres';
        $cat->save();

        $cat = new Category();
        $cat->name = 'Niños';
        $cat->save();

        $cat = new NoticeCategory();
        $cat->name = 'General';
        $cat->save();

        $cat = new NoticeCategory();
        $cat->name = 'Novedades';
        $cat->save();

        $brand = new Brand();
        $brand->name = 'Adidas';
        $brand->save();

        $brand = new Brand();
        $brand->name = 'Nike';
        $brand->save();

        $tag = new Tag();
        $tag->name = 'Nuevo';
        $tag->color = '#176171';
        $tag->save();

        $tag = new Tag();
        $tag->name = 'Novedad';
        $tag->color = '#d16c6a';
        $tag->save();

        $tag = new Tag();
        $tag->name = 'Sale';
        $tag->color = '#19925b';
        $tag->save();

        $value = new PropertyValue();
        $value->property_id = $attr2->id;
        $value->possible_value = 'S';
        $value->save();

        $value = new PropertyValue();
        $value->property_id = $attr2->id;
        $value->possible_value = 'M';
        $value->save();

        $value = new PropertyValue();
        $value->property_id = $attr2->id;
        $value->possible_value = 'L';
        $value->save();

        $value = new PropertyValue();
        $value->property_id = $attr7->id;
        $value->possible_value = 'Rojo';
        $value->save();

        $value = new PropertyValue();
        $value->property_id = $attr7->id;
        $value->possible_value = 'Blanco';
        $value->save();

        $value = new PropertyValue();
        $value->property_id = $attr7->id;
        $value->possible_value = 'Azul';
        $value->save();

        $feature = new Feature();
        $feature->name = 'Origen';
        $feature->input_type = 'text';
        $feature->save();

        $feature = new Feature();
        $feature->name = 'Material';
        $feature->input_type = 'select';
        $feature->save();

        $feature_val = new FeatureValue();
        $feature_val->possible_value = 'Algodón';
        $feature_val->feature_id = $feature->id;
        $feature_val->save();

        $feature_val = new FeatureValue();
        $feature_val->possible_value = 'Polyester';
        $feature_val->feature_id = $feature->id;
        $feature_val->save();
    }
}
