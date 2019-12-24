<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Entity\EventRepository;
use Mautic\CampaignBundle\Event\CampaignEvent;
use Mautic\CampaignBundle\Event\FailedEvent;
use Mautic\CampaignBundle\Executioner\Helper\NotificationHelper;
use Mautic\CampaignBundle\Model\CampaignModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var float
     */
    private $disableCampaignThreshold = 0.1;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var CampaignModel
     */
    private $campaignModel;

    public function __construct(EventRepository $eventRepository, NotificationHelper $notificationHelper, CampaignModel $campaignModel)
    {
        $this->eventRepository    = $eventRepository;
        $this->notificationHelper = $notificationHelper;
        $this->campaignModel      = $campaignModel;
    }

    /**
     * Get the subscribed events for this listener.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_PRE_SAVE => ['onCampaignPreSave', 0],
            CampaignEvents::ON_EVENT_FAILED   => ['onEventFailed', 0],
        ];
    }

    /**
     * Reset all campaign event failed_count's
     * to 0 when the campaign is published.
     */
    public function onCampaignPreSave(CampaignEvent $event)
    {
        $campaign = $event->getCampaign();
        $changes  = $campaign->getChanges();

        if (array_key_exists('isPublished', $changes)) {
            list($actual, $inMemory) = $changes['isPublished'];

            // If we're publishing the campaign
            if (false === $actual && true === $inMemory) {
                $this->eventRepository->resetFailedCountsForEventsInCampaign($campaign);
            }
        }
    }

    /**
     * Process the FailedEvent event. Notifies users and checks
     * failed thresholds to notify CS and/or disable the campaign.
     */
    public function onEventFailed(FailedEvent $event)
    {
        $log           = $event->getLog();
        $failedEvent   = $log->getEvent();
        $campaign      = $failedEvent->getCampaign();
        $failedCount   = $this->eventRepository->incrementFailedCount($failedEvent);
        $contactCount  = $campaign->getLeads()->count();
        $failedPercent = $contactCount ? ($failedCount / $contactCount) : 1;

        $this->notificationHelper->notifyOfFailure($log->getLead(), $failedEvent);

        if ($failedPercent >= $this->disableCampaignThreshold) {
            $this->notificationHelper->notifyOfUnpublish($failedEvent);
            $campaign->setIsPublished(false);
            $this->campaignModel->saveEntity($campaign);
        }
    }
}
