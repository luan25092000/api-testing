<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'content' => $this->faker->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
