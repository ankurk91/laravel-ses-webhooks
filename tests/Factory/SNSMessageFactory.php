<?php

declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Tests\Factory;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

/**
 * I don't own this php class.
 * @source https://github.com/renoki-co/laravel-sns-events/blob/master/src/Concerns/GeneratesSnsMessages.php
 */
class SNSMessageFactory
{
    /**
     * The certificate to sign the request for SNS.
     */
    protected ?string $certificate;

    /**
     * Get the signature for the message.
     */
    protected function getSignature(string $stringToSign): string
    {
        $privateKey = openssl_pkey_new();

        $csr = openssl_csr_new([], $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 1);
        openssl_x509_export($x509, $this->certificate);
        openssl_sign($stringToSign, $signature, $privateKey);

        return base64_encode($signature);
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    /**
     * Get an example subscription payload for testing.
     */
    public function getSubscriptionConfirmationPayload(array $custom = []): array
    {
        $message = array_merge([
            'Type' => 'SubscriptionConfirmation',
            'MessageId' => '165545c9-2a5c-472c-8df2-7ff2be2b3b1b',
            'Token' => '2336412f37...',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Message' => 'You have chosen to subscribe to the topic arn:aws:sns:us-west-2:123456789012:MyTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.',
            'SubscribeURL' => 'http://localhost/aws-sns',
            'Timestamp' => now()->toDateTimeString(),
            'SignatureVersion' => '1',
            'Signature' => true,
            'SigningCertURL' => 'https://sns.us-west-2.amazonaws.com/sns-cert.pem',
        ], $custom);

        $message['Signature'] = $this->getSignature(
            (new MessageValidator())->getStringToSign(new Message($message))
        );

        return $message;
    }

    /**
     * Get an example notification payload for testing.
     */
    public function getNotificationPayload(array $payload = [], array $custom = []): array
    {
        $payload = json_encode($payload);

        $message = array_merge([
            'Type' => 'Notification',
            'MessageId' => '22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Subject' => 'My First Message',
            'Message' => "$payload",
            'Timestamp' => now()->toDateTimeString(),
            'SignatureVersion' => '1',
            'Token' => '2336412f37...',
            'Signature' => true,
            'SigningCertURL' => 'https://sns.us-west-2.amazonaws.com/sns-cert.pem',
            'UnsubscribeURL' => 'https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96',
        ], $custom);

        $message['Signature'] = $this->getSignature(
            (new MessageValidator())->getStringToSign(new Message($message))
        );

        return $message;
    }

}
