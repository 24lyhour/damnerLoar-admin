<?php

namespace Tests\Unit;

use App\Support\InertiaPage;
use PHPUnit\Framework\TestCase;

class InertiaPageTest extends TestCase
{
    public function test_app_pages_resolve_to_resources_js_pages(): void
    {
        $this->assertSame(
            'resources/js/pages/dashboard/Overview.vue',
            InertiaPage::viteEntry('dashboard/Overview'),
        );
    }

    public function test_module_pages_resolve_to_the_module_folder(): void
    {
        $this->assertSame(
            'Modules/Merchant/resources/js/pages/Dashboard/V1/Merchant/Index.vue',
            InertiaPage::viteEntry('merchant::Dashboard/V1/Merchant/Index'),
        );
    }

    public function test_every_module_page_file_the_resolver_returns_exists(): void
    {
        $root = dirname(__DIR__, 2);

        foreach (['merchant::Dashboard/V1/Merchant/Index', 'merchant::Dashboard/V1/Merchant/Show', 'merchant::Dashboard/V1/Merchant/Edit', 'merchant::Dashboard/V1/Merchant/Invite', 'merchant::Dashboard/V1/Merchant/Review', 'dashboard/Overview'] as $page) {
            $this->assertFileExists("{$root}/".InertiaPage::viteEntry($page), $page);
        }
    }
}
