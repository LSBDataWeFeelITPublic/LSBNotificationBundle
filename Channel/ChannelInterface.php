<?php

namespace LSB\NotificationBundle\Channel;

use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationRecipient;

/**
 * @author Krzysztof Mazur
 *
 * Interface ChannelInterface
 * @package LSB\NotificationBundle\Channel
 */
interface ChannelInterface
{
    /**
     * Pobiera nazwę kanału powiadomień
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Wysyła pojedyncze "proste" powiadomienie
     *
     * @param Notification $notification
     * @param iterable $recipientsToProcess
     * @return array
     */
    public function sendSimpleNotification(Notification $notification, iterable $recipientsToProcess): array;

    /**
     * Wysyła pojedyncze "rozszerzone" powiadomienie
     *
     * @param Notification $notification
     * @param iterable $recipientsPackage
     * @return array
     */
    public function sendExtendedNotification(Notification $notification, iterable $recipientsPackage): array;

    /**
     * Waliduje dane odbiorców
     *
     * @param array $recipients
     * @return array
     */
    public function validateSimpleRecipients(array $recipients): array;

    /**
     * Metoda walidująca "prostego" odbiorcę
     *
     * @param $recipient
     * @return mixed
     */
    public function validateSimpleRecipient($recipient);

    /**
     * Metoda walidująca "rozszerzonego" odbiorcę
     *
     * @param NotificationRecipient $recipient
     * @return mixed
     */
    public function validateExtendedRecipient(NotificationRecipient $recipient);

    /**
     * Konwertuje dane do określonej przez kanały formy danych (w zależności od obiektu)
     *
     * @param $template
     * @param array $templateData
     * @return mixed
     */
    public function convertDataIntoContent($template, array $templateData);

    /**
     * Status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Zwraca maksymalną liczbę odbiorców dla jednego cyklu wysyłki
     *
     * @return int
     */
    public function getMaxRecipients(): int;

    /**
     * Dedykowana metoda parsująca uwzględniające specyfikę kanału - opiera swoje działanie na ogólno dostępnym parserze powiadomień
     *
     * @param Notification $notification
     * @param NotificationRecipient $notificationRecipient
     * @return Notification
     */
    public function parseContent(Notification $notification, NotificationRecipient $notificationRecipient): Notification;

}
