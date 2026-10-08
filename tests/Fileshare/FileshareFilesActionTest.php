<?php

namespace Tests\Fileshare;

use App\Fileshare\ConnectionTypes\NextcloudConnection;
use App\Fileshare\ConnectionTypes\OwncloudConnection;
use App\Fileshare\Models\Fileshare;
use Tests\FileshareTestCase;

class FileshareFilesActionTest extends FileshareTestCase
{
    public function testItGetsFilesForAConnection(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ieH2auj5AhKaengoD4Taeng9o')
            ->withDirs('badenpowell', ['/pictures', '/lala']);

        $connection = Fileshare::factory()
            ->type(OwncloudConnection::from(['user' => 'badenpowell', 'password' => 'ieH2auj5AhKaengoD4Taeng9o', 'base_url' => env('TEST_OWNCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), [
            'parent' => null,
        ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'lala')
            ->assertJsonPath('data.0.path', '/lala')
            ->assertJsonPath('data.0.parent', '/')
            ->assertJsonPath('data.1.name', 'pictures')
            ->assertJsonPath('data.1.path', '/pictures')
            ->assertJsonPath('data.1.parent', '/');
    }

    public function testItGetsSubdirectories(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ieH2auj5AhKaengoD4Taeng9o')
            ->withDirs('badenpowell', ['/pictures', '/lala', '/lala/dd', '/lala/ff']);

        $connection = Fileshare::factory()
            ->type(OwncloudConnection::from(['user' => 'badenpowell', 'password' => 'ieH2auj5AhKaengoD4Taeng9o', 'base_url' => env('TEST_OWNCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), ['parent' => '/pictures'])->assertJsonCount(0, 'data');
        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), ['parent' => '/lala'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'dd')
            ->assertJsonPath('data.0.path', '/lala/dd')
            ->assertJsonPath('data.0.parent', '/lala')
            ->assertJsonPath('data.1.name', 'ff')
            ->assertJsonPath('data.1.path', '/lala/ff')
            ->assertJsonPath('data.1.parent', '/lala');
    }

    public function testItGetsSubdirectoriesOfSubdirectory(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ieH2auj5AhKaengoD4Taeng9o')
            ->withDirs('badenpowell', ['/lala', '/lala/dd', '/lala/dd/ee']);

        $connection = Fileshare::factory()
            ->type(OwncloudConnection::from(['user' => 'badenpowell', 'password' => 'ieH2auj5AhKaengoD4Taeng9o', 'base_url' => env('TEST_OWNCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), ['parent' => '/lala/dd'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'ee')
            ->assertJsonPath('data.0.path', '/lala/dd/ee')
            ->assertJsonPath('data.0.parent', '/lala/dd');
    }

    public function testItGetsFilesWithDot(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ieH2auj5AhKaengoD4Taeng9o')
            ->withDirs('badenpowell', ['/1. aa']);

        $connection = Fileshare::factory()
            ->type(OwncloudConnection::from(['user' => 'badenpowell', 'password' => 'ieH2auj5AhKaengoD4Taeng9o', 'base_url' => env('TEST_OWNCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), ['parent' => '/'])
            ->assertJsonPath('data.0.name', '1. aa');
    }

    public function testItGetsFilesFromNextcloudConnection(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ohke5ko7ohBae8aiPh7fuu6ka')
            ->withDirs('badenpowell', ['/pictures', '/lala']);

        $connection = Fileshare::factory()
            ->type(NextcloudConnection::from(['user' => 'badenpowell', 'password' => 'ohke5ko7ohBae8aiPh7fuu6ka', 'base_url' => env('TEST_NEXTCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), [
            'parent' => null,
        ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'lala')
            ->assertJsonPath('data.0.path', '/lala')
            ->assertJsonPath('data.0.parent', '/')
            ->assertJsonPath('data.1.name', 'pictures')
            ->assertJsonPath('data.1.path', '/pictures')
            ->assertJsonPath('data.1.parent', '/');
    }

    public function testItGetsNextcloudSubdirectoriesOfSubdirectory(): void
    {
        $this->withoutExceptionHandling()->login()->loginNami()->withUser('badenpowell', 'ailiew7AhshiWae4va9OphieN')
            ->withDirs('badenpowell', ['/lala', '/lala/dd', '/lala/dd/ee']);

        $connection = Fileshare::factory()
            ->type(OwncloudConnection::from(['user' => 'badenpowell', 'password' => 'ailiew7AhshiWae4va9OphieN', 'base_url' => env('TEST_NEXTCLOUD_DOMAIN')]))
            ->create();

        $this->postJson(route('api.fileshare.files', ['fileshare' => $connection]), ['parent' => '/lala/dd'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'ee')
            ->assertJsonPath('data.0.path', '/lala/dd/ee')
            ->assertJsonPath('data.0.parent', '/lala/dd');
    }
}
