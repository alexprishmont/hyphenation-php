<?php

class WordApiCest
{
    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @example { "word": "mistranslate" }
     */
    public function checkFullWordsList(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Get full words list');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful(); // change to 200

        $I->seeResponseContainsJson([
            'original_word' => $example['word'],
        ]);
    }

    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @example {"wordID": 150000}
     */
    public function tryToGetNotExistingWord(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to get not existing word ');
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
     * @param Codeception\Example $example
     * @dataProvider wordProvider
     */
    public function tryCreateWord(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to create word via API');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/word', ['word' => $example['word']]);
        $I->seeResponseCodeIsSuccessful();

        $I->sendGET('/word');
        $I->seeResponseContainsJson([
            'original_word' => $example['word']
        ]);
    }

    /**
     * @param ApiTester $I
     * @param Codeception\Example $example
     * @dataProvider wordProvider
     * @throws Exception
     */
    public function deleteWord(ApiTester $I, Codeception\Example $example)
    {
        $I->wantToTest('Try to delete temporary word.');
        $I->sendGET('/word');
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['original_word' => $example['word']]);

        $id = $I->grabDataFromResponseByJsonPath('$.data.*.id');
        $id = $id[sizeof($id) - 1];

        $I->sendDELETE('/word/' . $id);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $I->sendGET('/word');
        $I->dontSeeResponseContainsJson([
            'original_word' => $example['word']
        ]);
    }
}