<?php

class PatternApiCest
{
    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @example { "pattern": ".ad4der" }
     */
    public function checkFullPatternsList(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Get full patterns list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'pattern' => $example['pattern']
        ]);
    }

    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @example {"patternID": 150000}
     */
    public function tryToGetNotExistingPattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to get not existing word and pattern');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/pattern/' . $example['patternID']);
        $I->seeResponseCodeIsClientError();
    }

    protected function patternProvider()
    {
        return [
            ['pattern' => 'testcase']
        ];
    }

    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @dataProvider patternProvider
     */
    public function tryCreatePattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to create pattern via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/pattern', ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $I->sendGET('/pattern');
        $I->seeResponseContainsJson([
            'pattern' => $example['pattern']
        ]);
    }

    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @dataProvider patternProvider
     * @throws Exception
     */
    public function deletePattern(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to delete temporary pattern.');
        $I->sendGET('/pattern');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseContainsJson();
        $I->seeResponseContainsJson(['pattern' => $example['pattern']]);

        $id = $I->grabDataFromResponseByJsonPath('$.data.*.id');
        $id = $id[0];

        $I->sendDELETE('/pattern/' . $id, []);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $I->sendGET('/pattern');
        $I->dontSeeResponseContainsJson([
            'pattern' => $example['pattern']
        ]);
    }
}