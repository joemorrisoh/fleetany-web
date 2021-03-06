<?php

namespace Tests\Unit;

use Tests\UnitTestCase;
use App\Entities\Company;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\UserController;
use Illuminate\Container\Container as Application;
use App\Repositories\UserRepositoryEloquent;
use App\Entities\User;
use App\Http\Controllers\InviteController;

class UserModelTest extends UnitTestCase
{

    public function testHasContact()
    {
    
        $contact = factory(\App\Entities\Contact::class)->create();
        $user = factory(\App\Entities\User::class)->create([
            'contact_id' => $contact->id,
        ]);
    
        $this->assertEquals($user->contact->name, $contact->name);
    }
    
    public function testHasCompany()
    {
    
        $company = factory(\App\Entities\Company::class)->create();
        $user = factory(\App\Entities\User::class)->create([
            'company_id' => $company->id,
        ]);
    
        $this->assertEquals($user->company->name, $company->name);
    }
    
    public function testSetUp()
    {
        $company = factory(\App\Entities\Company::class)->create();
        $user = factory(\App\Entities\User::class)->create([
            'company_id' => $company->id,
        ]);
        
        $user->setUp();
    
        $this->seeInDatabase('companies', ['id' => Company::all()->last()['id'], 'name' => $user->name . ' Inc.']);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'entry',
            'name' => 'repair'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'entry',
            'name' => 'service'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'vehicle',
            'name' => 'car'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'vehicle',
            'name' => 'truck'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'contact',
            'name' => 'vendor'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'contact',
            'name' => 'driver'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'trip',
            'name' => 'tour'
        ]);
        $this->seeInDatabase('types', ['company_id' => $user->company_id,
            'entity_key' => 'trip',
            'name' => 'delivery'
        ]);
        $this->seeInDatabase('contacts', ['company_id' => $user->company_id,
            'name' => $user->name
        ]);
        $this->seeInDatabase('contacts', ['company_id' => $user->company_id,
            'name' => 'Generic Vendor',
            'license_no' => '123456'
        ]);
        $this->seeInDatabase('contacts', ['company_id' => $user->company_id,
            'name' => 'Generic Driver',
            'license_no' => '123456'
        ]);
        $this->seeInDatabase('models', ['company_id' => $user->company_id,
            'name' => 'Generic Car'
        ]);
        $this->seeInDatabase('models', ['company_id' => $user->company_id,
            'name' => 'Generic Truck'
        ]);
        $this->seeInDatabase('vehicles', ['company_id' => $user->company_id,
            'description' => 'Generic Vehicle',
            'cost' => 50000
        ]);
    
    }
    
    public function testSendEmailInvite()
    {
        \Illuminate\Support\Facades\Request::setSession($this->app['session.store']);
        
        $user = User::findOrFail(1);
        
        Mail::shouldReceive('send')
        ->once()
        ->andReturn(function ($message) {
            $this->assertEquals('fleetany invitation', $message->getSubject());
            $this->assertEquals($user->email, $message->getTo());
            $this->assertEquals(View::make('emails.invite'), $message->getBody());
        });

        try {
            $repo = new UserRepositoryEloquent(new Application);
            $userController = new InviteController($repo);
            $userController->sendEmailInvite($user->id);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
