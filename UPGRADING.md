# Upgrading

## From 1.x to 2.x

Starting from v2.0, the package will decode and store the `Message` property and save along with payload in database.

If you have been decoding the `Message` string in your app, you will no longer need to.

```diff
- $message = json_decode($this->webhookCall->payload['Message'], true, 512, JSON_THROW_ON_ERROR);
+ $message = $this->webhookCall->payload['Message']
```
