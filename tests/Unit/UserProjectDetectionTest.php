<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserProjectDetectionTest extends TestCase
{
    public function test_detect_project_key_matches_project_names_with_extra_words(): void
    {
        $this->assertSame(User::PROJECT_FPCT, User::detectProjectKey('FPCT Bigabiro'));
        $this->assertSame(User::PROJECT_FPCT, User::detectProjectKey('FPCT Mwana Kondoo Bigabiro'));
        $this->assertSame(User::PROJECT_TAG, User::detectProjectKey('TAG Kasulu'));
        $this->assertSame(User::PROJECT_EAGT, User::detectProjectKey('EAGT Kakonko'));
        $this->assertSame(User::PROJECT_MORAVIAN, User::detectProjectKey('Mwana Kondoo Mission Center'));
        $this->assertSame(User::PROJECT_BAPTIST, User::detectProjectKey('Babtist Nyakitonto'));
        $this->assertSame(User::PROJECT_ANGLICAN, User::detectProjectKey('Anglican Youth Project'));
    }

    public function test_project_logo_for_returns_new_logo_assets(): void
    {
        $this->assertStringContainsString('project-fpct.jfif', User::projectLogoFor('FPCT Bigabiro'));
        $this->assertStringContainsString('project-fpct.jfif', User::projectLogoFor('FPCT Mwana Kondoo Bigabiro'));
        $this->assertStringContainsString('project-tag.png', User::projectLogoFor('TAG Kihinga'));
        $this->assertStringContainsString('project-eagt.png', User::projectLogoFor('Assemblies of God Center'));
        $this->assertStringContainsString('project-moravian.jpeg', User::projectLogoFor('Mwana Kondoo'));
        $this->assertStringContainsString('project-baptist.jfif', User::projectLogoFor('Baptist Church'));
    }
}
