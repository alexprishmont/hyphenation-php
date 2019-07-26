<?php
class ApiCest 
{    
    public function tryApi(ApiTester $I)
    {
        $I->sendGET('/pattern');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }
}