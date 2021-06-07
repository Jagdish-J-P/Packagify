<?php

namespace Database\Factories;

use App\Models\EmailList;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailList::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'emailId' => $this->faker->unique()->email(),
            'status' => -1,
            'fromEmail' => 'hktest47@gmail.com',
        ];
    }
}
