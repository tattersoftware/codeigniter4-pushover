# Tatter\Pushover
Pushover integration for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-pushover/workflows/PHP%20Unit%20Tests/badge.svg)](https://github.com/tattersoftware/codeigniter4-pushover/actions?query=workflow%3A%22PHP+Unit+Tests)

## Quick Start

1. Install with Composer: `> composer require tatter/pushover`
2. Send an alert: `service('pushover')->message(['message' => 'Hellow world'])->send();`

## Description

**Tatter\Pushover** adds an easy-to-use class for [Pushover](https://pushover.net)
to your CodeIgniter 4 project. Send push notifications and access other API endpoints with
the integrated Service and support entities.

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**examples/Pushover.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** then the library will use its own.

In order to interface with Pushover you will need to specify your user secret and app token.
As these are sensitive items it is highly recommended you supply them in your **.env** file
instead of directly to repository code, e.g. (fake values):
```
pushover.user = e9e1495ec75826de5983cd1abc8031
pushover.token = KzGDORePKggMaC0QOYAMyEEuzJnyUi
```

## Usage

Load the service with CodeIgniter's service helper:

	$pushover = service('pushover');

Then craft your message and send it off to Pushover:

```
use Tatter\Pushover\Entities\Message;

$message = new Message([
	'title'    => 'My Message',
	'message'  => 'This is my first CodeIgniter push notification!',
	'priority' => 1,
]);

$pushover->sendMessage($message);
```

You may also use class convenience methods to draft Messages with pre-defined default
properties (see **examples/Pushover.php** for configuration):

```
$message = $pushover->message(['message' => 'Hellow world']);
$message->send();
```

## Troubleshooting

Follow Pushover's [API specifications](https://pushover.net/api#messages) to make sure
your messages are valid and you usage complies with their policies. Use the class method
`Pushover::getErrors()` to access any error messages should something go wrong:

```
try
{
	$pushover->message(['title' => 'New Boots', 'attachment' => 'boots.jpg'])->send();
}
catch (\Tatter\Pushover\Exceptions\PushoverException $e)
{
	d($pushover->getErrors());
}
...
array (2) [
    0 => string (23) "message cannot be blank"
    1 => string (37) "The API returned a failing status: 0."
]
```
