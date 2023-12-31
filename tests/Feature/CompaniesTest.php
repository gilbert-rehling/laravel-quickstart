<?php
/*
 * Laravel (R)   Kickstart Project
 * @version      CompaniesTest.php -  001 - 15 6 2023
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

namespace Tests\Feature;

use App\Events\NewCompanyNotification;
use App\Listeners\SendNewCompanyNotification;
use App\Mail\CompanyNotification;
use App\Models\Company;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Traits\Authentication;
use Tests\TestCase;

/**
 * Companies tests
 *
 * ToDo: Tried to use a setup() function to trigger user auth but it caused errors - needs investigation
 * ToDo: There is no test for the update action...
 * ToDo: Encountered issues testing email notification when a new company is saved - further testing required
 */
class CompaniesTest extends TestCase
{
    use Authentication;

    /** @var int */
    public int $id;

    /**
     * Application loads.
     *
     * @return void
     */
    public function test_uri()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Companies list test.
     *
     * @return void
     */
    public function test_companies_unverified()
    {
        $response = $this->get('/companies');
        // should redirect to the login
        $response->assertStatus(302);
    }

    /**
     * Companies list test with user.
     *
     * @return void
     */
    public function test_companies_verified()
    {
        // fetch authed user
        $this->setupUser();
        $this->authenticated();

        $response = $this->get('/companies');
        // should load
        $response->assertStatus(200);
    }

    /**
     * Companies create form test.
     *
     * @return void
     */
    public function test_companies_create()
    {
        // fetch authed user
        $this->setupUser();
        $this->authenticated();

        $response = $this->get('/companies/create');
        // should load
        $response->assertStatus(200);
    }

    /**
     * Companies save entity test.
     *
     * @return int
     */
    public function test_companies_post()
    {
        // fetch authed user
        $this->setupUser();
        $this->authenticated();

        // prevent the test from trying tio send real main
        Mail::fake();

        // prevent any events from triggering
        Event::fake();

        // doesn't really do much
        Mail::assertNothingSent();

        $name = 'Unit Test Company 1';
        $response = $this->post(
            '/companies/create',
            [
                'name' => $name,
                'email' => 'unit-test@test-company1.com',
                'website' => 'http://unit-test-company1.com',
            ]
        );

        Event::assertDispatched(NewCompanyNotification::class, function ($e) use ($name) {
            return $e->company->name === $name;
        });

        Event::assertListening( NewCompanyNotification::class, SendNewCompanyNotification::class );

        //Mail::assertSent(CompanyNotification::class, function ($mail) use ($name) {
        //    return $mail->company->name === $name;
        //});

        /*Mail::assertQueued(CompanyNotification::class, function ($mail) use ($name) {
            return $mail->company->name === $name;
        });*/

        // should redirect
        $response->assertStatus(302);

        // get companies to confirm insert
        $companies = Company::all();
        $inserted = $companies[count($companies)-1];
        $this->assertEquals($name, $inserted->name);

        // save the id if the last test passes
        if ($inserted->name === $name) {
            return $inserted->id;
        }

        // this is probably not needed
        //return 0;
    }

    /**
     * Companies edit form test.
     *
     * @depends test_companies_post
     */
    public function test_companies_edit($id)
    {
        // fetch authed user
        $this->setupUser();
        $this->authenticated();

        $response = $this->get('/companies/edit/' . $id);
        // should load
        $response->assertStatus(200);

        return $id;
    }

    /**
     * Companies delete entity test.
     *
     * @depends test_companies_edit
     */
    public function test_companies_delete($id)
    {
        // fetch authed user
        $this->setupUser();
        $this->authenticated();

        $response = $this->get('/companies/delete/' . $id);
        // should redirect
        $response->assertStatus(302);

        // check item is deleted
        $company = Company::find($id);
        self::assertEmpty($company);
    }
}
