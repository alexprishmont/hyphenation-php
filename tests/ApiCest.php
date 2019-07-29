<?php

class ApiCest
{
    /**
     * @param ApiTester $I
     * @example { "pattern": ".ad4der" }
     */
    public function checkFullPatternsList(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Get full patterns list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'pattern' => $example['pattern']
        ]);
    }

    /**
     * @param ApiTester $I
     * @example { "word": "mistranslate" }
     */
    public function checkFullWordsList(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Get full words list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'original_word' => $example['word'],
        ]);
    }

    /**
     * @param ApiTester $I
     * @example {"wordID": 1500000, "patternID": 150000}
     */
    public function tryToGetNotExistingWordAndPattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to get not existing word and pattern');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern/' . $example['patternID']);
        $I->seeResponseCodeIsClientError();
        $I->seeResponseIsJson();

        $I->sendGET('/word/' . $example['wordID']);
        $I->seeResponseCodeIsClientError();
        $I->seeResponseIsJson();
    }

    /**
     * @return array
     */
    protected function wordProvider()
    {
        return [
            ['word' => 'working']
        ];
    }

    /**
     * @param ApiTester $I
     * @dataProvider wordProvider
     */
    public function tryCreateWord(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to create word via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/word', ['word' => $example['word']]);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Creation successfully proceeded.'
        ]);
    }

    /**
     * @param ApiTester $I
     * @dataProvider wordProvider
     */
    public function tryDeleteWord(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to delete temporary word.');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['original_word' => $example['word']]);

        $response = json_decode($I->grabResponse(), true);
        $response = $response['data'];
        $id = $response[sizeof($response) - 1]['id'];

        $I->sendDELETE('/word/' . $id, []);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
    }

    protected function patternProvider()
    {
        return [
            ['pattern' => 'testcase']
        ];
    }

    /**
     * @param ApiTester $I
     * @dataProvider patternProvider
     */
    public function tryCreatePattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to create pattern via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/pattern', ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson();
        $I->seeResponseContainsJson([
            'message' => 'Creation successfully proceeded.'
        ]);
    }

    /**
     * @param ApiTester $I
     * @dataProvider patternProvider
     */
    public function tryDeletePattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to delete temporary pattern.');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseContainsJson();
        $I->seeResponseContainsJson(['pattern' => $example['pattern']]);

        $response = json_decode($I->grabResponse(), true);
        $response = $response['data'];
        $id = $response[0]['id'];

        $I->sendDELETE('/pattern/' . $id, []);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
    }
}