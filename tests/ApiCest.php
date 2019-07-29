<?php

class ApiCest
{
    const FIND_IN_PATTERNS = '.ad4der';
    const FIND_IN_WORDS = 'mistranslate';

    public function checkFullPatternsList(ApiTester $I)
    {
        $I->wantToTest('Get full patterns list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'pattern' => self::FIND_IN_PATTERNS
        ]);
    }

    public function checkFullWordsList(ApiTester $I)
    {
        $I->wantToTest('Get full words list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'original_word' => self::FIND_IN_WORDS,
        ]);
    }

    public function tryToGetNotExistingWordAndPattern(ApiTester $I)
    {
        $I->wantToTest('Try to get not existing word and pattern');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern/1500000');
        $I->seeResponseCodeIsClientError();
        $I->seeResponseIsJson();

        $I->sendGET('/word/1500000');
        $I->seeResponseCodeIsClientError();
        $I->seeResponseIsJson();
    }

    public function tryCreateWord(ApiTester $I)
    {
        $I->wantToTest('Try to create word via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/word', ['word' => 'working']);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Creation successfully proceeded.'
        ]);
    }

    public function tryCreatePattern(ApiTester $I)
    {
        $I->wantToTest('Try to create pattern via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/pattern', ['pattern' => 'testcase']);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson();
        $I->seeResponseContainsJson([
            'message' => 'Creation successfully proceeded.'
        ]);
    }

    public function tryDeleteWord(ApiTester $I)
    {
        $I->wantToTest('Try to delete temporary word.');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['original_word' => 'working']);

        $response = json_decode($I->grabResponse(), true);
        $response = $response['data'];
        $id = $response[sizeof($response) - 1]['id'];

        $I->sendDELETE('/word/' . $id, []);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
    }

    public function tryDeletePattern(ApiTester $I)
    {
        $I->wantToTest('Try to delete temporary pattern.');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseContainsJson();
        $I->seeResponseContainsJson(['pattern' => 'testcase']);

        $response = json_decode($I->grabResponse(), true);
        $response = $response['data'];
        $id = $response[0]['id'];

        $I->sendDELETE('/pattern/' . $id, []);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
    }
}