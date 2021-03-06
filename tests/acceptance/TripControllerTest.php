<?php

namespace Tests\Acceptance;

use Tests\AcceptanceTestCase;
use App\Entities\Trip;
use App\Entities\Type;

class TripControllerTest extends AcceptanceTestCase
{
    public function testView()
    {
        $this->visit('/')->see('mdl-navigation__link" href="'.$this->baseUrl.'/trip">');
    
        $this->visit('/trip')
            ->see('<i class="material-icons">filter_list</i>')
        ;
    }
    
    public function testCreate()
    {
        $fuelType = Type::where('entity_key', 'fuel')->where('name', 'unleaded')->first();

        $this->visit('/trip')->see('<a href="'.$this->baseUrl.'/trip/create');
        
        $this->visit('/trip/create');
    
        $this->type('01/01/2016 15:15:15', 'pickup_date')
            ->type('02/01/2016 15:15:15', 'deliver_date')
            ->type('1200 First Av', 'pickup_place')
            ->type('345 63th St', 'deliver_place')
            ->type('320', 'begin_mileage')
            ->type('450', 'end_mileage')
            ->type('130', 'total_mileage')
            ->type('13.60', 'fuel_cost')
            ->type('50.00', 'fuel_amount')
            ->type('Descricao', 'description')
            ->type($fuelType->id, 'fuel_type')
            ->select('0', 'tank_fill_up')
            ->press('Enviar')
            ->seePageIs('/trip')
        ;
    
        $this->seeInDatabase(
            'trips',
            [
                    'pickup_date' => '2016-01-01 15:15:15',
                    'deliver_date' => '2016-01-02 15:15:15',
                    'pickup_place' => '1200 First Av',
                    'deliver_place' => '345 63th St',
                    'begin_mileage' => 320,
                    'end_mileage' => 450,
                    'total_mileage' => 130,
                    'fuel_cost' => 13.6,
                    'fuel_amount' => 50,
                    'fuel_type' => $fuelType->id,
                    'tank_fill_up' => 0,
                    'description' => 'Descricao',
            ]
        );
    }

    public function testUpdate()
    {
        $fuelType = Type::where('entity_key', 'fuel')->where('name', 'premium')->first();
        
        $this->visit('/trip/'.Trip::all()->last()['id'].'/edit');
    
        $this->type('01/02/2016 15:15:15', 'pickup_date')
            ->type('02/02/2016 15:15:15', 'deliver_date')
            ->type('1220 First Av', 'pickup_place')
            ->type('342 63th St', 'deliver_place')
            ->type('322', 'begin_mileage')
            ->type('452', 'end_mileage')
            ->type('132', 'total_mileage')
            ->type('13.20', 'fuel_cost')
            ->type('20.00', 'fuel_amount')
            ->type('Descricao2', 'description')
            ->type($fuelType->id, 'fuel_type')
            ->select('1', 'tank_fill_up')
            ->press('Enviar')
            ->seePageIs('/trip')
        ;
    
        $this->seeInDatabase(
            'trips',
            [
                'pickup_date' => '2016-02-01 15:15:15',
                'deliver_date' => '2016-02-02 15:15:15',
                'pickup_place' => '1220 First Av',
                'deliver_place' => '342 63th St',
                'begin_mileage' => 322,
                'end_mileage' => 452,
                'total_mileage' => 132,
                'fuel_cost' => 13.2,
                'fuel_amount' => 20,
                'fuel_type' => $fuelType->id,
                'tank_fill_up' => 1,
                'description' => 'Descricao2',
            ]
        );
    }
    
    public function testDelete()
    {
        $idDelete = Trip::all()->last()['id'];
        $this->seeInDatabase('trips', ['id' => $idDelete]);
        $this->visit('/trip/destroy/'.$idDelete);
        $this->seeIsSoftDeletedInDatabase('trips', ['id' => $idDelete]);
    }
    
    public function testErrors()
    {
        $this->visit('/trip/create')
            ->press('Enviar')
            ->seePageIs('/trip/create')
            ->see('de um valor para o campo pickup date.</span>')
            ->see('de um valor para o campo end mileage.</span>')
            ->see('<span class="mdl-textfield__error">O campo fuel cost dever')
            ->see('<span class="mdl-textfield__error">O campo fuel amount dever')
        ;
    }
    
    public function testFilters()
    {
        $this->visit('/trip')
            ->type('Generic Car', 'vehicle')
            ->type('delivery', 'trip_type')
            ->type('01/01/2016 00:00:00', 'pickup_date')
            ->type('15,00', 'fuel_cost')
            ->press('Buscar')
            ->see('Generic Car</div>')
            ->see('delivery</div>')
            ->see('01/01/2016 00:00:00</div>')
            ->see('15,00</div>')
        ;
    }
    
    public function testSort()
    {
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=id-desc')
            ->see('mode_edit</i>');
        
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=id-asc')
            ->see('mode_edit</i>');

        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=vehicle-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=vehicle-asc')
            ->see('mode_edit</i>');

        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=trip-type-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=trip-type-asc')
            ->see('mode_edit</i>');

        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=pickup-date-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=pickup-date-asc')
            ->see('mode_edit</i>');

        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=fuel-cost-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/trip?id=&vehicle=&trip-type=&pickup-date=&fuel-cost=&sort=fuel-cost-asc')
            ->see('mode_edit</i>');
            
    }
}
