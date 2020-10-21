<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = ucwords($this->faker->unique()->word . ' ' . $this->faker->unique()->word);
        $slug = Str::slug($title);

        return [
            'id' => Uuid::uuid4()->toString(),
            'name' => $title,
            'slug' => $slug,
        ];
    }
}
