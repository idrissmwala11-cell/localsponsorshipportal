<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserProjectDetectionTest extends TestCase
{
    public function test_detect_project_key_matches_project_names_with_extra_words(): void
    {
        $this->assertSame(User::PROJECT_PAGT, User::detectProjectKey('PAGT Kasulu'));
        $this->assertSame(User::PROJECT_FPCT, User::detectProjectKey('FPCT Bigabiro'));
        $this->assertSame(User::PROJECT_FPCT, User::detectProjectKey('FPCT Mwana Kondoo Bigabiro'));
        $this->assertSame(User::PROJECT_TAG, User::detectProjectKey('TAG Kasulu'));
        $this->assertSame(User::PROJECT_TAGT, User::detectProjectKey('TAGT Mission Center'));
        $this->assertSame(User::PROJECT_EAGT, User::detectProjectKey('EAGT Kakonko'));
        $this->assertSame(User::PROJECT_MORAVIAN, User::detectProjectKey('Mwana Kondoo Mission Center'));
        $this->assertSame(User::PROJECT_BAPTIST, User::detectProjectKey('Babtist Nyakitonto'));
        $this->assertSame(User::PROJECT_ANGLICAN, User::detectProjectKey('Anglican Youth Project'));
        $this->assertSame(User::PROJECT_KKKT, User::detectProjectKey('KKKT Bigabiro'));
        $this->assertSame(User::PROJECT_KKKT, User::detectProjectKey('ELCT Kasulu Parish'));
    }

    public function test_project_logo_for_returns_new_logo_assets(): void
    {
        $this->assertStringContainsString('project-pagt.', User::projectLogoFor('PAGT Kasulu'));
        $this->assertStringContainsString('project-fpct.', User::projectLogoFor('FPCT Bigabiro'));
        $this->assertStringContainsString('project-fpct.', User::projectLogoFor('FPCT Mwana Kondoo Bigabiro'));
        $this->assertStringContainsString('project-tag.', User::projectLogoFor('TAG Kihinga'));
        $this->assertStringContainsString('project-tag.', User::projectLogoFor('TAGT Kihinga'));
        $this->assertStringContainsString('project-eagt.', User::projectLogoFor('Assemblies of God Center'));
        $this->assertStringContainsString('project-moravian.', User::projectLogoFor('Mwana Kondoo'));
        $this->assertStringContainsString('project-baptist.', User::projectLogoFor('Baptist Church'));
        $this->assertStringContainsString('project-anglican.', User::projectLogoFor('Anglican Church'));
        $this->assertStringContainsString('project-kkkt.', User::projectLogoFor('KKKT Bigabiro'));
    }

    public function test_all_supported_project_keys_resolve_to_non_default_project_logo_assets(): void
    {
        $logoMap = [
            'Anglican Church' => 'project-anglican.',
            'Baptist Church' => 'project-baptist.',
            'PAGT Kasulu' => 'project-pagt.',
            'FPCT Bigabiro' => 'project-fpct.',
            'TAG Kihinga' => 'project-tag.',
            'TAGT Mission' => 'project-tag.',
            'EAGT Kakonko' => 'project-eagt.',
            'KKKT Kasulu' => 'project-kkkt.',
            'Moravian Mission' => 'project-moravian.',
        ];

        foreach ($logoMap as $projectName => $expectedFragment) {
            $logo = User::projectLogoFor($projectName);

            $this->assertStringContainsString($expectedFragment, $logo, $projectName . ' should resolve to its own project logo.');
            $this->assertStringNotContainsString('compassion-mark.png', $logo, $projectName . ' should not fall back to the default logo.');
        }
    }
}
