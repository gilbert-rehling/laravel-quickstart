<?php
/*
 * Laravel (R)   Kickstart Project
 * @version      CreateCompanyRequest.php -  001 - 15 6 2023
 * @link         https://github.com/gilbert-rehling/caremaster
 * @copyright    Copyright (c) 2023.  Gilbert Rehling. All right reserved. (https://github.com/gilbert-rehling)
 * @license      Released under the MIT model
 * @author       Gilbert Rehling:    gilbert@gilbert-rehling.com
 * This kickstart project provides basic authentication along with modeling for 2 data sets with a foreign key example
 * Created using Laravel 9.* on PHP 8.0.
 * To get started download and extract the package to your desired location.
 * Run: composer install.
 * Create an appropriate database. MySQL was used for this project.
 * Create and populate the .env file
 * Run: php artisan migrate
 * To seed the admin user run: php artisan db:seed --class=CreateUser
 * Seed data is also available for the Companies dataset.
 * Run: php artisan storage:link - to enable access to the public images
 */

namespace App\Http\Requests;

/**
 * @uses
 */
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for new company entities
 */
class CreateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Provide the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'      => 'required|string|min:3|max:256',
            'email'     => 'sometimes|email|nullable',
            'website'   => 'sometimes|string|min:3|max:256|nullable',
            'logo'      => 'sometimes|image|dimensions:min_width=100,min_height=100'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name'      => 'Please enter a valid company name',
            'email'     => 'Please enter a valid email address',
            'website'   => 'The website address is not valid',
            'logo'      => 'The logo does not meet minimum requirements',
            'name.unique' => 'The  company name provided already exists in the database',
        ];
    }
}
