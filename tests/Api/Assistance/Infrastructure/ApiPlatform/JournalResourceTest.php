<?php

declare(strict_types=1);

namespace App\Tests\Api\Assistance\Infrastructure\ApiPlatform;

use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Tests\Api\Common\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class JournalResourceTest extends ApiTestCase
{
    public function testCreateJournal(): void
    {
        $this->createJournalWithSingleStay();
    }

    public function testGetExistentJournal(): void
    {
        $journalId = $this->createJournalWithSingleStay();

        $response = $this->client->request('GET', '/api/journals/' . $journalId);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseContent = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('stays', $responseContent, 'Response does not contain an "stays" field');
        $this->assertCount(1, $responseContent['stays'], 'Field "stays" contain unexpected number of elements');
        $stay = reset($responseContent['stays']);
        $this->assertIsArray($stay , 'Field "stays" is not array');

        $requiredKeys = ['@id', '@type', 'country', 'purpose', 'dateFrom', 'dateTo'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stay, "Array should have key: $key");
        }
        $this->assertEquals('/api/stays', $stay['@id']);
    }

    public function testAnalyzeExistentJournal(): void
    {
        $journalId = $this->createJournalWithSingleStay();

        $response = $this->client->request('POST', '/api/journals/' . $journalId . '/analyze');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseContent = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('years', $responseContent, 'Response does not contain a "years" field');
        $this->assertIsArray($responseContent['years'], 'Field "years" is not an array');

        // Since we have a stay in 2023, we should have an entry for 2023
        $this->assertArrayHasKey('2023', $responseContent['years'], 'Response does not contain an entry for year 2023');
        $this->assertIsArray($responseContent['years']['2023'], 'Year 2023 entry is not an array');

        // Check the structure of the country residency output
        $countryResidency = $responseContent['years']['2023'][0];
        $this->assertIsArray($countryResidency, 'Country residency entry is not an array');
        $this->assertArrayHasKey('country', $countryResidency, 'Country residency does not contain a "country" field');
        $this->assertArrayHasKey('isResident', $countryResidency, 'Country residency does not contain an "isResident" field');
        $this->assertIsBool($countryResidency['isResident'], 'Field "isResident" is not a boolean');
    }

    public function testDeleteExistentJournal(): void
    {
        $journalId = $this->createJournalWithSingleStay();

        // First, verify the journal exists
        $this->client->request('GET', '/api/journals/' . $journalId);
        $this->assertResponseIsSuccessful();

        // Delete the journal
        $this->client->request('DELETE', '/api/journals/' . $journalId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify the journal no longer exists
        $this->client->request('GET', '/api/journals/' . $journalId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetNonExistentJournal(): void
    {
        $nonExistentId = Uuid::v7()->__toString();

        $this->client->request('GET', '/api/journals/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
        ]);
    }

    private function createJournalWithSingleStay(): string
    {
        $response = $this->client->request('POST', '/api/journals', ['json' => [
            'stays' => [
                [
                    'country' => CountryCode::TURKEY->value,
                    'purpose' => StayPurpose::TOURISM->value,
                    'dateFrom' => '2023-01-01',
                    'dateTo' => '2023-01-10',
                ],
            ],
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseContent = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('@id', $responseContent, 'Response does not contain an @id field');
        $idUrl = $responseContent['@id'];
        $this->assertIsString($idUrl, '@id is not a string');

        $parts = explode('/', $idUrl);
        $journalId = end($parts);
        $this->assertIsString($journalId);
        $this->assertTrue(Uuid::isValid($journalId), "The extracted ID '$journalId' is not a valid UUID");

        return $journalId;
    }
}
