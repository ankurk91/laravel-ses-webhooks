<?php

return [
    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. See the examples below for key names.
     *
     * You can find a list of event types here:
     * https://docs.aws.amazon.com/ses/latest/dg/event-publishing-retrieving-sns-examples.html
     */
    'jobs' => [
        // 'bounce' => \App\Jobs\SesWebhooks\BounceEvent::class,
        // 'complaint' => \App\Jobs\SesWebhooks\ComplaintEvent::class,
        // 'rendering_failure' => \App\Jobs\SesWebhooks\RenderingFailureEvent::class,
    ],

    /*
    * The classname of the model to be used. The class should equal or extend
    * \Ankurk91\SesWebhooks\SesWebhookCall.
    */
    'model' => \Ankurk91\SesWebhooks\SesWebhookCall::class,

    /**
     * This class determines if the webhook call should be stored and processed.
     */
    'profile' => \Ankurk91\SesWebhooks\SesWebhookProfile::class,

    /*
     * When disabled, the package will not verify if the signature is valid.
     * This can be handy in local environments.
     */
    'verify_signature' => (bool) env('SES_SIGNATURE_VERIFY', true),
];
