<?php

namespace Mautic\DashboardBundle\Tests\Event;

use Mautic\CacheBundle\Cache\CacheProvider;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\DashboardBundle\Entity\Widget;
use Mautic\DashboardBundle\Event\WidgetDetailEvent;
use Mautic\DashboardBundle\Event\WidgetDetailEventFactory;
use Mautic\UserBundle\Entity\User;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Translation\TranslatorInterface;

class WidgetDetailEventFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testShowPost()
    {
        if (!function_exists('uopz_flags')) {
            return $this->markTestSkipped('Cache provider is final, cannot test without uopz.');
        }
        uopz_flags(CacheProvider::class, null, 0);

        $securityMock      = $this->createMock(CorePermissions::class);
        $translatorMock    = $this->createMock(TranslatorInterface::class);
        $cacheProviderMock = $this->createMock(CacheProvider::class);
        $userHelperMock    = $this->createMock(UserHelper::class);
        $widgetEntity      = $this->createMock(Widget::class);
        $userMock          = $this->createMock(User::class);
        $cacheItemMock     = new CacheItem();

        $userHelperMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $eventFactory = new WidgetDetailEventFactory($translatorMock, $cacheProviderMock, $securityMock, $userHelperMock);
        /** @var WidgetDetailEvent $event */
        $event = $eventFactory->create($widgetEntity, 99);

        $this->assertEquals($event->getTranslator(), $translatorMock);

        $cacheProviderMock->expects($this->exactly(2))->method('getItem')->willReturn($cacheItemMock);
        $cacheProviderMock->expects($this->once())->method('save')->willReturn(true);

        $this->assertEquals(false, $event->isCached());

        $this->assertEquals('dashboard.widget._eeb638b133b96b27', $event->getCacheKey());
        $this->assertEquals(true, $event->setTemplateData([], false));
    }
}
